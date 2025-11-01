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
     *
     * @param  array{version: string, tag: string, commit_hash: string, channel: string, notes: string, published_at: string, major: bool, user_id: ?int}  $data
     */
    public function recordRelease(array $data): Release
    {
        Log::info('Recording release', ['version' => $data['version'], 'channel' => $data['channel']]);

        $release = Release::create($data);

        Log::info('Release recorded', ['release_id' => $release->id]);

        return $release;
    }

    /**
     * Attach an artifact to a release.
     *
     * @param  array{platform: string, source: string, filename: string, size: int, sha256: ?string, signature: ?string, notarized: bool, url: string, path: ?string}  $artifactData
     */
    public function attachArtifact(Release $release, array $artifactData): Artifact
    {
        Log::info('Attaching artifact to release', [
            'release_id' => $release->id,
            'platform' => $artifactData['platform'],
        ]);

        $artifact = $release->artifacts()->create($artifactData);

        Log::info('Artifact attached', ['artifact_id' => $artifact->id]);

        return $artifact;
    }
}
