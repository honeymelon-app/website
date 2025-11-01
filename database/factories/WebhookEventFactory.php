<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WebhookEvent as WebhookEventEnum;
use App\Models\WebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebhookEvent>
 */
class WebhookEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<WebhookEvent>
     */
    protected $model = WebhookEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provider = fake()->randomElement(['ls', 'stripe']);
        $type = fake()->randomElement(WebhookEventEnum::cases());

        return [
            'provider' => $provider,
            'type' => $type,
            'payload' => [
                'event_id' => fake()->uuid(),
                'created_at' => now()->toIso8601String(),
                'data' => [
                    'order_id' => fake()->numerify('###########'),
                    'email' => fake()->safeEmail(),
                ],
            ],
            'processed_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Configure the factory for an unprocessed event.
     */
    public function unprocessed(): self
    {
        return $this->state(fn () => ['processed_at' => null]);
    }

    /**
     * Configure the factory for a processed event.
     */
    public function processed(): self
    {
        return $this->state(fn () => ['processed_at' => now()]);
    }
}
