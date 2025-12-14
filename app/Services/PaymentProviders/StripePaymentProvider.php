<?php

declare(strict_types=1);

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentProvider;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentProvider implements PaymentProvider
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $webhookSecret
    ) {
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Create a checkout session for a one-time purchase.
     *
     * @param  array{amount: int, currency: string, product_name: string, stripe_price_id?: string, success_url: string, cancel_url: string, metadata: array<string, mixed>}  $data
     * @return array{checkout_url: string, session_id: string, provider: string}
     */
    public function createCheckoutSession(array $data): array
    {
        try {
            $sessionData = [
                'mode' => 'payment',
                'success_url' => $data['success_url'],
                'cancel_url' => $data['cancel_url'],
                'metadata' => $data['metadata'],
            ];

            if (! empty($data['stripe_price_id'])) {
                $sessionData['line_items'] = [[
                    'price' => $data['stripe_price_id'],
                    'quantity' => 1,
                ]];
            } else {
                $sessionData['line_items'] = [[
                    'price_data' => [
                        'currency' => $data['currency'],
                        'product_data' => [
                            'name' => $data['product_name'],
                        ],
                        'unit_amount' => $data['amount'],
                    ],
                    'quantity' => 1,
                ]];
            }

            $session = Session::create($sessionData);

            return [
                'checkout_url' => $session->url,
                'session_id' => $session->id,
                'provider' => $this->getProviderIdentifier(),
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to create Stripe checkout session: {$e->getMessage()}",
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process a refund for a payment.
     *
     * @return array{refund_id: string, status: string, amount: int}
     */
    public function refund(string $paymentId, ?int $amount = null, ?string $reason = null): array
    {
        try {
            // If the paymentId is a checkout session ID, get the payment intent
            $paymentIntentId = $paymentId;
            if (str_starts_with($paymentId, 'cs_')) {
                $session = Session::retrieve($paymentId);
                $paymentIntentId = $session->payment_intent;
            }

            $refundData = [
                'payment_intent' => $paymentIntentId,
            ];

            if ($amount !== null) {
                $refundData['amount'] = $amount;
            }

            if ($reason !== null) {
                // Stripe only accepts: duplicate, fraudulent, requested_by_customer
                $refundData['reason'] = 'requested_by_customer';
                $refundData['metadata'] = ['admin_reason' => $reason];
            }

            $refund = Refund::create($refundData);

            return [
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount,
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to process Stripe refund: {$e->getMessage()}",
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            Webhook::constructEvent($payload, $signature, $this->webhookSecret);

            return true;
        } catch (SignatureVerificationException $e) {
            return false;
        }
    }

    /**
     * Parse webhook payload and extract order data.
     *
     * @return array{external_id: string, email: string, amount_cents: int, currency: string, status: string, metadata: array<string, mixed>}|null
     */
    public function parseWebhookPayload(array $payload): ?array
    {
        // Check if this is a checkout.session.completed event
        if (! isset($payload['type']) || $payload['type'] !== 'checkout.session.completed') {
            return null;
        }

        $session = $payload['data']['object'] ?? null;

        if (! $session) {
            return null;
        }

        return [
            'external_id' => $session['id'] ?? '',
            'email' => $session['customer_details']['email'] ?? '',
            'amount_cents' => $session['amount_total'] ?? 0,
            'currency' => $session['currency'] ?? '',
            'status' => $session['payment_status'] ?? 'unpaid',
            'metadata' => $session['metadata'] ?? [],
        ];
    }

    /**
     * Get provider identifier.
     */
    public function getProviderIdentifier(): string
    {
        return 'stripe';
    }
}
