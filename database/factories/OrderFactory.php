<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
        ];
    }

    /**
     * Configure the factory for a Lemon Squeezy order.
     */
    public function lemonsqueezy(): static
    {
        return $this->state(fn () => [
            'provider' => 'ls',
            'external_id' => fake()->numerify('ls-order-###########'),
        ]);
    }

    /**
     * Configure the factory for a Stripe order.
     */
    public function stripe(): static
    {
        return $this->state(fn () => [
            'provider' => 'stripe',
            'external_id' => fake()->regexify('pi_[a-zA-Z0-9]{24}'),
        ]);
    }

    /**
     * Configure the factory for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Configure the factory for a specific product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn () => [
            'product_id' => $product->id,
            'amount_cents' => $product->price_cents,
        ]);
    }

    /**
     * Configure the factory for a refunded order.
     */
    public function refunded(): static
    {
        return $this->state(fn () => [
            'refund_id' => fake()->regexify('re_[a-zA-Z0-9]{24}'),
            'refunded_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Configure the factory for a paid (completed) order.
     */
    public function paid(): static
    {
        return $this->state(fn () => [
            'refund_id' => null,
            'refunded_at' => null,
        ]);
    }
}
