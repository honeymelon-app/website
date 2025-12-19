<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Services\GithubService;
use Illuminate\Container\Attributes\Bind;

#[Bind(GithubService::class)]
interface GitRepository
{
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
}
