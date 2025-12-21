<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Note: Most service bindings are now handled via PHP attributes:
     * - #[Singleton] for singleton bindings
     * - #[Config] for configuration value injection
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Note: Model observers are registered via #[ObservedBy] attributes on models.
     */
    public function boot(): void
    {
        // Add response macro for CDN caching
        Response::macro('cdnJson', function (mixed $data, int $ttl = 300): JsonResponse {
            return response()->json($data)
                ->header('Cache-Control', "public, max-age={$ttl}, stale-while-revalidate=60");
        });
    }
}
