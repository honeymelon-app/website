<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * @var class-string<Client>
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'key' => 'hm_'.Str::random(32),
            'secret' => Str::random(64),
            'revoked_at' => null,
            'last_used_at' => null,
        ];
    }

    /**
     * Indicate the client is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn () => [
            'revoked_at' => now(),
        ]);
    }

    /**
     * Indicate the client was recently used.
     */
    public function recentlyUsed(): static
    {
        return $this->state(fn () => [
            'last_used_at' => now()->subMinutes(fake()->numberBetween(1, 30)),
        ]);
    }
}
