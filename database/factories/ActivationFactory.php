<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Activation;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activation>
 */
class ActivationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Activation>
     */
    protected $model = Activation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'device_id_hash' => hash('sha256', fake()->uuid()),
            'app_version' => fake()->semver(),
            'os_version' => fake()->randomElement([
                'macOS 14.1.0',
                'macOS 13.5.2',
                'macOS 15.0.0',
            ]),
            'last_seen_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Assign the activation to a specific license.
     */
    public function forLicense(License $license): self
    {
        return $this->state(fn () => ['license_id' => $license->id]);
    }
}
