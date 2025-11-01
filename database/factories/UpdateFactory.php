<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Release;
use App\Models\Update;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Update>
 */
class UpdateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Update>
     */
    protected $model = Update::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $version = fake()->semver();
        $channel = fake()->randomElement(['stable', 'beta']);
        $publishedAt = fake()->dateTimeBetween('-90 days', 'now');

        return [
            'release_id' => Release::factory(),
            'channel' => $channel,
            'version' => $version,
            'manifest' => [
                'version' => $version,
                'notes' => fake()->paragraph(),
                'pub_date' => $publishedAt->format('c'),
                'platforms' => [
                    'darwin-aarch64' => [
                        'signature' => base64_encode(random_bytes(64)),
                        'url' => "https://github.com/honeymelon-app/honeymelon/releases/download/v{$version}/honeymelon-{$version}.dmg",
                        'sha256' => hash('sha256', fake()->uuid()),
                    ],
                ],
            ],
            'is_latest' => false,
            'published_at' => $publishedAt,
        ];
    }

    /**
     * Mark this update as the latest for its channel.
     */
    public function latest(): self
    {
        return $this->state(fn () => ['is_latest' => true]);
    }

    /**
     * Assign the update to a specific release.
     */
    public function forRelease(Release $release): self
    {
        return $this->state(fn () => [
            'release_id' => $release->id,
            'version' => $release->version,
            'channel' => $release->channel->value,
        ]);
    }
}
