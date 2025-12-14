<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\LicenseKeyMail;
use App\Models\Order;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WebhookProcessingService
{
    public function __construct(private readonly LicenseService $licenseService) {}

    /**
     * Process a payment webhook and issue license if applicable.
     *
     * @param  array{external_id: string, email: string, amount_cents: int, currency: string, status: string, metadata?: array<string, mixed>}  $orderData
     */
    public function processPayment(WebhookEvent $event, array $orderData): void
    {
        if (Order::where('external_id', $orderData['external_id'])->exists()) {
            Log::info('Order already processed', ['external_id' => $orderData['external_id']]);

            return;
        }

        $order = Order::create([
            'provider' => $event->provider,
            'external_id' => $orderData['external_id'],
            'email' => $orderData['email'],
            'amount_cents' => $orderData['amount_cents'],
            'currency' => $orderData['currency'],
            'meta' => $orderData['metadata'] ?? [],
        ]);

        if ($orderData['status'] === 'paid' || $orderData['status'] === 'completed') {
            $license = $this->licenseService->issue([
                'order_id' => $order->id,
                'max_major_version' => 1,
            ]);

            Log::info('License issued from webhook', [
                'provider' => $event->provider,
                'order_id' => $order->id,
                'license_id' => $license->id,
            ]);

            Mail::to($orderData['email'])->queue(new LicenseKeyMail($license));

            Log::info('License key email queued', [
                'email' => $orderData['email'],
                'license_id' => $license->id,
            ]);
        }
    }

    /**
     * Map Stripe event types to internal event types.
     */
    public function mapStripeEventType(array $payload): string
    {
        $eventType = $payload['type'] ?? 'unknown';

        return match ($eventType) {
            'checkout.session.completed' => 'order.created',
            'payment_intent.succeeded' => 'order.created',
            default => 'unknown',
        };
    }
}
