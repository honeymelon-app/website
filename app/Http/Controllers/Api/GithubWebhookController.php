<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ReleaseChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGithubReleaseWebhookRequest;
use App\Jobs\ProcessGithubReleaseJob;
use App\Models\Release;
use Illuminate\Http\JsonResponse;

class GithubWebhookController extends Controller
{
    /**
     * Handle incoming GitHub release webhook.
     */
    public function store(StoreGithubReleaseWebhookRequest $request): JsonResponse
    {
        $data = $request->validated();

        ProcessGithubReleaseJob::dispatch(
            tag: $data['tag'],
            channel: ReleaseChannel::from($data['channel']),
            version: $data['version'],
            commitHash: $data['commit_hash'],
            isMajor: (bool) ($data['major'] ?? false),
            userId: auth()->id(),
            payload: [
                'notes' => $data['notes'],
                'published_at' => $data['published_at'],
                'artifacts' => $data['artifacts'] ?? [],
            ],
        );

        return response()->json([], 201);
    }
}
