<?php

namespace Database\Factories;

use App\Models\Release;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Release>
 */
class ReleaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Release>
     */
    protected $model = Release::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // semantic version pieces
        $major = fake()->numberBetween(0, 2);
        $minor = fake()->numberBetween(0, 9);
        $patch = fake()->numberBetween(0, 20);
        $version = "{$major}.{$minor}.{$patch}";

        $channel = Arr::random(['stable', 'beta']);

        return [
            // id is UUID via HasUuids
            'version' => $version,
            'tag' => "v{$version}",
            'commit_hash' => bin2hex(random_bytes(20)), // 40-hex like git SHA1
            'channel' => $channel,
            'notes' => fake()->paragraphs(asText: true),
            'published_at' => fake()->dateTimeBetween('-180 days', 'now'),
            'major' => $minor === 0 && $patch === 0, // mark x.0.0 as major
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
        ];
    }

    /**
     * Configure the factory for a stable release.
     */
    public function stable(): self
    {
        return $this->state(fn () => ['channel' => 'stable']);
    }

    /**
     * Configure the factory for a beta release.
     */
    public function beta(): self
    {
        return $this->state(fn () => ['channel' => 'beta']);
    }

    /**
     * Configure the factory for a major release.
     */
    public function majorRelease(): self
    {
        return $this->state(function () {
            $maj = fake()->numberBetween(1, 3);
            $version = "{$maj}.0.0";

            return [
                'version' => $version,
                'tag' => "v{$version}",
                'major' => true,
            ];
        });
    }

    /**
     * Configure the factory for a specific version and channel.
     */
    public function forVersion(string $version, string $channel = 'stable'): self
    {
        return $this->state(function () use ($version, $channel) {
            return [
                'version' => $version,
                'tag' => "v{$version}",
                'channel' => $channel,
            ];
        });
    }
}
