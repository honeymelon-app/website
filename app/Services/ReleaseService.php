<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GitRepository;
use App\Contracts\ReleaseManager;
use App\Models\Artifact;
use App\Models\Release;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ReleaseService implements ReleaseManager
{
    public function __construct(private GitRepository $gitRepository) {}

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

    /**
     * Delete a release and its associated artifacts, including from GitHub.
     */
    public function deleteRelease(Release $release): void
    {
        $tag = $release->tag;
        $version = $release->version;

        Log::info('Deleting release', [
            'release_id' => $release->id,
            'version' => $version,
            'tag' => $tag,
        ]);

        DB::transaction(function () use ($release, $tag): void {
            $release->artifacts()->delete();
            $release->delete();

            try {
                $this->gitRepository->deleteReleaseAndTag($tag);
                Log::info('GitHub release and tag deleted', ['tag' => $tag]);
            } catch (\Exception $e) {
                Log::warning('Failed to delete GitHub release/tag', [
                    'tag' => $tag,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        Log::info('Release deleted successfully', ['version' => $version]);
    }
}
