<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GitRepository;
use Fetch\Http\Response;
use Illuminate\Container\Attributes\Config;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
class GithubService implements GitRepository
{
    private const BASE_URL = 'https://api.github.com';

    public function __construct(
        #[Config('services.github.owner')]
        private readonly string $owner,
        #[Config('services.github.repo')]
        private readonly string $repo,
        #[Config('services.github.token')]
        private readonly ?string $token = null
    ) {}

    /**
     * Fetch all releases from GitHub.
     *
     * @return array<int, array{id: int, tag: string, name: string, notes: string, published_at: string, prerelease: bool, draft: bool, assets: array<int, array{name: string, url: string, size: int}>}>
     */
    public function fetchAllReleases(): array
    {
        $releases = [];
        $page = 1;
        $perPage = 100;

        do {
            $data = $this->get("/repos/{$this->owner}/{$this->repo}/releases?per_page={$perPage}&page={$page}");

            foreach ($data as $release) {
                $releases[] = $this->mapRelease($release);
            }

            $page++;
        } while (count($data) === $perPage);

        return $releases;
    }

    /**
     * Fetch a GitHub release by tag.
     *
     * @return array{notes: string, published_at: string, assets: array<int, array{name: string, url: string, size: int, sha256: ?string, signature: ?string}>}
     */
    public function fetchRelease(string $tag): array
    {
        $data = $this->get("/repos/{$this->owner}/{$this->repo}/releases/tags/{$tag}");

        return [
            'notes' => $data['body'] ?? '',
            'published_at' => $data['published_at'] ?? now()->toIso8601String(),
            'assets' => $this->mapAssets($data['assets'] ?? [], withChecksums: true),
        ];
    }

    /**
     * Delete a GitHub release by tag.
     */
    public function deleteRelease(string $tag): bool
    {
        $response = $this->request('GET', "/repos/{$this->owner}/{$this->repo}/releases/tags/{$tag}");

        if ($response->status() === 404) {
            return true;
        }

        $this->request('DELETE', "/repos/{$this->owner}/{$this->repo}/releases/{$response->json()['id']}");

        return true;
    }

    /**
     * Delete a GitHub tag (git ref).
     */
    public function deleteTag(string $tag): bool
    {
        $response = $this->request('DELETE', "/repos/{$this->owner}/{$this->repo}/git/refs/tags/{$tag}");

        return $response->status() === 404 || $response->successful();
    }

    /**
     * Delete both the GitHub release and its tag.
     */
    public function deleteReleaseAndTag(string $tag): bool
    {
        $this->deleteRelease($tag);
        $this->deleteTag($tag);

        return true;
    }

    /**
     * Fetch the commit SHA for a specific tag.
     *
     * This resolves the actual commit SHA that a tag points to by fetching
     * the git ref. Tags can point either directly to a commit (lightweight tag)
     * or to a tag object (annotated tag) which in turn points to a commit.
     */
    public function fetchCommitShaForTag(string $tag): ?string
    {
        $response = $this->request('GET', "/repos/{$this->owner}/{$this->repo}/git/ref/tags/{$tag}");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $sha = $data['object']['sha'] ?? null;
        $type = $data['object']['type'] ?? null;

        if ($sha === null) {
            return null;
        }

        if ($type === 'commit') {
            return $sha;
        }

        if ($type === 'tag') {
            return $this->fetchTagObjectCommitSha($sha);
        }

        return null;
    }

    /**
     * Fetch the commit SHA from an annotated tag object.
     *
     * Annotated tags point to a tag object which contains the actual commit SHA.
     */
    protected function fetchTagObjectCommitSha(string $tagSha): ?string
    {
        $response = $this->request('GET', "/repos/{$this->owner}/{$this->repo}/git/tags/{$tagSha}");

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return $data['object']['sha'] ?? null;
    }

    /**
     * Make a GET request and return JSON data.
     *
     * @return array<string, mixed>
     */
    protected function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint)->json();
    }

    /**
     * Make an HTTP request.
     */
    protected function request(string $method, string $endpoint): Response
    {
        $options = [
            'method' => $method,
            'headers' => $this->buildHeaders(),
        ];

        return fetch(self::BASE_URL.$endpoint, $options);
    }

    /**
     * Build request headers including authentication.
     *
     * @return array<string, string>
     */
    protected function buildHeaders(): array
    {
        $headers = ['Accept' => 'application/vnd.github.v3+json'];

        if ($this->token) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }

        return $headers;
    }

    /**
     * Map a GitHub release to our internal format.
     *
     * @param  array<string, mixed>  $release
     * @return array{id: int, tag: string, name: string, notes: string, published_at: string, prerelease: bool, draft: bool, assets: array<int, array{name: string, url: string, size: int}>}
     */
    protected function mapRelease(array $release): array
    {
        return [
            'id' => $release['id'],
            'tag' => $release['tag_name'],
            'name' => $release['name'] ?? $release['tag_name'],
            'notes' => $release['body'] ?? '',
            'published_at' => $release['published_at'] ?? $release['created_at'],
            'prerelease' => $release['prerelease'] ?? false,
            'draft' => $release['draft'] ?? false,
            'assets' => $this->mapAssets($release['assets'] ?? []),
        ];
    }

    /**
     * Map GitHub assets to our internal format.
     *
     * @param  array<int, array<string, mixed>>  $assets
     * @return array<int, array{name: string, url: string, size: int, sha256?: ?string, signature?: ?string}>
     */
    protected function mapAssets(array $assets, bool $withChecksums = false): array
    {
        return array_map(function (array $asset) use ($withChecksums) {
            $mapped = [
                'name' => $asset['name'],
                'url' => $asset['browser_download_url'],
                'size' => $asset['size'],
            ];

            if ($withChecksums) {
                $mapped['sha256'] = null;
                $mapped['signature'] = null;
            }

            return $mapped;
        }, $assets);
    }
}
