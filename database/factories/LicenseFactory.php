<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<License>
     */
    protected $model = License::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a license key like XXXX-XXXX-XXXX-XXXX
        $key = strtoupper(implode('-', [
            Str::random(4),
            Str::random(4),
            Str::random(4),
            Str::random(4),
        ]));

        return [
            'key' => $key,
            'status' => LicenseStatus::ACTIVE,
            'seats' => fake()->randomElement([1, 2, 5]),
            'entitlements' => fake()->randomElement([
                ['pro'],
                ['pro', 'hevc'],
                ['standard'],
            ]),
            'updates_until' => fake()->dateTimeBetween('now', '+2 years'),
            'meta' => [
                'issued_at' => now()->toIso8601String(),
            ],
            'order_id' => Order::factory(),
        ];
    }

    /**
     * Configure the factory for a revoked license.
     */
    public function revoked(): self
    {
        return $this->state(fn () => ['status' => LicenseStatus::REVOKED]);
    }

    /**
     * Configure the factory for an expired license.
     */
    public function expired(): self
    {
        return $this->state(fn () => [
            'status' => LicenseStatus::EXPIRED,
            'updates_until' => fake()->dateTimeBetween('-2 years', '-1 day'),
        ]);
    }
}
