<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageVisit>
 */
class PageVisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $routes = ['home', 'download', 'pricing', 'privacy', 'terms'];
        $routeName = fake()->randomElement($routes);
        $paths = [
            'home' => '/',
            'download' => '/download',
            'pricing' => '/pricing',
            'privacy' => '/privacy',
            'terms' => '/terms',
        ];

        return [
            'path' => $paths[$routeName],
            'route_name' => $routeName,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referrer' => fake()->optional(0.6)->url(),
            'country' => fake()->optional(0.8)->countryCode(),
            'device_type' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            'browser' => fake()->randomElement(['Chrome', 'Safari', 'Firefox', 'Edge', null]),
            'platform' => fake()->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android', null]),
            'session_id' => Str::random(40),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Configure the visit as a home page visit.
     */
    public function homePage(): static
    {
        return $this->state(fn (array $attributes): array => [
            'path' => '/',
            'route_name' => 'home',
        ]);
    }

    /**
     * Configure the visit as a pricing page visit.
     */
    public function pricingPage(): static
    {
        return $this->state(fn (array $attributes): array => [
            'path' => '/pricing',
            'route_name' => 'pricing',
        ]);
    }

    /**
     * Configure the visit as a download page visit.
     */
    public function downloadPage(): static
    {
        return $this->state(fn (array $attributes): array => [
            'path' => '/download',
            'route_name' => 'download',
        ]);
    }

    /**
     * Configure the visit as a mobile visit.
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes): array => [
            'device_type' => 'mobile',
        ]);
    }

    /**
     * Configure the visit as a desktop visit.
     */
    public function desktop(): static
    {
        return $this->state(fn (array $attributes): array => [
            'device_type' => 'desktop',
        ]);
    }
}
