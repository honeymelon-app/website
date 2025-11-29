<?php

declare(strict_types=1);

namespace App\Contracts;

interface PaymentProvider
{
    /**
     * Create a checkout session for a one-time purchase.
     *
     * @param  array{amount: int, currency: string, product_name: string, success_url: string, cancel_url: string, metadata: array<string, mixed>}  $data
     * @return array{checkout_url: string, session_id: string, provider: string}
     */
    public function createCheckoutSession(array $data): array;

    /**
     * Process a refund for a payment.
     *
     * @return array{refund_id: string, status: string, amount: int}
     */
    public function refund(string $paymentId, ?int $amount = null, ?string $reason = null): array;

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Parse webhook payload and extract order data.
     *
     * @return array{external_id: string, email: string, amount_cents: int, currency: string, status: string, metadata: array<string, mixed>}|null
     */
    public function parseWebhookPayload(array $payload): ?array;

    /**
     * Get provider identifier (stripe, ls).
     */
    public function getProviderIdentifier(): string;
}
