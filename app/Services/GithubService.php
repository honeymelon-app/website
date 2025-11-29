<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GithubService
{
    public function __construct(
        private readonly string $owner,
        private readonly string $repo,
        private readonly ?string $token = null
    ) {}

    /**
     * Fetch a GitHub release by tag.
     *
     * @return array{notes: string, published_at: string, assets: array<int, array{name: string, url: string, size: int, sha256: ?string, signature: ?string}>}
     */
    public function fetchRelease(string $tag): array
    {
        $response = $this->client()
            ->get("/repos/{$this->owner}/{$this->repo}/releases/tags/{$tag}")
            ->throw();

        $data = $response->json();

        $assets = collect($data['assets'] ?? [])
            ->map(function (array $asset) {
                return [
                    'name' => $asset['name'],
                    'url' => $asset['browser_download_url'],
                    'size' => $asset['size'],
                    'sha256' => $this->extractSha256FromAssets($asset['name']),
                    'signature' => $this->extractSignatureFromAssets($asset['name']),
                ];
            })
            ->toArray();

        return [
            'notes' => $data['body'] ?? '',
            'published_at' => $data['published_at'] ?? now()->toIso8601String(),
            'assets' => $assets,
        ];
    }

    /**
     * Create the HTTP client with authentication.
     */
    protected function client(): PendingRequest
    {
        $client = Http::baseUrl('https://api.github.com')
            ->withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
            ]);

        if ($this->token) {
            $client->withToken($this->token);
        }

        return $client;
    }

    /**
     * Extract SHA256 from assets (look for .sha256 file).
     */
    protected function extractSha256FromAssets(string $assetName): ?string
    {
        // In a real implementation, we'd fetch the .sha256 file if it exists
        // For now, return null and let the caller handle it
        return null;
    }

    /**
     * Extract signature from assets (look for .sig file).
     */
    protected function extractSignatureFromAssets(string $assetName): ?string
    {
        // In a real implementation, we'd fetch the .sig file if it exists
        // For now, return null and let the caller handle it
        return null;
    }

    /**
     * Delete a GitHub release by tag.
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function deleteRelease(string $tag): bool
    {
        // First, get the release ID from the tag
        $response = $this->client()
            ->get("/repos/{$this->owner}/{$this->repo}/releases/tags/{$tag}");

        if ($response->status() === 404) {
            // Release doesn't exist, nothing to delete
            return true;
        }

        $response->throw();
        $releaseId = $response->json('id');

        // Delete the release
        $this->client()
            ->delete("/repos/{$this->owner}/{$this->repo}/releases/{$releaseId}")
            ->throw();

        return true;
    }

    /**
     * Delete a GitHub tag (git ref).
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function deleteTag(string $tag): bool
    {
        $ref = "tags/{$tag}";

        $response = $this->client()
            ->delete("/repos/{$this->owner}/{$this->repo}/git/refs/{$ref}");

        // 404 means tag doesn't exist, which is fine
        if ($response->status() === 404) {
            return true;
        }

        $response->throw();

        return true;
    }

    /**
     * Delete both the GitHub release and its tag.
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function deleteReleaseAndTag(string $tag): bool
    {
        // Delete release first (if it exists)
        $this->deleteRelease($tag);

        // Then delete the tag
        $this->deleteTag($tag);

        return true;
    }
}
