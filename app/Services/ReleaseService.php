<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Artifact;
use App\Models\Product;
use App\Models\Release;
use Illuminate\Support\Facades\Log;

class ReleaseService
{
    /**
     * Record a release from GitHub data.
     * Uses updateOrCreate for idempotency when webhooks are retried.
     *
     * @param  array{version: string, name?: string, tag: string, commit_hash: string, author?: ?string, html_url?: string, target_commitish?: string, github_id?: int, channel: string, prerelease?: bool, draft?: bool, notes: string, published_at: string, github_created_at?: string, major: bool, user_id: ?int}  $data
     */
    public function recordRelease(array $data): Release
    {
        Log::info('Recording release', ['version' => $data['version'], 'channel' => $data['channel']]);

        $product = Product::query()->where('is_active', true)->first();

        if (! $product) {
            throw new \RuntimeException('No active product found to associate with release');
        }

        $release = Release::updateOrCreate(
            [
                'version' => $data['version'],
                'tag' => $data['tag'],
                'channel' => $data['channel'],
                'commit_hash' => $data['commit_hash'],
            ],
            [
                'product_id' => $product->id,
                'name' => $data['name'] ?? null,
                'author' => $data['author'] ?? null,
                'html_url' => $data['html_url'] ?? null,
                'target_commitish' => $data['target_commitish'] ?? null,
                'github_id' => $data['github_id'] ?? null,
                'prerelease' => $data['prerelease'] ?? false,
                'draft' => $data['draft'] ?? false,
                'notes' => $data['notes'],
                'published_at' => $data['published_at'],
                'github_created_at' => $data['github_created_at'] ?? null,
                'major' => $data['major'],
                'user_id' => $data['user_id'] ?? null,
            ]
        );

        Log::info('Release recorded', [
            'release_id' => $release->id,
            'was_recently_created' => $release->wasRecentlyCreated,
        ]);

        return $release;
    }

    /**
     * Attach an artifact to a release.
     * Uses updateOrCreate for idempotency when webhooks are retried.
     *
     * @param  array{platform: string, source: string, state?: string, filename: string, content_type?: string, size: int, download_count?: int, sha256: ?string, signature: ?string, notarized: bool, url: string, path: ?string, github_id?: int, github_created_at?: string, github_updated_at?: string}  $artifactData
     */
    public function attachArtifact(Release $release, array $artifactData): Artifact
    {
        Log::info('Attaching artifact to release', [
            'release_id' => $release->id,
            'platform' => $artifactData['platform'],
        ]);

        $artifact = $release->artifacts()->updateOrCreate(
            [
                'platform' => $artifactData['platform'],
                'filename' => $artifactData['filename'],
            ],
            [
                'source' => $artifactData['source'],
                'state' => $artifactData['state'] ?? null,
                'content_type' => $artifactData['content_type'] ?? null,
                'size' => $artifactData['size'],
                'download_count' => $artifactData['download_count'] ?? 0,
                'sha256' => $artifactData['sha256'],
                'signature' => $artifactData['signature'],
                'notarized' => $artifactData['notarized'],
                'url' => $artifactData['url'],
                'path' => $artifactData['path'] ?? null,
                'github_id' => $artifactData['github_id'] ?? null,
                'github_created_at' => $artifactData['github_created_at'] ?? null,
                'github_updated_at' => $artifactData['github_updated_at'] ?? null,
            ]
        );

        Log::info('Artifact attached', [
            'artifact_id' => $artifact->id,
            'was_recently_created' => $artifact->wasRecentlyCreated,
        ]);

        return $artifact;
    }
}
