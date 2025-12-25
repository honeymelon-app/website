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
                'name' => $data['name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'published_at' => $data['published_at'],
                'author' => $data['author'] ?? null,
                'html_url' => $data['html_url'] ?? null,
                'target_commitish' => $data['target_commitish'] ?? null,
                'github_id' => $data['github_id'] ?? null,
                'prerelease' => $data['prerelease'] ?? false,
                'draft' => $data['draft'] ?? false,
                'github_created_at' => $data['github_created_at'] ?? null,
                'artifacts' => $data['artifacts'] ?? [],
            ],
        );

        return response()->json([], 201);
    }
}
