<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Contracts\GitRepository;
use App\Enums\ReleaseChannel;
use App\Models\Artifact;
use App\Models\Release;
use App\Services\GitHubSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class GitHubSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_syncs_new_release_from_github(): void
    {
        $githubReleases = [
            [
                'id' => 123,
                'tag' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'notes' => 'Initial release',
                'published_at' => '2024-01-15T10:00:00Z',
                'prerelease' => false,
                'draft' => false,
                'target_commitish' => 'abc123',
                'assets' => [
                    [
                        'name' => 'Honeymelon_1.0.0_aarch64.dmg',
                        'url' => 'https://github.com/test/releases/download/v1.0.0/Honeymelon_1.0.0_aarch64.dmg',
                        'size' => 50000000,
                    ],
                ],
            ],
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
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
            'notes' => 'Initial release',
        ]);

        $this->assertDatabaseHas('artifacts', [
            'platform' => 'darwin-aarch64',
            'filename' => 'Honeymelon_1.0.0_aarch64.dmg',
            'source' => 'github',
        ]);
    }

    public function test_skips_existing_unchanged_release(): void
    {
        $release = Release::factory()->create([
            'tag' => 'v1.0.0',
            'version' => '1.0.0',
            'notes' => 'Initial release',
            'commit_hash' => 'abc123',
        ]);

        $githubReleases = [
            [
                'id' => 123,
                'tag' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'notes' => 'Initial release',
                'published_at' => '2024-01-15T10:00:00Z',
                'prerelease' => false,
                'draft' => false,
                'target_commitish' => 'abc123',
                'assets' => [],
            ],
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
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
            'commit_hash' => 'abc123',
        ]);

        $githubReleases = [
            [
                'id' => 123,
                'tag' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'notes' => 'Updated release notes',
                'published_at' => '2024-01-15T10:00:00Z',
                'prerelease' => false,
                'draft' => false,
                'target_commitish' => 'abc123',
                'assets' => [],
            ],
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
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
            [
                'id' => 123,
                'tag' => 'v1.0.0',
                'name' => 'Draft Release',
                'notes' => 'Not ready yet',
                'published_at' => '2024-01-15T10:00:00Z',
                'prerelease' => false,
                'draft' => true,
                'target_commitish' => 'abc123',
                'assets' => [],
            ],
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
            [
                'id' => 123,
                'tag' => 'v1.0.0',
                'name' => 'Beta Release',
                'notes' => 'Beta notes',
                'published_at' => '2024-01-15T10:00:00Z',
                'prerelease' => true,
                'draft' => false,
                'target_commitish' => 'abc123',
                'assets' => [],
            ],
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $syncService->syncReleases();

        $release = Release::where('tag', 'v1.0.0')->first();
        $this->assertEquals(ReleaseChannel::BETA, $release->channel);
    }

    public function test_detects_alpha_channel_from_tag(): void
    {
        $githubReleases = [
            [
                'id' => 123,
                'tag' => 'v1.0.0-alpha.1',
                'name' => 'Alpha Release',
                'notes' => 'Alpha notes',
                'published_at' => '2024-01-15T10:00:00Z',
                'prerelease' => true,
                'draft' => false,
                'target_commitish' => 'abc123',
                'assets' => [],
            ],
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
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
            'commit_hash' => 'abc123',
        ]);

        $githubReleases = [
            [
                'id' => 123,
                'tag' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'notes' => 'Initial release',
                'published_at' => '2024-01-15T10:00:00Z',
                'prerelease' => false,
                'draft' => false,
                'target_commitish' => 'abc123',
                'assets' => [
                    [
                        'name' => 'Honeymelon_1.0.0_aarch64.dmg',
                        'url' => 'https://github.com/test/releases/download/v1.0.0/Honeymelon_1.0.0_aarch64.dmg',
                        'size' => 50000000,
                    ],
                ],
            ],
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
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
            'notes' => 'Initial release',
            'commit_hash' => 'abc123',
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
            [
                'id' => 123,
                'tag' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'notes' => 'Initial release',
                'published_at' => '2024-01-15T10:00:00Z',
                'prerelease' => false,
                'draft' => false,
                'target_commitish' => 'abc123',
                'assets' => [
                    [
                        'name' => 'Honeymelon_1.0.0_aarch64.dmg',
                        'url' => 'https://github.com/test/releases/download/v1.0.0/Honeymelon_1.0.0_aarch64.dmg',
                        'size' => 55000000,
                    ],
                ],
            ],
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
            'commit_hash' => 'abc123',
        ]);

        Artifact::factory()->create([
            'release_id' => $release->id,
            'platform' => 'darwin-aarch64',
            'source' => 'r2',
            'filename' => 'Honeymelon-macOS-aarch64.dmg',
            'url' => 'https://r2.example.com/releases/1.0.0/Honeymelon.dmg',
        ]);

        $githubReleases = [
            [
                'id' => 123,
                'tag' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'notes' => 'Initial release',
                'published_at' => '2024-01-15T10:00:00Z',
                'prerelease' => false,
                'draft' => false,
                'target_commitish' => 'abc123',
                'assets' => [
                    [
                        'name' => 'Honeymelon_1.0.0_aarch64.dmg',
                        'url' => 'https://github.com/test/releases/download/v1.0.0/Honeymelon_1.0.0_aarch64.dmg',
                        'size' => 50000000,
                    ],
                ],
            ],
        ];

        $this->mock(GitRepository::class, function (MockInterface $mock) use ($githubReleases) {
            $mock->shouldReceive('fetchAllReleases')
                ->once()
                ->andReturn($githubReleases);
        });

        $syncService = $this->app->make(GitHubSyncService::class);
        $syncService->syncReleases();

        $this->assertCount(1, $release->artifacts);
        $this->assertEquals('r2', $release->artifacts->first()->source);
    }
}
