<?php

declare(strict_types=1);

namespace App\Services;

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
     * @param  array{provider: string, amount: int, currency: string, success_url: string, cancel_url: string, email?: string, metadata?: array<string, mixed>}  $data
     * @return array{checkout_url: string, session_id: string, provider: string}
     */
    public function createCheckoutSession(array $data): array
    {
        Log::info('Creating checkout session', ['provider' => $data['provider']]);

        $provider = $this->providerFactory->make($data['provider']);

        $checkoutData = [
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'usd',
            'product_name' => 'Honeymelon License',
            'success_url' => $data['success_url'],
            'cancel_url' => $data['cancel_url'],
            'metadata' => array_merge($data['metadata'] ?? [], [
                'product' => 'honeymelon_license',
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
        ]);

        return $session;
    }
}
