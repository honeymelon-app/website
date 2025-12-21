<?php

declare(strict_types=1);

namespace Tests\Feature\AttributeRefactor;

use App\Models\Artifact;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test that #[ObservedBy] attributes work correctly.
 */
class ObserverAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_artifact_observer_deletes_s3_file_when_artifact_deleted(): void
    {
        Storage::fake('s3');

        $release = Release::factory()->create();
        $artifact = Artifact::factory()->create([
            'release_id' => $release->id,
            'source' => 's3',
            'path' => 'artifacts/test-file.dmg',
        ]);

        // Create a fake file in S3
        Storage::disk('s3')->put($artifact->path, 'fake content');
        $this->assertTrue(Storage::disk('s3')->exists($artifact->path));

        // Delete the artifact
        $artifact->delete();

        // File should be deleted from S3
        $this->assertFalse(Storage::disk('s3')->exists($artifact->path));
    }

    public function test_artifact_observer_skips_deletion_for_non_s3_sources(): void
    {
        Storage::fake('s3');

        $release = Release::factory()->create();
        $artifact = Artifact::factory()->create([
            'release_id' => $release->id,
            'source' => 'github',
            'path' => 'artifacts/test-file.dmg',
        ]);

        // Create a fake file in S3
        Storage::disk('s3')->put($artifact->path, 'fake content');
        $this->assertTrue(Storage::disk('s3')->exists($artifact->path));

        // Delete the artifact
        $artifact->delete();

        // File should NOT be deleted from S3 (source is github, not s3)
        $this->assertTrue(Storage::disk('s3')->exists($artifact->path));
    }

    public function test_release_observer_deletes_artifacts_when_release_deleted(): void
    {
        Storage::fake('s3');

        $release = Release::factory()->create();

        $artifact1 = Artifact::factory()->create([
            'release_id' => $release->id,
            'source' => 's3',
            'path' => 'artifacts/file1.dmg',
        ]);

        $artifact2 = Artifact::factory()->create([
            'release_id' => $release->id,
            'source' => 's3',
            'path' => 'artifacts/file2.dmg',
        ]);

        Storage::disk('s3')->put($artifact1->path, 'content 1');
        Storage::disk('s3')->put($artifact2->path, 'content 2');

        // Delete the release
        $release->delete();

        // Artifacts should be deleted
        $this->assertDatabaseMissing('artifacts', ['id' => $artifact1->id]);
        $this->assertDatabaseMissing('artifacts', ['id' => $artifact2->id]);

        // S3 files should be deleted
        $this->assertFalse(Storage::disk('s3')->exists($artifact1->path));
        $this->assertFalse(Storage::disk('s3')->exists($artifact2->path));
    }
}
