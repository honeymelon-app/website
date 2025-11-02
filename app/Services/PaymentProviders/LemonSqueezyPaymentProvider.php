<?php

declare(strict_types=1);

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class LemonSqueezyPaymentProvider implements PaymentProvider
{
    public function __construct(
        private readonly string $storeId,
        private readonly string $apiKey,
        private readonly string $webhookSecret,
        private readonly ?string $variantId = null
    ) {}

    /**
     * Create a checkout session for a one-time purchase.
     *
     * @param  array{amount: int, currency: string, product_name: string, success_url: string, cancel_url: string, metadata: array<string, mixed>}  $data
     * @return array{checkout_url: string, session_id: string, provider: string}
     */
    public function createCheckoutSession(array $data): array
    {
        try {
            $response = $this->client()
                ->post('/checkouts', [
                    'data' => [
                        'type' => 'checkouts',
                        'attributes' => [
                            'checkout_data' => [
                                'custom' => $data['metadata'],
                                'email' => $data['metadata']['email'] ?? null,
                            ],
                            'product_options' => [
                                'name' => $data['product_name'],
                                'description' => $data['metadata']['description'] ?? null,
                                'redirect_url' => $data['success_url'],
                            ],
                            'checkout_options' => [
                                'button_color' => '#000000',
                            ],
                        ],
                        'relationships' => [
                            'store' => [
                                'data' => [
                                    'type' => 'stores',
                                    'id' => $this->storeId,
                                ],
                            ],
                            'variant' => [
                                'data' => [
                                    'type' => 'variants',
                                    'id' => $this->variantId,
                                ],
                            ],
                        ],
                    ],
                ])
                ->throw();

            $checkoutData = $response->json('data');

            return [
                'checkout_url' => $checkoutData['attributes']['url'] ?? '',
                'session_id' => $checkoutData['id'] ?? '',
                'provider' => $this->getProviderIdentifier(),
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to create Lemon Squeezy checkout session: {$e->getMessage()}",
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Verify webhook signature using HMAC SHA256.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $hash = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($hash, $signature);
    }

    /**
     * Parse webhook payload and extract order data.
     *
     * @return array{external_id: string, email: string, amount_cents: int, currency: string, status: string, metadata: array<string, mixed>}|null
     */
    public function parseWebhookPayload(array $payload): ?array
    {
        // Check if this is an order_created event
        $eventType = $payload['meta']['event_name'] ?? null;

        if ($eventType !== 'order_created') {
            return null;
        }

        $order = $payload['data']['attributes'] ?? null;

        if (! $order) {
            return null;
        }

        return [
            'external_id' => $payload['data']['id'] ?? '',
            'email' => $order['user_email'] ?? '',
            'amount_cents' => (int) ($order['total'] ?? 0),
            'currency' => $order['currency'] ?? '',
            'status' => $order['status'] ?? 'pending',
            'metadata' => [
                'order_number' => $order['order_number'] ?? null,
                'first_order_item' => $order['first_order_item'] ?? null,
                'custom_data' => $order['custom_data'] ?? [],
            ],
        ];
    }

    /**
     * Get provider identifier.
     */
    public function getProviderIdentifier(): string
    {
        return 'ls';
    }

    /**
     * Create the HTTP client with authentication.
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl('https://api.lemonsqueezy.com/v1')
            ->withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ])
            ->withToken($this->apiKey);
    }
}
