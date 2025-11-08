<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UploadArtifactTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploads_artifact_and_returns_url(): void
    {
        Storage::fake('private');
        config(['filesystems.disks.private.url' => 'https://r2.example.com/private']);

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('Honeymelon-arm64.dmg', 1024, 'application/x-apple-diskimage');
        $expectedHash = hash_file('sha256', $file->getRealPath());
        $signature = base64_encode(random_bytes(64));

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->post('/api/artifacts/upload', [
                'platform' => 'darwin-aarch64',
                'artifact' => $file,
                'signature' => $signature,
                'notarized' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.platform', 'darwin-aarch64')
            ->assertJsonPath('data.sha256', $expectedHash)
            ->assertJsonPath('data.signature', $signature)
            ->assertJsonPath('data.source', 'r2');

        $path = $response->json('data.path');
        $this->assertNotEmpty($path);

        Storage::disk('private')->assertExists($path);

        $this->assertSame(
            'https://r2.example.com/private/'.$path,
            $response->json('data.url')
        );
    }

    public function test_requires_authentication(): void
    {
        Storage::fake('private');

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->post('/api/artifacts/upload', [
                'platform' => 'darwin-aarch64',
                'artifact' => UploadedFile::fake()->create('build.dmg'),
            ]);

        $response->assertUnauthorized();
    }

    public function test_validates_required_fields(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->post('/api/artifacts/upload', [
                'platform' => 'darwin-aarch64',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['artifact']);
    }
}
