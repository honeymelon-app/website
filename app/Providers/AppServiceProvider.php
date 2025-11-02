<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\GithubService;
use App\Services\PaymentProviders\LemonSqueezyPaymentProvider;
use App\Services\PaymentProviders\PaymentProviderFactory;
use App\Services\PaymentProviders\StripePaymentProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register GithubService
        $this->app->singleton(GithubService::class, function () {
            return new GithubService(
                owner: config('services.github.owner'),
                repo: config('services.github.repo'),
                token: config('services.github.token')
            );
        });

        // Register StripePaymentProvider
        $this->app->singleton(StripePaymentProvider::class, function () {
            return new StripePaymentProvider(
                secretKey: config('services.stripe.secret'),
                webhookSecret: config('services.stripe.webhook_secret')
            );
        });

        // Register LemonSqueezyPaymentProvider
        $this->app->singleton(LemonSqueezyPaymentProvider::class, function () {
            return new LemonSqueezyPaymentProvider(
                storeId: config('services.lemonsqueezy.store_id'),
                apiKey: config('services.lemonsqueezy.api_key'),
                webhookSecret: config('services.lemonsqueezy.webhook_secret'),
                variantId: config('services.lemonsqueezy.variant_id')
            );
        });

        // Register PaymentProviderFactory
        $this->app->singleton(PaymentProviderFactory::class, function ($app) {
            return new PaymentProviderFactory(
                stripe: $app->make(StripePaymentProvider::class),
                lemonSqueezy: $app->make(LemonSqueezyPaymentProvider::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
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
