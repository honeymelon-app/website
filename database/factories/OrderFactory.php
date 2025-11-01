<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Order>
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provider = fake()->randomElement(['ls', 'stripe']);

        return [
            'provider' => $provider,
            'external_id' => $provider === 'ls' ? fake()->numerify('ls-order-###########') : fake()->regexify('pi_[a-zA-Z0-9]{24}'),
            'email' => fake()->safeEmail(),
            'amount_cents' => fake()->numberBetween(1900, 9900),
            'currency' => 'usd',
            'meta' => [
                'product_name' => 'Honeymelon License',
                'variant_name' => fake()->randomElement(['Standard', 'Pro', 'Team']),
            ],
        ];
    }

    /**
     * Configure the factory for a Lemon Squeezy order.
     */
    public function lemonsqueezy(): self
    {
        return $this->state(fn () => [
            'provider' => 'ls',
            'external_id' => fake()->numerify('ls-order-###########'),
        ]);
    }

    /**
     * Configure the factory for a Stripe order.
     */
    public function stripe(): self
    {
        return $this->state(fn () => [
            'provider' => 'stripe',
            'external_id' => fake()->regexify('pi_[a-zA-Z0-9]{24}'),
        ]);
    }
}
