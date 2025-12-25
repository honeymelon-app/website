<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ReleaseChannel;
use App\Services\GithubService;
use App\Services\ReleaseService;
use App\Support\PlatformDetector;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessGithubReleaseJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $tag,
        public readonly ReleaseChannel $channel,
        public readonly string $version,
        public readonly string $commitHash,
        public readonly bool $isMajor = false,
        public readonly ?int $userId = null,
        public readonly array $payload = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        GithubService $githubService,
        ReleaseService $releaseService,
    ): void {
        Log::info('Processing GitHub release', [
            'tag' => $this->tag,
            'version' => $this->version,
            'channel' => $this->channel->value,
        ]);

        try {
            DB::transaction(function () use ($githubService, $releaseService) {
                $notes = $this->payload['notes'] ?? null;
                $publishedAt = $this->payload['published_at'] ?? null;
                $artifacts = $this->payload['artifacts'] ?? [];

                if (empty($artifacts)) {
                    $githubData = $githubService->fetchRelease($this->tag);
                    $notes = $githubData['notes'];
                    $publishedAt = $githubData['published_at'];
                    $artifacts = $githubData['assets'];
                }

                // Create release record
                $release = $releaseService->recordRelease([
                    'version' => $this->version,
                    'name' => $this->payload['name'] ?? null,
                    'tag' => $this->tag,
                    'commit_hash' => $this->commitHash,
                    'author' => $this->payload['author'] ?? null,
                    'html_url' => $this->payload['html_url'] ?? null,
                    'target_commitish' => $this->payload['target_commitish'] ?? null,
                    'github_id' => $this->payload['github_id'] ?? null,
                    'channel' => $this->channel,
                    'prerelease' => $this->payload['prerelease'] ?? false,
                    'draft' => $this->payload['draft'] ?? false,
                    'notes' => $notes,
                    'published_at' => $publishedAt ?? now()->toIso8601String(),
                    'github_created_at' => $this->payload['github_created_at'] ?? null,
                    'major' => $this->isMajor,
                    'user_id' => $this->userId,
                ]);

                Log::info('Release created', ['release_id' => $release->id]);

                // Process and attach artifacts
                foreach ($artifacts as $asset) {
                    $platform = $asset['platform'] ?? PlatformDetector::detect($asset['filename'] ?? $asset['name'] ?? '');

                    if ($platform === null) {
                        Log::warning('Could not extract platform from asset', ['asset' => $asset['filename'] ?? $asset['name'] ?? null]);

                        continue;
                    }

                    $releaseService->attachArtifact($release, [
                        'platform' => $platform,
                        'source' => $asset['source'] ?? 'r2',
                        'state' => $asset['state'] ?? null,
                        'filename' => $asset['filename'] ?? $asset['name'] ?? null,
                        'content_type' => $asset['content_type'] ?? null,
                        'size' => $asset['size'] ?? 0,
                        'download_count' => $asset['download_count'] ?? 0,
                        'sha256' => $asset['sha256'] ?? null,
                        'signature' => $asset['signature'] ?? null,
                        'notarized' => (bool) ($asset['notarized'] ?? false),
                        'url' => $asset['url'] ?? $asset['path'] ?? null,
                        'path' => $asset['path'] ?? null,
                        'github_id' => $asset['github_id'] ?? null,
                        'github_created_at' => $asset['github_created_at'] ?? null,
                        'github_updated_at' => $asset['github_updated_at'] ?? null,
                    ]);

                    Log::info('Artifact attached', [
                        'release_id' => $release->id,
                        'platform' => $platform,
                        'filename' => $asset['filename'] ?? $asset['name'] ?? null,
                    ]);
                }

                Log::info('GitHub release processed successfully', [
                    'release_id' => $release->id,
                    'version' => $this->version,
                    'channel' => $this->channel->value,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to process GitHub release', [
                'tag' => $this->tag,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
