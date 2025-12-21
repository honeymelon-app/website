<?php

declare(strict_types=1);

namespace Tests\Feature\AttributeRefactor;

use App\Services\GithubService;
use App\Services\PaymentProviders\PaymentProviderFactory;
use App\Services\PaymentProviders\StripePaymentProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test that #[Singleton] and #[Config] attributes work correctly.
 */
class ContainerBindingAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_github_service_resolves_as_singleton(): void
    {
        $instance1 = app(GithubService::class);
        $instance2 = app(GithubService::class);

        // Should return the same instance (singleton)
        $this->assertSame($instance1, $instance2);
    }

    public function test_github_service_receives_config_values(): void
    {
        config([
            'services.github.owner' => 'test-owner',
            'services.github.repo' => 'test-repo',
            'services.github.token' => 'test-token',
        ]);

        // Clear any existing instance
        app()->forgetInstance(GithubService::class);

        $service = app(GithubService::class);

        // Use reflection to check private properties
        $reflection = new \ReflectionClass($service);
        $owner = $reflection->getProperty('owner');
        $repo = $reflection->getProperty('repo');
        $token = $reflection->getProperty('token');

        $this->assertSame('test-owner', $owner->getValue($service));
        $this->assertSame('test-repo', $repo->getValue($service));
        $this->assertSame('test-token', $token->getValue($service));
    }

    public function test_stripe_payment_provider_resolves_as_singleton(): void
    {
        $instance1 = app(StripePaymentProvider::class);
        $instance2 = app(StripePaymentProvider::class);

        // Should return the same instance (singleton)
        $this->assertSame($instance1, $instance2);
    }

    public function test_payment_provider_factory_resolves_as_singleton(): void
    {
        $instance1 = app(PaymentProviderFactory::class);
        $instance2 = app(PaymentProviderFactory::class);

        // Should return the same instance (singleton)
        $this->assertSame($instance1, $instance2);
    }

    public function test_payment_provider_factory_receives_stripe_dependency(): void
    {
        $factory = app(PaymentProviderFactory::class);
        $stripe = $factory->make('stripe');

        $this->assertInstanceOf(StripePaymentProvider::class, $stripe);
    }

    public function test_all_services_can_be_resolved_from_container(): void
    {
        // Ensure all our attribute-based bindings work
        $this->assertInstanceOf(GithubService::class, app(GithubService::class));
        $this->assertInstanceOf(StripePaymentProvider::class, app(StripePaymentProvider::class));
        $this->assertInstanceOf(PaymentProviderFactory::class, app(PaymentProviderFactory::class));
    }
}
