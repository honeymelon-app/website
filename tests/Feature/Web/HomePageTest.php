<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Enums\ReleaseChannel;
use App\Models\Artifact;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Welcome'));
    }

    public function test_home_page_renders_latest_stable_artifact_with_release(): void
    {
        $release = Release::factory()->create([
            'channel' => ReleaseChannel::STABLE,
            'version' => '1.2.3',
            'published_at' => now(),
        ]);

        Artifact::factory()->create([
            'release_id' => $release->id,
            'platform' => 'darwin-aarch64',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Welcome')
                ->has('artifact')
                ->has('artifact.release')
                ->where('artifact.release.version', '1.2.3')
        );
    }
}
