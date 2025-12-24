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
     * @return array{created: int, updated: int, skipped: int, errors: int}
     */
    public function syncReleases(): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];

        Log::info('Starting GitHub release sync');

        try {
            $githubReleases = $this->githubService->fetchAllReleases();
        } catch (\Throwable $e) {
            Log::error('Failed to fetch releases from GitHub', ['error' => $e->getMessage()]);

            throw $e;
        }

        Log::info('Fetched releases from GitHub', ['count' => count($githubReleases)]);

        foreach ($githubReleases as $githubRelease) {
            try {
                $result = $this->syncRelease($githubRelease);
                $stats[$result]++;
            } catch (\Throwable $e) {
                Log::error('Failed to sync release', [
                    'tag' => $githubRelease['tag'],
                    'error' => $e->getMessage(),
                ]);
                $stats['errors']++;
            }
        }

        Log::info('GitHub release sync completed', $stats);

        return $stats;
    }

    /**
     * Sync a single release from GitHub.
     *
     * @param  array{id: int, tag: string, name: string, notes: string, published_at: string, prerelease: bool, draft: bool, assets: array<int, array{name: string, url: string, size: int}>}  $githubRelease
     * @return string Result status: 'created', 'updated', or 'skipped'
     */
    public function syncRelease(array $githubRelease): string
    {
        if ($githubRelease['draft']) {
            Log::debug('Skipping draft release', ['tag' => $githubRelease['tag']]);

            return 'skipped';
        }

        $existingRelease = Release::where('tag', $githubRelease['tag'])->first();

        if ($existingRelease) {
            return $this->updateRelease($existingRelease, $githubRelease);
        }

        return $this->createRelease($githubRelease);
    }

    /**
     * Create a new release from GitHub data.
     *
     * @param  array{id: int, tag: string, name: string, notes: string, published_at: string, prerelease: bool, draft: bool, assets: array<int, array{name: string, url: string, size: int}>}  $githubRelease
     */
    protected function createRelease(array $githubRelease): string
    {
        $version = $this->extractVersion($githubRelease['tag']);
        $channel = $this->determineChannel($githubRelease);
        $commitHash = $this->fetchCommitHash($githubRelease['tag']);

        $release = $this->releaseService->recordRelease([
            'version' => $version,
            'tag' => $githubRelease['tag'],
            'commit_hash' => $commitHash,
            'channel' => $channel,
            'notes' => $githubRelease['notes'],
            'published_at' => $githubRelease['published_at'],
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
     * @param  array{id: int, tag: string, name: string, notes: string, published_at: string, prerelease: bool, draft: bool, assets: array<int, array{name: string, url: string, size: int}>}  $githubRelease
     */
    protected function updateRelease(Release $release, array $githubRelease): string
    {
        $hasChanges = false;

        if ($release->notes !== $githubRelease['notes']) {
            $release->notes = $githubRelease['notes'];
            $hasChanges = true;
        }

        $commitHash = $this->fetchCommitHash($githubRelease['tag']);
        if ($release->commit_hash !== $commitHash) {
            $release->commit_hash = $commitHash;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $release->save();
        }

        $artifactsUpdated = $this->syncArtifacts($release, $githubRelease['assets']);

        if ($hasChanges || $artifactsUpdated) {
            Log::info('Updated release from GitHub', [
                'release_id' => $release->id,
                'tag' => $githubRelease['tag'],
            ]);

            return 'updated';
        }

        return 'skipped';
    }

    /**
     * Sync artifacts for a release.
     *
     * @param  array<int, array{name: string, url: string, size: int}>  $assets
     * @return bool Whether any artifacts were created or updated
     */
    protected function syncArtifacts(Release $release, array $assets): bool
    {
        $updated = false;

        foreach ($assets as $asset) {
            $platform = PlatformDetector::detect($asset['name']);

            if ($platform === null) {
                continue;
            }

            $existingArtifact = $release->artifacts()
                ->where('platform', $platform)
                ->where('filename', $asset['name'])
                ->first();

            if ($existingArtifact) {
                if ($this->updateArtifact($existingArtifact, $asset)) {
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
     * @param  array{name: string, url: string, size: int}  $asset
     */
    protected function createArtifact(Release $release, array $asset, string $platform): Artifact
    {
        return $this->releaseService->attachArtifact($release, [
            'platform' => $platform,
            'source' => 'github',
            'filename' => $asset['name'],
            'size' => $asset['size'],
            'sha256' => null,
            'signature' => null,
            'notarized' => false,
            'url' => $asset['url'],
            'path' => null,
        ]);
    }

    /**
     * Update an existing artifact if it has changed.
     *
     * Never overwrites artifacts that were uploaded to R2/S3 via the CI pipeline.
     * Only updates artifacts that originated from GitHub sync (source: 'github').
     *
     * @param  array{name: string, url: string, size: int}  $asset
     */
    protected function updateArtifact(Artifact $artifact, array $asset): bool
    {
        if ($artifact->source !== 'github') {
            return false;
        }

        $hasChanges = false;

        if ($artifact->size !== $asset['size']) {
            $artifact->size = $asset['size'];
            $hasChanges = true;
        }

        if ($artifact->url !== $asset['url']) {
            $artifact->url = $asset['url'];
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
}
