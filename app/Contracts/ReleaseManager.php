<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Artifact;
use App\Models\Release;
use App\Services\ReleaseService;
use Illuminate\Container\Attributes\Bind;

#[Bind(ReleaseService::class)]
interface ReleaseManager
{
    /**
     * Record a new release.
     *
     * @param  array{version: string, tag: string, commit_hash: string, channel: string, notes: string, published_at: string, major: bool, user_id: ?int}  $data
     */
    public function recordRelease(array $data): Release;

    /**
     * Attach an artifact to a release.
     *
     * @param  array{platform: string, source: string, filename: string, size: int, sha256: ?string, signature: ?string, notarized: bool, url: string, path: ?string}  $artifactData
     */
    public function attachArtifact(Release $release, array $artifactData): Artifact;

    /**
     * Delete a release and its associated artifacts.
     */
    public function deleteRelease(Release $release): void;
}
