<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Contracts\PaymentProvider;
use App\Mail\LicenseKeyMail;
use App\Services\PaymentProviders\PaymentProviderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class WebhookEventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_webhook_only_queues_one_license_email(): void
    {
        Mail::fake();

        $orderData = [
            'external_id' => 'cs_test_123',
            'email' => 'customer@example.com',
            'amount_cents' => 2500,
            'currency' => 'usd',
            'status' => 'paid',
            'metadata' => ['product' => 'Honeymelon'],
        ];

        $provider = new class($orderData) implements PaymentProvider
        {
            public function __construct(private array $orderData) {}

            public function createCheckoutSession(array $data): array
            {
                return [];
            }

            public function refund(string $paymentId, ?int $amount = null, ?string $reason = null): array
            {
                return [];
            }

            public function verifyWebhookSignature(string $payload, string $signature): bool
            {
                return true;
            }

            public function parseWebhookPayload(array $payload): ?array
            {
                return $this->orderData;
            }

            public function getProviderIdentifier(): string
            {
                return 'stripe';
            }
        };

        $factory = Mockery::mock(PaymentProviderFactory::class);
        $factory->shouldReceive('make')
            ->once()
            ->with('stripe')
            ->andReturn($provider);

        $this->app->instance(PaymentProviderFactory::class, $factory);

        $payload = [
            'type' => 'checkout.session.completed',
            'data' => ['object' => []],
        ];

        $response = $this->postJson(route('webhooks.stripe'), $payload, ['Stripe-Signature' => 'test']);

        $response->assertOk();

        Mail::assertQueued(LicenseKeyMail::class, 1);
    }
}
