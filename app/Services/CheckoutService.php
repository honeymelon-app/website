<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Services\PaymentProviders\PaymentProviderFactory;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    public function __construct(
        private readonly PaymentProviderFactory $providerFactory
    ) {}

    /**
     * Create a checkout session for license purchase.
     *
     * @param  array{provider: string, product_slug?: string, success_url: string, cancel_url: string, email?: string, metadata?: array<string, mixed>}  $data
     * @return array{checkout_url: string, session_id: string, provider: string}
     */
    public function createCheckoutSession(array $data): array
    {
        Log::info('Creating checkout session', ['provider' => $data['provider']]);

        $provider = $this->providerFactory->make($data['provider']);

        $product = Product::where('slug', $data['product_slug'] ?? 'honeymelon')
            ->where('is_active', true)
            ->firstOrFail();

        $checkoutData = [
            'amount' => $product->price_cents,
            'currency' => $product->currency,
            'product_name' => $product->name.' License',
            'stripe_price_id' => $product->stripe_price_id,
            'success_url' => $data['success_url'],
            'cancel_url' => $data['cancel_url'],
            'metadata' => array_merge($data['metadata'] ?? [], [
                'product' => $product->slug,
                'product_id' => $product->id,
                'seats' => 1,
            ]),
        ];

        if (isset($data['email'])) {
            $checkoutData['email'] = $data['email'];
        }

        $session = $provider->createCheckoutSession($checkoutData);

        Log::info('Checkout session created', [
            'provider' => $session['provider'],
            'session_id' => $session['session_id'],
            'product' => $product->slug,
        ]);

        return $session;
    }
}
