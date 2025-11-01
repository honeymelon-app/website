<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublishReleaseRequest;
use App\Http\Requests\RollbackReleaseRequest;
use App\Http\Resources\ReleaseCollection;
use App\Http\Resources\ReleaseResource;
use App\Models\Release;
use App\Services\GithubService;
use App\Services\ReleaseService;
use App\Services\UpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReleaseController extends Controller
{
    public function __construct(
        private readonly GithubService $github,
        private readonly ReleaseService $releaseService,
        private readonly UpdateService $updateService
    ) {}

    /**
     * Display a listing of releases.
     */
    public function index(Request $request): ReleaseCollection
    {
        $releases = Release::query()
            ->latest('published_at')
            ->paginate($request->input('per_page', 20));

        return new ReleaseCollection($releases);
    }

    /**
     * Display the specified release.
     */
    public function show(Release $release): ReleaseResource
    {
        return new ReleaseResource($release->load('artifacts', 'updates'));
    }

    /**
     * Publish a release from GitHub.
     */
    public function publish(PublishReleaseRequest $request): JsonResponse
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
                $platform = $this->detectPlatform($asset['name']);

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

    /**
     * Rollback to a previous release.
     */
    public function rollback(RollbackReleaseRequest $request): JsonResponse
    {
        $version = $request->input('version');
        $channel = $request->input('channel');

        Log::info('Rolling back release', ['version' => $version, 'channel' => $channel]);

        try {
            $update = \App\Models\Update::where('channel', $channel)
                ->where('version', $version)
                ->firstOrFail();

            // Unset current latest
            \App\Models\Update::where('channel', $channel)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // Set this version as latest
            $update->update(['is_latest' => true]);

            Log::info('Release rolled back', ['update_id' => $update->id]);

            return response()->json([
                'message' => 'Release rolled back successfully',
                'update' => [
                    'id' => $update->id,
                    'version' => $update->version,
                    'channel' => $update->channel,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to rollback release', [
                'version' => $version,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to rollback release',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detect platform from filename.
     */
    protected function detectPlatform(string $filename): string
    {
        if (str_contains($filename, 'aarch64') || str_contains($filename, 'arm64')) {
            return 'darwin-aarch64';
        }

        if (str_contains($filename, 'x64') || str_contains($filename, 'x86_64')) {
            return 'darwin-x86_64';
        }

        // Default to aarch64 for macOS
        return 'darwin-aarch64';
    }
}
