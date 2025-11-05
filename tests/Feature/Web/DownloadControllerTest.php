<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Enums\ReleaseChannel;
use App\Models\Artifact;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_download_page_loads_successfully(): void
    {
        $response = $this->get('/download');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Download'));
    }

    public function test_download_page_displays_latest_stable_artifact(): void
    {
        $release = Release::factory()->create([
            'channel' => ReleaseChannel::STABLE,
            'version' => '1.0.0',
            'published_at' => now(),
        ]);

        $artifact = Artifact::factory()->create([
            'release_id' => $release->id,
            'platform' => 'darwin-aarch64',
            'url' => 'https://example.com/download.dmg',
            'size' => 44335104,
        ]);

        $response = $this->get('/download');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Download')
            ->has('artifact.platform')
            ->where('artifact.platform', 'darwin-aarch64')
            ->where('artifact.url', 'https://example.com/download.dmg')
        );
    }

    public function test_download_page_handles_no_artifacts_gracefully(): void
    {
        $response = $this->get('/download');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Download')
            ->where('artifact', null)
        );
    }

    public function test_download_page_only_shows_stable_releases(): void
    {
        $betaRelease = Release::factory()->create([
            'channel' => ReleaseChannel::BETA,
            'version' => '2.0.0-beta',
            'published_at' => now(),
        ]);

        Artifact::factory()->create([
            'release_id' => $betaRelease->id,
            'platform' => 'darwin-aarch64',
        ]);

        $stableRelease = Release::factory()->create([
            'channel' => ReleaseChannel::STABLE,
            'version' => '1.0.0',
            'published_at' => now()->subDay(),
        ]);

        Artifact::factory()->create([
            'release_id' => $stableRelease->id,
            'platform' => 'darwin-aarch64',
        ]);

        $response = $this->get('/download');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Download')
            ->has('artifact')
            ->has('artifact.release')
            ->where('artifact.release.version', '1.0.0')
            ->where('artifact.platform', 'darwin-aarch64')
        );
    }
}
