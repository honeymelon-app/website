<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Services\GithubService;
use Illuminate\Container\Attributes\Bind;

#[Bind(GithubService::class)]
interface GitRepository
{
    /**
     * Fetch all releases.
     *
     * @return array<int, array{id: int, tag: string, name: string, notes: string, published_at: string, prerelease: bool, draft: bool, assets: array<int, array{name: string, url: string, size: int}>}>
     */
    public function fetchAllReleases(): array;

    /**
     * Fetch a release by tag.
     *
     * @return array{notes: string, published_at: string, assets: array<int, array{name: string, url: string, size: int, sha256: ?string, signature: ?string}>}
     */
    public function fetchRelease(string $tag): array;

    /**
     * Delete a release by tag.
     */
    public function deleteRelease(string $tag): bool;

    /**
     * Delete a tag.
     */
    public function deleteTag(string $tag): bool;

    /**
     * Delete both release and tag.
     */
    public function deleteReleaseAndTag(string $tag): bool;

    /**
     * Fetch the commit SHA for a specific tag.
     *
     * This resolves the actual commit SHA that a tag points to,
     * which may differ from the target_commitish (branch name) in release data.
     */
    public function fetchCommitShaForTag(string $tag): ?string;
}
