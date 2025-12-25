<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Contracts\GitRepository;
use App\Enums\ReleaseChannel;
use App\Models\Artifact;
use App\Models\Product;
use App\Models\Release;
use App\Services\GitHubSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class GitHubSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Product::factory()->create();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Generate a complete GitHub release mock data structure.
     */
    protected function mockGithubRelease(array $overrides = []): array
    {
        $defaults = [
            'id' => 123,
            'github_id' => 123,
            'tag' => 'v1.0.0',
            'name' => 'Version 1.0.0',
            'notes' => 'Release notes',
            'published_at' => '2024-01-15T10:00:00Z',
            'created_at' => '2024-01-15T09:00:00Z',
            'prerelease' => false,
            'draft' => false,
            'author' => 'johndoe',
            'html_url' => 'https://github.com/test/repo/releases/tag/v1.0.0',
            'target_commitish' => 'main',
            'assets' => [],
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Generate a complete GitHub asset mock data structure.
     */
    protected function mockGithubAsset(array $overrides = []): array
    {
        $defaults = [
            'id' => 456,
            'name' => 'Honeymelon_1.0.0_aarch64.dmg',
            'url' => 'https://github.com/test/releases/download/v1.0.0/Honeymelon_1.0.0_aarch64.dmg',
            'size' => 50000000,
            'content_type' => 'application/x-apple-diskimage',
            'download_count' => 0,
            'state' => 'uploaded',
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ];

        return array_merge($defaults, $overrides);
    }

    public function test_syncs_new_release_from_github(): void
    {
        $githubReleases = [
            [
                'id' => 123,
                'github_id' => 123,
                'tag' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'notes' => 'Initial release',
                'published_at' => '2024-01-15T10:00:00Z',
                'created_at' => '2024-01-15T09:00:00Z',
                'prerelease' => false,
                'draft' => false,
                'author' => 'johndoe',
                'html_url' => 'https://github.com/test/repo/releases/tag/v1.0.0',
                'target_commitish' => 'main',
                'assets' => [
                    [
                        'id' => 456,
                        'name' => 'Honeymelon_1.0.0_aarch64.dmg',
                        'url' => 'https://github.com/test/releases/download/v1.0.0/Honeymelon_1.0.0_aarch64.dmg',
                        'size' => 50000000,
                        'content_type' => 'application/x-apple-diskimage',
                        'download_count' => 42,
                        'state' => 'uploaded',
                        'created_at' => '2024-01-15T10:00:00Z',
                        'updated_at' => '2024-01-15T10:00:00Z',
                    ],
                ],
            ],
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
            $mock->shouldReceive('fetchCommitShaForTag')
                ->with('v1.0.0')
                ->once()
                ->andReturn('abc123def456');
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $stats = $syncService->syncReleases();

        $this->assertEquals(1, $stats['created']);
        $this->assertEquals(0, $stats['updated']);
        $this->assertEquals(0, $stats['skipped']);
        $this->assertEquals(0, $stats['errors']);

        $this->assertDatabaseHas('releases', [
            'tag' => 'v1.0.0',
            'version' => '1.0.0',
            'name' => 'Version 1.0.0',
            'notes' => 'Initial release',
            'commit_hash' => 'abc123def456',
            'author' => 'johndoe',
            'html_url' => 'https://github.com/test/repo/releases/tag/v1.0.0',
            'target_commitish' => 'main',
            'github_id' => 123,
        ]);

        $this->assertDatabaseHas('artifacts', [
            'platform' => 'darwin-aarch64',
            'filename' => 'Honeymelon_1.0.0_aarch64.dmg',
            'source' => 'github',
            'content_type' => 'application/x-apple-diskimage',
            'download_count' => 42,
            'state' => 'uploaded',
            'github_id' => 456,
        ]);
    }

    public function test_skips_existing_unchanged_release(): void
    {
        $release = Release::factory()->create([
            'tag' => 'v1.0.0',
            'version' => '1.0.0',
            'name' => 'Version 1.0.0',
            'notes' => 'Initial release',
            'commit_hash' => 'abc123def456',
            'github_id' => 123,
            'author' => 'johndoe',
            'html_url' => 'https://github.com/test/repo/releases/tag/v1.0.0',
            'target_commitish' => 'main',
            'prerelease' => false,
            'draft' => false,
            'published_at' => '2024-01-15T10:00:00Z',
            'github_created_at' => '2024-01-15T09:00:00Z',
        ]);

        $githubReleases = [
            $this->mockGithubRelease([
                'id' => 123,
                'github_id' => 123,
                'tag' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'notes' => 'Initial release',
            ]),
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
            $mock->shouldReceive('fetchCommitShaForTag')
                ->with('v1.0.0')
                ->once()
                ->andReturn('abc123def456');
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $stats = $syncService->syncReleases();

        $this->assertEquals(0, $stats['created']);
        $this->assertEquals(0, $stats['updated']);
        $this->assertEquals(1, $stats['skipped']);
    }

    public function test_updates_release_when_notes_change(): void
    {
        $release = Release::factory()->create([
            'tag' => 'v1.0.0',
            'version' => '1.0.0',
            'notes' => 'Old notes',
            'commit_hash' => 'abc123def456',
            'github_id' => 123,
        ]);

        $githubReleases = [
            $this->mockGithubRelease([
                'id' => 123,
                'github_id' => 123,
                'tag' => 'v1.0.0',
                'notes' => 'Updated release notes',
            ]),
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
            $mock->shouldReceive('fetchCommitShaForTag')
                ->with('v1.0.0')
                ->once()
                ->andReturn('abc123def456');
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $stats = $syncService->syncReleases();

        $this->assertEquals(0, $stats['created']);
        $this->assertEquals(1, $stats['updated']);

        $release->refresh();
        $this->assertEquals('Updated release notes', $release->notes);
    }

    public function test_skips_draft_releases(): void
    {
        $githubReleases = [
            $this->mockGithubRelease([
                'tag' => 'v1.0.0',
                'name' => 'Draft Release',
                'notes' => 'Not ready yet',
                'draft' => true,
            ]),
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $stats = $syncService->syncReleases();

        $this->assertEquals(0, $stats['created']);
        $this->assertEquals(1, $stats['skipped']);

        $this->assertDatabaseMissing('releases', ['tag' => 'v1.0.0']);
    }

    public function test_detects_prerelease_as_beta_channel(): void
    {
        $githubReleases = [
            $this->mockGithubRelease([
                'tag' => 'v1.0.0',
                'name' => 'Beta Release',
                'notes' => 'Beta notes',
                'prerelease' => true,
            ]),
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
            $mock->shouldReceive('fetchCommitShaForTag')
                ->with('v1.0.0')
                ->once()
                ->andReturn('abc123def456');
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $syncService->syncReleases();

        $release = Release::where('tag', 'v1.0.0')->first();
        $this->assertEquals(ReleaseChannel::BETA, $release->channel);
    }

    public function test_detects_alpha_channel_from_tag(): void
    {
        $githubReleases = [
            $this->mockGithubRelease([
                'tag' => 'v1.0.0-alpha.1',
                'name' => 'Alpha Release',
                'notes' => 'Alpha notes',
                'prerelease' => true,
            ]),
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
            $mock->shouldReceive('fetchCommitShaForTag')
                ->with('v1.0.0-alpha.1')
                ->once()
                ->andReturn('abc123def456');
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $syncService->syncReleases();

        $release = Release::where('tag', 'v1.0.0-alpha.1')->first();
        $this->assertEquals(ReleaseChannel::ALPHA, $release->channel);
    }

    public function test_adds_new_artifacts_to_existing_release(): void
    {
        $release = Release::factory()->create([
            'tag' => 'v1.0.0',
            'version' => '1.0.0',
            'notes' => 'Initial release',
            'commit_hash' => 'abc123def456',
            'github_id' => 123,
        ]);

        $githubReleases = [
            $this->mockGithubRelease([
                'id' => 123,
                'github_id' => 123,
                'tag' => 'v1.0.0',
                'assets' => [
                    $this->mockGithubAsset([
                        'name' => 'Honeymelon_1.0.0_aarch64.dmg',
                        'size' => 50000000,
                    ]),
                ],
            ]),
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
            $mock->shouldReceive('fetchCommitShaForTag')
                ->with('v1.0.0')
                ->once()
                ->andReturn('abc123def456');
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $stats = $syncService->syncReleases();

        $this->assertEquals(0, $stats['created']);
        $this->assertEquals(1, $stats['updated']);

        $this->assertDatabaseHas('artifacts', [
            'release_id' => $release->id,
            'platform' => 'darwin-aarch64',
            'filename' => 'Honeymelon_1.0.0_aarch64.dmg',
        ]);
    }

    public function test_does_not_overwrite_r2_artifacts_with_github_urls(): void
    {
        $release = Release::factory()->create([
            'tag' => 'v1.0.0',
            'version' => '1.0.0',
            'name' => 'Version 1.0.0',
            'notes' => 'Release notes',
            'commit_hash' => 'abc123def456',
            'github_id' => 123,
            'author' => 'johndoe',
            'html_url' => 'https://github.com/test/repo/releases/tag/v1.0.0',
            'target_commitish' => 'main',
            'prerelease' => false,
            'draft' => false,
            'published_at' => '2024-01-15T10:00:00Z',
            'github_created_at' => '2024-01-15T09:00:00Z',
        ]);

        $artifact = Artifact::factory()->create([
            'release_id' => $release->id,
            'platform' => 'darwin-aarch64',
            'source' => 'r2',
            'filename' => 'Honeymelon_1.0.0_aarch64.dmg',
            'url' => 'https://r2.example.com/releases/1.0.0/Honeymelon.dmg',
            'size' => 50000000,
        ]);

        $githubReleases = [
            $this->mockGithubRelease([
                'id' => 123,
                'github_id' => 123,
                'tag' => 'v1.0.0',
                'assets' => [
                    $this->mockGithubAsset([
                        'name' => 'Honeymelon_1.0.0_aarch64.dmg',
                        'size' => 55000000,
                    ]),
                ],
            ]),
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
            $mock->shouldReceive('fetchCommitShaForTag')
                ->with('v1.0.0')
                ->once()
                ->andReturn('abc123def456');
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $stats = $syncService->syncReleases();

        $this->assertEquals(0, $stats['created']);
        $this->assertEquals(1, $stats['skipped']);

        $artifact->refresh();
        $this->assertEquals('r2', $artifact->source);
        $this->assertEquals('https://r2.example.com/releases/1.0.0/Honeymelon.dmg', $artifact->url);
        $this->assertEquals(50000000, $artifact->size);
    }

    public function test_does_not_create_duplicate_artifact_when_r2_artifact_exists(): void
    {
        $release = Release::factory()->create([
            'tag' => 'v1.0.0',
            'version' => '1.0.0',
            'notes' => 'Initial release',
            'commit_hash' => 'abc123def456',
            'github_id' => 123,
        ]);

        Artifact::factory()->create([
            'release_id' => $release->id,
            'platform' => 'darwin-aarch64',
            'source' => 'r2',
            'filename' => 'Honeymelon-macOS-aarch64.dmg',
            'url' => 'https://r2.example.com/releases/1.0.0/Honeymelon.dmg',
        ]);

        $githubReleases = [
            $this->mockGithubRelease([
                'id' => 123,
                'github_id' => 123,
                'tag' => 'v1.0.0',
                'assets' => [
                    $this->mockGithubAsset([
                        'name' => 'Honeymelon_1.0.0_aarch64.dmg',
                        'size' => 50000000,
                    ]),
                ],
            ]),
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
            $mock->shouldReceive('fetchCommitShaForTag')
                ->with('v1.0.0')
                ->once()
                ->andReturn('abc123def456');
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $syncService->syncReleases();

        $this->assertCount(1, $release->artifacts);
        $this->assertEquals('r2', $release->artifacts->first()->source);
    }
}
