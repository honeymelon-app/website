<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Contracts\PaymentProvider;
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
        return array_merge([
            'provider' => 'stripe',
            'amount' => 2900,
            'currency' => 'usd',
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

    public function test_creates_checkout_session_with_lemon_squeezy(): void
    {
        $expectedResponse = [
            'checkout_url' => 'https://checkout.lemonsqueezy.com/test-session',
            'session_id' => 'ls_test_123',
            'provider' => 'ls',
        ];

        $mockProvider = $this->getMockPaymentProvider($expectedResponse);

        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $mockFactory->method('make')
            ->with('ls')
            ->willReturn($mockProvider);

        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $response = $this->postJson('/api/checkout', $this->getValidCheckoutPayload([
            'provider' => 'ls',
        ]));

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

    public function test_creates_checkout_session_without_optional_currency(): void
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
        unset($payload['currency']);

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
                'provider' => ['The provider must be either stripe or ls (Lemon Squeezy)'],
            ]);
    }

    public function test_requires_amount(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload();
        unset($payload['amount']);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_validates_amount_is_integer(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload([
            'amount' => 'not-an-integer',
        ]);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_validates_minimum_amount(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload([
            'amount' => 99,
        ]);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount'])
            ->assertJsonFragment([
                'amount' => ['The amount must be at least $1.00 (100 cents)'],
            ]);
    }

    public function test_validates_currency_format(): void
    {
        $mockFactory = $this->createMock(PaymentProviderFactory::class);
        $this->app->instance(PaymentProviderFactory::class, $mockFactory);

        $payload = $this->getValidCheckoutPayload([
            'currency' => 'toolong',
        ]);

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
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
}
