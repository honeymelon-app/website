<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider.
 *
 * Note: Contract-to-implementation bindings are now declared using #[Bind] attributes
 * directly on the interfaces in App\Contracts\. Singletons use #[Singleton] on classes.
 * Config injection uses #[Config] parameter attributes.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureModels();
        $this->configureUrls();
        $this->configureDevelopmentTools();
        $this->registerResponseMacros();
    }

    /**
     * Configure Eloquent model strictness for safer development.
     *
     * - preventLazyLoading: Catches N+1 query issues early
     * - preventSilentlyDiscardingAttributes: Fails if mass-assigning non-fillable attrs
     * - preventAccessingMissingAttributes: Fails on typos like $model->naem
     *
     * All three are disabled in production to avoid breaking deployed apps.
     */
    protected function configureModels(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
    }

    /**
     * Configure URL generation defaults.
     *
     * Forces HTTPS scheme in production to ensure generated URLs
     * (routes, assets, signed URLs) use secure protocol.
     */
    protected function configureUrls(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure development-only debugging tools.
     *
     * Query logging is opt-in via LOG_DB_QUERIES env var to avoid
     * noisy logs during normal development.
     */
    protected function configureDevelopmentTools(): void
    {
        if (! $this->app->isProduction() && config('app.debug') && env('LOG_DB_QUERIES', false)) {
            DB::listen(function ($query): void {
                Log::debug('DB Query', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time.'ms',
                ]);
            });
        }
    }

    /**
     * Register custom response macros.
     */
    protected function registerResponseMacros(): void
    {
        Response::macro('cdnJson', function (mixed $data, int $ttl = 300): JsonResponse {
            return response()->json($data)
                ->header('Cache-Control', "public, max-age={$ttl}, stale-while-revalidate=60");
        });
    }
}
