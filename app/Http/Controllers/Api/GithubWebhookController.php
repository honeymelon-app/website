<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

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
        ProcessGithubReleaseJob::dispatch(
            tag: $request->input('tag'),
            channel: $request->input('channel'),
            version: $request->input('version'),
            commitHash: $request->input('commit_hash'),
            isMajor: $request->input('major', false),
            userId: auth()->id(),
        );

        return response()->json([], 201);
    }
}
