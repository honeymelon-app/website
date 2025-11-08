<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Enums\ReleaseChannel;
use App\Jobs\ProcessGithubReleaseJob;
use App\Models\User;
use App\Services\GithubService;
use App\Services\ReleaseService;
use App\Services\UpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessGithubReleaseJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_attaches_uploaded_artifacts_from_payload(): void
    {
        $user = User::factory()->create();

        $payload = [
            'notes' => 'Release with uploaded artifacts',
            'published_at' => now()->toIso8601String(),
            'artifacts' => [
                [
                    'platform' => 'darwin-aarch64',
                    'source' => 'r2',
                    'filename' => 'honeymelon.dmg',
                    'url' => 'https://r2.example.com/releases/v1.0.0/honeymelon.dmg',
                    'path' => 'releases/1.0.0/honeymelon.dmg',
                    'size' => 98_765_432,
                    'sha256' => 'abc123def456',
                    'signature' => 'sig==',
                    'notarized' => true,
                ],
            ],
        ];

        $job = new ProcessGithubReleaseJob(
            tag: 'v1.0.0',
            channel: ReleaseChannel::STABLE,
            version: '1.0.0',
            commitHash: 'deadbeef',
            isMajor: true,
            userId: $user->id,
            payload: $payload,
        );

        $githubService = $this->createMock(GithubService::class);
        $githubService->expects($this->never())->method('fetchRelease');

        $job->handle(
            $githubService,
            app(ReleaseService::class),
            app(UpdateService::class),
        );

        $this->assertDatabaseHas('releases', [
            'tag' => 'v1.0.0',
            'version' => '1.0.0',
            'channel' => ReleaseChannel::STABLE->value,
            'notes' => 'Release with uploaded artifacts',
        ]);

        $this->assertDatabaseHas('artifacts', [
            'platform' => 'darwin-aarch64',
            'source' => 'r2',
            'url' => 'https://r2.example.com/releases/v1.0.0/honeymelon.dmg',
            'path' => 'releases/1.0.0/honeymelon.dmg',
            'sha256' => 'abc123def456',
            'signature' => 'sig==',
        ]);
    }
}
