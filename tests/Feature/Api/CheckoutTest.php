<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Contracts\PaymentProvider;
use App\Models\Product;
use App\Services\PaymentProviders\PaymentProviderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function getMockPaymentProvider(array $checkoutResponse): PaymentProvider
    {
        $mock = $this->createMock(PaymentProvider::class);
        $mock->method('createCheckoutSession')
            ->willReturn($checkoutResponse);

        return $mock;
    }

    protected function getValidCheckoutPayload(array $overrides = []): array
    {
        $product = Product::factory()->create();

        return array_merge([
            'provider' => 'stripe',
            'product_id' => $product->id,
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'email' => 'test@example.com',
        ], $overrides);
    }

    public function test_creates_checkout_session_successfully(): void
    {
        $expectedResponse = [
            'checkout_url' => 'https://checkout.stripe.com/test-session',
            'session_id' => 'cs_test_123',
            'provider' => 'stripe',
        ];

        $mockProvider = $this->getMockPaymentProvider($expectedResponse);

        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $mockFactory->method('make')
            ->with('stripe')
            ->willReturn($mockProvider);

        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $response = $this->postJson('/api/checkout', $this->getValidCheckoutPayload());

        $response->assertStatus(201)
            ->assertJson($expectedResponse);
    }

    public function test_creates_checkout_session_without_optional_email(): void
    {
        $expectedResponse = [
            'checkout_url' => 'https://checkout.stripe.com/test-session',
            'session_id' => 'cs_test_123',
            'provider' => 'stripe',
        ];

        $mockProvider = $this->getMockPaymentProvider($expectedResponse);

        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $mockFactory->method('make')
            ->willReturn($mockProvider);

        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload();
        unset($payload['email']);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(201)
            ->assertJson($expectedResponse);
    }

    public function test_requires_provider(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload();
        unset($payload['provider']);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['provider']);
    }

    public function test_validates_provider_options(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload([
            'provider' => 'invalid',
        ]);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['provider'])
            ->assertJsonFragment([
                'provider' => ['The provider must be stripe'],
            ]);
    }

    public function test_requires_product_id(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload();
        unset($payload['product_id']);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_validates_product_id_is_uuid(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload([
            'product_id' => 'not-a-uuid',
        ]);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_validates_product_exists(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload([
            'product_id' => '00000000-0000-0000-0000-000000000000',
        ]);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id'])
            ->assertJsonFragment([
                'product_id' => ['The selected product does not exist'],
            ]);
    }

    public function test_requires_success_url(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload();
        unset($payload['success_url']);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['success_url']);
    }

    public function test_validates_success_url_format(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload([
            'success_url' => 'not-a-url',
        ]);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['success_url']);
    }

    public function test_requires_cancel_url(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload();
        unset($payload['cancel_url']);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cancel_url']);
    }

    public function test_validates_cancel_url_format(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload([
            'cancel_url' => 'not-a-url',
        ]);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cancel_url']);
    }

    public function test_validates_email_format(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload([
            'email' => 'not-an-email',
        ]);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_handles_checkout_service_exception(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $mockFactory->method('make')
            ->willThrowException(new \Exception('Provider not found'));

        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $response = $this->postJson('/api/checkout', $this->getValidCheckoutPayload());

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Failed to create checkout session',
                'error' => 'Provider not found',
            ]);
    }

    public function test_uses_product_price_for_checkout(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price_cents' => 4999,
            'currency' => 'eur',
            'stripe_price_id' => 'price_test123',
        ]);

        $expectedResponse = [
            'checkout_url' => 'https://checkout.stripe.com/test-session',
            'session_id' => 'cs_test_123',
            'provider' => 'stripe',
        ];

        $mockProvider = $this->createMock(PaymentProvider::class);
        $mockProvider->expects($this->once())
            ->method('createCheckoutSession')
            ->with($this->callback(function (array $data) {
                return $data['amount'] === 4999
                    && $data['currency'] === 'eur'
                    && $data['product_name'] === 'Test Product License'
                    && $data['stripe_price_id'] === 'price_test123';
            }))
            ->willReturn($expectedResponse);

        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $mockFactory->method('make')
            ->willReturn($mockProvider);

        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $response = $this->postJson('/api/checkout', [
            'provider' => 'stripe',
            'product_id' => $product->id,
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ]);

        $response->assertStatus(201);
    }
}
