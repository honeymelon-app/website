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
        tap($request->validated(), function (array $data): void {
            ProcessGithubReleaseJob::dispatch(
                tag: $data['tag'],
                channel: ReleaseChannel::from($data['channel']),
                version: $data['version'],
                commitHash: $data['commit_hash'],
                isMajor: $data['major'],
                userId: auth()->id(),
            );
        });

        return response()->json([], 201);
    }
}
