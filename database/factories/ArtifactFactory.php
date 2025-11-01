<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Artifact;
use App\Models\Release;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Artifact>
 */
class ArtifactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Artifact>
     */
    protected $model = Artifact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = 'honeymelon-'.fake()->semver().'.dmg';

        return [
            'release_id' => Release::factory(),
            'platform' => 'darwin-aarch64',
            'source' => 'github',         // or r2 / s3
            'filename' => $filename,
            'size' => fake()->numberBetween(25_000_000, 120_000_000),
            'sha256' => hash('sha256', fake()->uuid()),
            'signature' => base64_encode(random_bytes(64)),
            'notarized' => true,
            'url' => 'https://github.com/honeymelon-app/honeymelon/releases/download/v'.fake()->semver()."/{$filename}",
            'path' => null,             // for r2/s3
        ];
    }

    /**
     * Assign the artifact to a specific release.
     */
    public function forRelease(Release $release): self
    {
        return $this->state(fn () => ['release_id' => $release->id]);
    }

    /**
     * Configure the artifact for a specific platform.
     */
    public function forPlatform(string $platform): self
    {
        return $this->state(fn () => ['platform' => $platform]);
    }
}
