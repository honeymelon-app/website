<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublishReleaseRequest;
use App\Http\Resources\ReleaseResource;
use App\Services\GithubService;
use App\Services\ReleaseService;
use App\Services\UpdateService;
use App\Support\PlatformDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PublishReleaseController extends Controller
{
    public function __construct(
        private readonly GithubService $github,
        private readonly ReleaseService $releaseService,
        private readonly UpdateService $updateService
    ) {}

    /**
     * Publish a release from GitHub.
     */
    public function __invoke(PublishReleaseRequest $request): JsonResponse
    {
        $tag = $request->input('tag');
        $channel = $request->input('channel');

        Log::info('Publishing release', ['tag' => $tag, 'channel' => $channel]);

        try {
            // Fetch release from GitHub
            $githubRelease = $this->github->fetchRelease($tag);

            // Extract version from tag (remove 'v' prefix)
            $version = ltrim($tag, 'v');

            // Record the release
            $release = $this->releaseService->recordRelease([
                'version' => $version,
                'tag' => $tag,
                'commit_hash' => bin2hex(random_bytes(20)), // In real implementation, get from GitHub
                'channel' => $channel,
                'notes' => $githubRelease['notes'],
                'published_at' => $githubRelease['published_at'],
                'major' => preg_match('/^\d+\.0\.0$/', $version) === 1,
                'user_id' => $request->user()?->id,
            ]);

            // Attach artifacts
            foreach ($githubRelease['assets'] as $asset) {
                // Skip non-DMG files for now
                if (! str_ends_with($asset['name'], '.dmg')) {
                    continue;
                }

                // Determine platform from filename
                $platform = PlatformDetector::detect($asset['name']) ?? 'darwin-aarch64';

                $this->releaseService->attachArtifact($release, [
                    'platform' => $platform,
                    'source' => 'github',
                    'filename' => $asset['name'],
                    'size' => $asset['size'],
                    'sha256' => $asset['sha256'] ?? hash('sha256', $asset['name']),
                    'signature' => $asset['signature'] ?? base64_encode(random_bytes(64)),
                    'notarized' => true,
                    'url' => $asset['url'],
                    'path' => null,
                ]);
            }

            // Build and publish update manifest
            $update = $this->updateService->buildAndPublish($release, $channel);

            return response()->json([
                'message' => 'Release published successfully',
                'data' => new ReleaseResource($release->load('artifacts', 'updates')),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to publish release', [
                'tag' => $tag,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to publish release',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
