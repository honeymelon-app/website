<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGithubReleaseWebhookRequest;
use App\Jobs\ProcessGithubReleaseJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GithubWebhookController extends Controller
{
    /**
     * Handle incoming GitHub release webhook.
     *
     * This endpoint should be triggered by your GitHub Actions workflow
     * when a new release is created. It will:
     * 1. Validate the webhook signature (if secret is configured)
     * 2. Dispatch a job to fetch release data from GitHub
     * 3. Create Release, Artifact, and Update records
     *
     * Expected payload:
     * {
     *   "tag": "v1.0.0",
     *   "version": "1.0.0",
     *   "channel": "stable",
     *   "commit_hash": "abc123def456",
     *   "is_major": false
     * }
     */
    public function store(StoreGithubReleaseWebhookRequest $request): JsonResponse
    {
        Log::info('Received GitHub release webhook', [
            'tag' => $request->input('tag'),
            'version' => $request->input('version'),
            'channel' => $request->input('channel'),
        ]);

        try {
            // Dispatch job to process the release asynchronously
            ProcessGithubReleaseJob::dispatch(
                tag: $request->input('tag'),
                channel: $request->getChannel(),
                version: $request->input('version'),
                commitHash: $request->input('commit_hash'),
                isMajor: $request->boolean('is_major', false),
                userId: $request->user()?->id
            );

            return response()->json([
                'message' => 'Release webhook received and queued for processing',
                'tag' => $request->input('tag'),
                'version' => $request->input('version'),
                'channel' => $request->input('channel'),
            ], 202);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch GitHub release job', [
                'tag' => $request->input('tag'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to process release webhook',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
