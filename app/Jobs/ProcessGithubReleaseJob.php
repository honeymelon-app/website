<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ReleaseChannel;
use App\Services\GithubService;
use App\Services\ReleaseService;
use App\Services\UpdateService;
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
        UpdateService $updateService
    ): void {
        Log::info('Processing GitHub release', [
            'tag' => $this->tag,
            'version' => $this->version,
            'channel' => $this->channel->value,
        ]);

        try {
            DB::transaction(function () use ($githubService, $releaseService, $updateService) {
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
                    'tag' => $this->tag,
                    'commit_hash' => $this->commitHash,
                    'channel' => $this->channel,
                    'notes' => $notes,
                    'published_at' => $publishedAt ?? now()->toIso8601String(),
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
                        'source' => $asset['source'] ?? 'github',
                        'filename' => $asset['filename'] ?? $asset['name'] ?? null,
                        'size' => $asset['size'] ?? 0,
                        'sha256' => $asset['sha256'] ?? null,
                        'signature' => $asset['signature'] ?? null,
                        'notarized' => (bool) ($asset['notarized'] ?? false),
                        'url' => $asset['url'] ?? $asset['path'] ?? null,
                        'path' => $asset['path'] ?? null,
                    ]);

                    Log::info('Artifact attached', [
                        'release_id' => $release->id,
                        'platform' => $platform,
                        'filename' => $asset['filename'] ?? $asset['name'] ?? null,
                    ]);
                }

                // Build and publish update manifest
                $update = $updateService->buildAndPublish($release, $this->channel->value);

                Log::info('GitHub release processed successfully', [
                    'release_id' => $release->id,
                    'update_id' => $update->id,
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
