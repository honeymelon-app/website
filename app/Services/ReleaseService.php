<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Artifact;
use App\Models\Release;
use Illuminate\Support\Facades\Log;

class ReleaseService
{
    /**
     * Record a release from GitHub data.
     * Uses updateOrCreate for idempotency when webhooks are retried.
     *
     * @param  array{version: string, tag: string, commit_hash: string, channel: string, notes: string, published_at: string, major: bool, user_id: ?int}  $data
     */
    public function recordRelease(array $data): Release
    {
        Log::info('Recording release', ['version' => $data['version'], 'channel' => $data['channel']]);

        $release = Release::updateOrCreate(
            [
                'version' => $data['version'],
                'tag' => $data['tag'],
                'channel' => $data['channel'],
                'commit_hash' => $data['commit_hash'],
            ],
            [
                'notes' => $data['notes'],
                'published_at' => $data['published_at'],
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
     * @param  array{platform: string, source: string, filename: string, size: int, sha256: ?string, signature: ?string, notarized: bool, url: string, path: ?string}  $artifactData
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
                'size' => $artifactData['size'],
                'sha256' => $artifactData['sha256'],
                'signature' => $artifactData['signature'],
                'notarized' => $artifactData['notarized'],
                'url' => $artifactData['url'],
                'path' => $artifactData['path'] ?? null,
            ]
        );

        Log::info('Artifact attached', [
            'artifact_id' => $artifact->id,
            'was_recently_created' => $artifact->wasRecentlyCreated,
        ]);

        return $artifact;
    }
}
