<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
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
        $this->configureModelBehavior();
        $this->configureHttpClient();
        $this->configureUrlGeneration();
        $this->configureRateLimiters();
        $this->configureResponseMacros();
        $this->configureQueryLogging();
    }

    /**
     * Configure model behavior and database settings.
     */
    protected function configureModelBehavior(): void
    {
        // Prevent lazy loading in non-local environments to catch N+1 queries
        Model::preventLazyLoading(! app()->isLocal());

        // Prevent accessing missing attributes
        Model::preventAccessingMissingAttributes(! app()->isLocal());

        // Prevent silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(! app()->isLocal());
    }

    /**
     * Configure global HTTP client settings using Fetch PHP.
     */
    protected function configureHttpClient(): void
    {
        // Register GitHub client as a singleton
        $this->app->singleton('http.github', function () {
            return fetch_client([
                'base_uri' => 'https://api.github.com',
                'headers' => [
                    'Authorization' => 'Bearer '.config('github.token'),
                    'Accept' => 'application/vnd.github.v3+json',
                ],
                'timeout' => 30,
                'retries' => 3,
                'retry_delay' => 100,
            ]);
        });

        // Register Stripe client as a singleton
        $this->app->singleton('http.stripe', function () {
            return fetch_client([
                'base_uri' => 'https://api.stripe.com',
                'headers' => [
                    'Authorization' => 'Bearer '.config('services.stripe.secret'),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'timeout' => 30,
                'retries' => 3,
                'retry_delay' => 100,
            ]);
        });
    }

    /**
     * Configure URL generation.
     */
    protected function configureUrlGeneration(): void
    {
        // Force HTTPS in production
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure response macros.
     */
    protected function configureResponseMacros(): void
    {
        // Add response macro for CDN caching
        Response::macro('cdnJson', function (mixed $data, int $ttl = 300): JsonResponse {
            return response()->json($data)
                ->header('Cache-Control', "public, max-age={$ttl}, stale-while-revalidate=60");
        });
    }

    /**
     * Configure query logging for debugging.
     */
    protected function configureQueryLogging(): void
    {
        // Enable query logging in local environment
        if (app()->isLocal() && config('app.debug')) {
            DB::listen(function ($query) {
                logger()->debug('Query executed', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            });
        }
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
