<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Artifact;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReconcileArtifactsFromStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dry_runs_without_creating_artifacts(): void
    {
        Storage::fake('r2');

        $release = Release::factory()->forVersion('1.2.3')->create();

        Storage::disk('r2')->put('releases/darwin-aarch64/20250101000000-abc123-honeymelon-1.2.3.dmg', 'test');

        $this->artisan('artifacts:reconcile-storage --disk=r2 --prefix=releases/')
            ->assertExitCode(0);

        $this->assertDatabaseCount('artifacts', 0);
        $this->assertSame(0, Artifact::query()->where('release_id', $release->id)->count());
    }

    public function test_it_creates_missing_artifacts_when_apply_is_set(): void
    {
        Storage::fake('r2');

        $release = Release::factory()->forVersion('1.2.3')->create();

        Storage::disk('r2')->put('releases/darwin-aarch64/20250101000000-abc123-honeymelon-1.2.3.dmg', str_repeat('a', 10));

        $this->artisan('artifacts:reconcile-storage --disk=r2 --prefix=releases/ --apply')
            ->assertExitCode(0);

        $artifact = Artifact::query()->where('release_id', $release->id)->first();

        $this->assertNotNull($artifact);
        $this->assertSame('darwin-aarch64', $artifact->platform);
        $this->assertSame('r2', $artifact->source);
        $this->assertSame('honeymelon-1.2.3.dmg', $artifact->filename);
        $this->assertSame('releases/darwin-aarch64/20250101000000-abc123-honeymelon-1.2.3.dmg', $artifact->path);
    }
}
