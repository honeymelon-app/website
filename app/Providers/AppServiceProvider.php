<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimiters();

        // Add response macro for CDN caching
        Response::macro('cdnJson', function (mixed $data, int $ttl = 300): JsonResponse {
            return response()->json($data)
                ->header('Cache-Control', "public, max-age={$ttl}, stale-while-revalidate=60");
        });
    }

    /**
     * Configure rate limiters for the application.
     */
    protected function configureRateLimiters(): void
    {
        // 10 downloads per minute per IP/license combo
        RateLimiter::for('downloads', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip().'|'.$request->query('license'))
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many download attempts. Please try again later.',
                    ], 429, $headers);
                });
        });

        // 60 requests per minute for general API endpoints
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
