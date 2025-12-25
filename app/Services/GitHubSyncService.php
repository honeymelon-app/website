<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GitRepository;
use App\Enums\ReleaseChannel;
use App\Models\Artifact;
use App\Models\Release;
use App\Support\PlatformDetector;
use Illuminate\Support\Facades\Log;

final class GitHubSyncService
{
    public function __construct(
        private readonly GitRepository $githubService,
        private readonly ReleaseService $releaseService
    ) {}

    /**
     * Sync all releases from GitHub to the local database.
     *
     * @param  array{force?: bool, delete_removed?: bool, sync_download_counts?: bool}  $options
     * @return array{created: int, updated: int, skipped: int, deleted: int, errors: int}
     */
    public function syncReleases(array $options = []): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'deleted' => 0, 'errors' => 0];
        $force = $options['force'] ?? false;
        $deleteRemoved = $options['delete_removed'] ?? false;
        $syncDownloadCounts = $options['sync_download_counts'] ?? true;

        Log::info('Starting GitHub release sync', [
            'force' => $force,
            'delete_removed' => $deleteRemoved,
            'sync_download_counts' => $syncDownloadCounts,
        ]);

        try {
            $githubReleases = $this->githubService->fetchAllReleases();
        } catch (\Throwable $e) {
            Log::error('Failed to fetch releases from GitHub', ['error' => $e->getMessage()]);

            throw $e;
        }

        Log::info('Fetched releases from GitHub', ['count' => count($githubReleases)]);

        // Track GitHub release IDs to detect deletions
        $githubReleaseIds = [];

        foreach ($githubReleases as $githubRelease) {
            try {
                $githubReleaseIds[] = $githubRelease['github_id'];
                $result = $this->syncRelease($githubRelease, $force);
                $stats[$result]++;

                // Sync download counts if enabled
                if ($syncDownloadCounts && $result !== 'skipped') {
                    $this->syncDownloadCounts($githubRelease);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to sync release', [
                    'tag' => $githubRelease['tag'],
                    'error' => $e->getMessage(),
                ]);
                $stats['errors']++;
            }
        }

        // Handle deleted releases
        if ($deleteRemoved) {
            $deleted = $this->deleteRemovedReleases($githubReleaseIds);
            $stats['deleted'] = $deleted;
        }

        Log::info('GitHub release sync completed', $stats);

        return $stats;
    }

    /**
     * Sync a single release from GitHub.
     *
     * @param  array{id: int, github_id: int, tag: string, name: string, notes: string, published_at: string, created_at: string, prerelease: bool, draft: bool, author: ?string, html_url: string, target_commitish: string, assets: array<int, array{id: int, name: string, url: string, size: int, content_type: string, download_count: int, state: string, created_at: string, updated_at: string}>}  $githubRelease
     * @return string Result status: 'created', 'updated', or 'skipped'
     */
    public function syncRelease(array $githubRelease, bool $force = false): string
    {
        if ($githubRelease['draft']) {
            Log::debug('Skipping draft release', ['tag' => $githubRelease['tag']]);

            return 'skipped';
        }

        $existingRelease = Release::where('tag', $githubRelease['tag'])
            ->orWhere('github_id', $githubRelease['github_id'])
            ->first();

        if ($existingRelease) {
            return $this->updateRelease($existingRelease, $githubRelease, $force);
        }

        return $this->createRelease($githubRelease);
    }

    /**
     * Create a new release from GitHub data.
     *
     * @param  array{id: int, github_id: int, tag: string, name: string, notes: string, published_at: string, created_at: string, prerelease: bool, draft: bool, author: ?string, html_url: string, target_commitish: string, assets: array<int, array{id: int, name: string, url: string, size: int, content_type: string, download_count: int, state: string, created_at: string, updated_at: string}>}  $githubRelease
     */
    protected function createRelease(array $githubRelease): string
    {
        $version = $this->extractVersion($githubRelease['tag']);
        $channel = $this->determineChannel($githubRelease);
        $commitHash = $this->fetchCommitHash($githubRelease['tag']);

        $release = $this->releaseService->recordRelease([
            'version' => $version,
            'name' => $githubRelease['name'],
            'tag' => $githubRelease['tag'],
            'commit_hash' => $commitHash,
            'author' => $githubRelease['author'],
            'html_url' => $githubRelease['html_url'],
            'target_commitish' => $githubRelease['target_commitish'],
            'github_id' => $githubRelease['github_id'],
            'channel' => $channel,
            'prerelease' => $githubRelease['prerelease'],
            'draft' => $githubRelease['draft'],
            'notes' => $githubRelease['notes'],
            'published_at' => $githubRelease['published_at'],
            'github_created_at' => $githubRelease['created_at'],
            'major' => $this->isMajorRelease($version),
            'user_id' => null,
        ]);

        $this->syncArtifacts($release, $githubRelease['assets']);

        Log::info('Created release from GitHub', [
            'release_id' => $release->id,
            'tag' => $githubRelease['tag'],
            'artifacts' => count($githubRelease['assets']),
        ]);

        return 'created';
    }

    /**
     * Update an existing release with GitHub data.
     *
     * @param  array{id: int, github_id: int, tag: string, name: string, notes: string, published_at: string, created_at: string, prerelease: bool, draft: bool, author: ?string, html_url: string, target_commitish: string, assets: array<int, array{id: int, name: string, url: string, size: int, content_type: string, download_count: int, state: string, created_at: string, updated_at: string}>}  $githubRelease
     */
    protected function updateRelease(Release $release, array $githubRelease, bool $force = false): string
    {
        $hasChanges = false;

        // Update all metadata fields
        if ($force || $release->name !== $githubRelease['name']) {
            $release->name = $githubRelease['name'];
            $hasChanges = true;
        }

        if ($force || $release->notes !== $githubRelease['notes']) {
            $release->notes = $githubRelease['notes'];
            $hasChanges = true;
        }

        if ($force || $release->author !== $githubRelease['author']) {
            $release->author = $githubRelease['author'];
            $hasChanges = true;
        }

        if ($force || $release->html_url !== $githubRelease['html_url']) {
            $release->html_url = $githubRelease['html_url'];
            $hasChanges = true;
        }

        if ($force || $release->target_commitish !== $githubRelease['target_commitish']) {
            $release->target_commitish = $githubRelease['target_commitish'];
            $hasChanges = true;
        }

        if ($force || $release->github_id !== $githubRelease['github_id']) {
            $release->github_id = $githubRelease['github_id'];
            $hasChanges = true;
        }

        if ($force || $release->prerelease !== $githubRelease['prerelease']) {
            $release->prerelease = $githubRelease['prerelease'];
            $hasChanges = true;
        }

        if ($force || $release->draft !== $githubRelease['draft']) {
            $release->draft = $githubRelease['draft'];
            $hasChanges = true;
        }

        $publishedAt = $githubRelease['published_at'];
        if ($force || $release->published_at === null || ! $release->published_at->eq(\Carbon\Carbon::parse($publishedAt))) {
            $release->published_at = $publishedAt;
            $hasChanges = true;
        }

        $createdAt = $githubRelease['created_at'];
        if ($force || $release->github_created_at === null || ! $release->github_created_at->eq(\Carbon\Carbon::parse($createdAt))) {
            $release->github_created_at = $createdAt;
            $hasChanges = true;
        }

        $commitHash = $this->fetchCommitHash($githubRelease['tag']);
        if ($force || $release->commit_hash !== $commitHash) {
            $release->commit_hash = $commitHash;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $release->save();
        }

        $artifactsUpdated = $this->syncArtifacts($release, $githubRelease['assets'], $force);

        if ($hasChanges || $artifactsUpdated) {
            Log::info('Updated release from GitHub', [
                'release_id' => $release->id,
                'tag' => $githubRelease['tag'],
                'fields_updated' => $hasChanges,
                'artifacts_updated' => $artifactsUpdated,
            ]);

            return 'updated';
        }

        return 'skipped';
    }

    /**
     * Sync artifacts for a release.
     *
     * @param  array<int, array{id: int, name: string, url: string, size: int, content_type: string, download_count: int, state: string, created_at: string, updated_at: string}>  $assets
     * @return bool Whether any artifacts were created or updated
     */
    protected function syncArtifacts(Release $release, array $assets, bool $force = false): bool
    {
        $updated = false;

        foreach ($assets as $asset) {
            $platform = PlatformDetector::detect($asset['name']);

            if ($platform === null) {
                continue;
            }

            $existingArtifact = $release->artifacts()
                ->where(function ($query) use ($platform, $asset) {
                    $query->where('platform', $platform)
                        ->where('filename', $asset['name'])
                        ->orWhere('github_id', $asset['id']);
                })
                ->first();

            if ($existingArtifact) {
                if ($this->updateArtifact($existingArtifact, $asset, $force)) {
                    $updated = true;
                }

                continue;
            }

            $hasR2Artifact = $release->artifacts()
                ->where('platform', $platform)
                ->where('source', '!=', 'github')
                ->exists();

            if ($hasR2Artifact) {
                continue;
            }

            $this->createArtifact($release, $asset, $platform);
            $updated = true;
        }

        return $updated;
    }

    /**
     * Create a new artifact from GitHub asset data.
     *
     * @param  array{id: int, name: string, url: string, size: int, content_type: string, download_count: int, state: string, created_at: string, updated_at: string}  $asset
     */
    protected function createArtifact(Release $release, array $asset, string $platform): Artifact
    {
        return $this->releaseService->attachArtifact($release, [
            'platform' => $platform,
            'source' => 'github',
            'state' => $asset['state'],
            'filename' => $asset['name'],
            'content_type' => $asset['content_type'],
            'size' => $asset['size'],
            'download_count' => $asset['download_count'],
            'sha256' => null,
            'signature' => null,
            'notarized' => false,
            'url' => $asset['url'],
            'path' => null,
            'github_id' => $asset['id'],
            'github_created_at' => $asset['created_at'],
            'github_updated_at' => $asset['updated_at'],
        ]);
    }

    /**
     * Update an existing artifact if it has changed.
     *
     * Never overwrites artifacts that were uploaded to R2/S3 via the CI pipeline.
     * Only updates artifacts that originated from GitHub sync (source: 'github').
     *
     * @param  array{id: int, name: string, url: string, size: int, content_type: string, download_count: int, state: string, created_at: string, updated_at: string}  $asset
     */
    protected function updateArtifact(Artifact $artifact, array $asset, bool $force = false): bool
    {
        if ($artifact->source !== 'github') {
            return false;
        }

        $hasChanges = false;

        if ($force || $artifact->size !== $asset['size']) {
            $artifact->size = $asset['size'];
            $hasChanges = true;
        }

        if ($force || $artifact->url !== $asset['url']) {
            $artifact->url = $asset['url'];
            $hasChanges = true;
        }

        if ($force || $artifact->content_type !== $asset['content_type']) {
            $artifact->content_type = $asset['content_type'];
            $hasChanges = true;
        }

        if ($force || $artifact->download_count !== $asset['download_count']) {
            $artifact->download_count = $asset['download_count'];
            $hasChanges = true;
        }

        if ($force || $artifact->state !== $asset['state']) {
            $artifact->state = $asset['state'];
            $hasChanges = true;
        }

        if ($force || $artifact->github_id !== $asset['id']) {
            $artifact->github_id = $asset['id'];
            $hasChanges = true;
        }

        if ($force || $artifact->github_created_at?->toIso8601String() !== $asset['created_at']) {
            $artifact->github_created_at = $asset['created_at'];
            $hasChanges = true;
        }

        if ($force || $artifact->github_updated_at?->toIso8601String() !== $asset['updated_at']) {
            $artifact->github_updated_at = $asset['updated_at'];
            $hasChanges = true;
        }

        if ($hasChanges) {
            $artifact->save();
        }

        return $hasChanges;
    }

    /**
     * Extract version from a git tag (removes 'v' prefix if present).
     */
    protected function extractVersion(string $tag): string
    {
        return ltrim($tag, 'vV');
    }

    /**
     * Determine the release channel from GitHub release data.
     *
     * @param  array{prerelease: bool, tag: string, name: string}  $githubRelease
     */
    protected function determineChannel(array $githubRelease): ReleaseChannel
    {
        $tag = strtolower($githubRelease['tag']);

        if (str_contains($tag, 'alpha')) {
            return ReleaseChannel::ALPHA;
        }

        if (str_contains($tag, 'beta')) {
            return ReleaseChannel::BETA;
        }

        if (str_contains($tag, 'rc') || str_contains($tag, 'release-candidate')) {
            return ReleaseChannel::RC;
        }

        if ($githubRelease['prerelease']) {
            return ReleaseChannel::BETA;
        }

        return ReleaseChannel::STABLE;
    }

    /**
     * Fetch the actual commit hash for a tag from GitHub.
     *
     * The release API's target_commitish field contains the branch name,
     * not the commit SHA. This method fetches the actual commit SHA
     * that the tag points to.
     */
    protected function fetchCommitHash(string $tag): string
    {
        $commitSha = $this->githubService->fetchCommitShaForTag($tag);

        return $commitSha ?? '';
    }

    /**
     * Check if this is a major version release.
     */
    protected function isMajorRelease(string $version): bool
    {
        $parts = explode('.', $version);

        if (count($parts) < 2) {
            return false;
        }

        return $parts[1] === '0' && ($parts[2] ?? '0') === '0';
    }

    /**
     * Sync download counts for a release's artifacts.
     *
     * @param  array{id: int, github_id: int, tag: string, assets: array<int, array{id: int, download_count: int}>}  $githubRelease
     */
    protected function syncDownloadCounts(array $githubRelease): void
    {
        $release = Release::where('tag', $githubRelease['tag'])
            ->orWhere('github_id', $githubRelease['github_id'])
            ->first();

        if (! $release) {
            return;
        }

        foreach ($githubRelease['assets'] as $asset) {
            $artifact = $release->artifacts()
                ->where('github_id', $asset['id'])
                ->first();

            if ($artifact && $artifact->source === 'github') {
                $artifact->update(['download_count' => $asset['download_count']]);
            }
        }
    }

    /**
     * Delete releases that no longer exist on GitHub.
     *
     * @param  array<int>  $githubReleaseIds
     * @return int Number of releases deleted
     */
    protected function deleteRemovedReleases(array $githubReleaseIds): int
    {
        $releasesToDelete = Release::whereNotNull('github_id')
            ->whereNotIn('github_id', $githubReleaseIds)
            ->get();

        $count = $releasesToDelete->count();

        foreach ($releasesToDelete as $release) {
            Log::info('Deleting release removed from GitHub', [
                'release_id' => $release->id,
                'tag' => $release->tag,
                'github_id' => $release->github_id,
            ]);

            $release->delete();
        }

        return $count;
    }
}
