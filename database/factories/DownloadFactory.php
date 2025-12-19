<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Artifact;
use App\Models\Download;
use App\Models\License;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Download>
 */
class DownloadFactory extends Factory
{
    /**
     * @var class-string<Download>
     */
    protected $model = Download::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'artifact_id' => Artifact::factory(),
            'license_id' => License::factory(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'downloaded_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate the download was made today.
     */
    public function today(): static
    {
        return $this->state(fn () => [
            'downloaded_at' => now(),
        ]);
    }

    /**
     * Indicate the download has no license (guest download).
     */
    public function guest(): static
    {
        return $this->state(fn () => [
            'user_id' => null,
            'license_id' => null,
        ]);
    }
}
