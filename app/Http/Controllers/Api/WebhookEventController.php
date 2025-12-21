<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Mail\LicenseKeyMail;
use App\Models\Order;
use App\Models\WebhookEvent;
use App\Services\LicenseService;
use App\Services\PaymentProviders\PaymentProviderFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WebhookEventController extends Controller
{
    public function __construct(
        private readonly LicenseService $licenseService,
        private readonly PaymentProviderFactory $providerFactory
    ) {}

    /**
     * Handle Stripe webhook.
     */
    public function stripe(Request $request): JsonResponse
    {
        Log::info('Received Stripe webhook', ['payload' => $request->all()]);

        $provider = $this->providerFactory->make('stripe');

        // Verify signature
        if (! $provider->verifyWebhookSignature($request->getContent(), $request->header('Stripe-Signature', ''))) {
            Log::warning('Invalid Stripe webhook signature');

            return ApiResponse::unauthorized('Invalid signature');
        }

        $payload = $request->all();

        // Store webhook event
        $event = WebhookEvent::create([
            'provider' => 'stripe',
            'type' => $this->mapStripeEventType($payload),
            'payload' => $payload,
            'processed_at' => null,
        ]);

        try {
            // Parse webhook data
            $orderData = $provider->parseWebhookPayload($payload);

            if (! $orderData) {
                Log::info('Non-payment webhook event, skipping', ['event_id' => $event->id]);
                $event->update(['processed_at' => now()]);

                return ApiResponse::success(message: 'Event acknowledged');
            }

            // Process the payment
            $this->processPayment($event, $orderData);

            $event->update(['processed_at' => now()]);

            return ApiResponse::success(message: 'Webhook processed');
        } catch (\Exception $e) {
            return $this->handleApiException(
                $e,
                'Failed to process Stripe webhook',
                ['event_id' => $event->id]
            );
        }
    }

    /**
     * Process payment and issue license.
     */
    protected function processPayment(WebhookEvent $event, array $orderData): void
    {
        // Check if order already exists
        if (Order::where('external_id', $orderData['external_id'])->exists()) {
            Log::info('Order already processed', ['external_id' => $orderData['external_id']]);

            return;
        }

        // Create order
        $order = Order::create([
            'provider' => $event->provider,
            'external_id' => $orderData['external_id'],
            'email' => $orderData['email'],
            'amount_cents' => $orderData['amount_cents'],
            'currency' => $orderData['currency'],
            'meta' => $orderData['metadata'] ?? [],
        ]);

        // Issue license only if payment successful
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

            // Send email with license key
            Mail::to($orderData['email'])->queue(new LicenseKeyMail($license));

            Log::info('License key email queued', [
                'email' => $orderData['email'],
                'license_id' => $license->id,
            ]);
        }
    }

    /**
     * Map Stripe event types to our enum.
     */
    protected function mapStripeEventType(array $payload): string
    {
        $eventType = $payload['type'] ?? 'unknown';

        return match ($eventType) {
            'checkout.session.completed' => 'order.created',
            'payment_intent.succeeded' => 'order.created',
            default => 'unknown',
        };
    }
}
