<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * @var class-string<Product>
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'stripe_product_id' => 'prod_'.fake()->unique()->regexify('[A-Za-z0-9]{14}'),
            'stripe_price_id' => 'price_'.fake()->unique()->regexify('[A-Za-z0-9]{14}'),
            'price_cents' => fake()->numberBetween(500, 10000),
            'currency' => 'usd',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
