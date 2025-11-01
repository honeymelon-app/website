<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\WebhookEvent as WebhookEventEnum;
use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Order;
use App\Models\WebhookEvent;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookEventController extends Controller
{
    public function __construct(
        private readonly LicenseService $licenseService
    ) {}

    /**
     * Handle Lemon Squeezy webhook.
     */
    public function lemonsqueezy(Request $request): JsonResponse
    {
        Log::info('Received Lemon Squeezy webhook', ['payload' => $request->all()]);

        // TODO: Verify webhook signature

        $payload = $request->all();

        // Store webhook event
        $event = WebhookEvent::create([
            'provider' => 'ls',
            'type' => WebhookEventEnum::ORDER_CREATED, // TODO: Map from payload
            'payload' => $payload,
            'processed_at' => null,
        ]);

        try {
            // Process the webhook
            $this->processLemonSqueezyWebhook($event, $payload);

            $event->update(['processed_at' => now()]);

            return response()->json(['message' => 'Webhook processed']);
        } catch (\Exception $e) {
            Log::error('Failed to process Lemon Squeezy webhook', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle Stripe webhook.
     */
    public function stripe(Request $request): JsonResponse
    {
        Log::info('Received Stripe webhook', ['payload' => $request->all()]);

        // TODO: Verify webhook signature

        $payload = $request->all();

        // Store webhook event
        $event = WebhookEvent::create([
            'provider' => 'stripe',
            'type' => WebhookEventEnum::ORDER_CREATED, // TODO: Map from payload
            'payload' => $payload,
            'processed_at' => null,
        ]);

        try {
            // Process the webhook
            $this->processStripeWebhook($event, $payload);

            $event->update(['processed_at' => now()]);

            return response()->json(['message' => 'Webhook processed']);
        } catch (\Exception $e) {
            Log::error('Failed to process Stripe webhook', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Process a Lemon Squeezy webhook event.
     */
    private function processLemonSqueezyWebhook(WebhookEvent $event, array $payload): void
    {
        // Create order
        $order = Order::create([
            'provider' => 'ls',
            'external_id' => $payload['data']['id'] ?? uniqid('ls-'),
            'email' => $payload['data']['attributes']['user_email'] ?? 'unknown@example.com',
            'amount_cents' => $payload['data']['attributes']['total'] ?? 0,
            'currency' => $payload['data']['attributes']['currency'] ?? 'usd',
            'meta' => $payload,
        ]);

        // Issue license
        $license = $this->licenseService->issue([
            'order_id' => $order->id,
            'seats' => 1,
            'entitlements' => ['standard'],
        ]);

        Log::info('License issued from Lemon Squeezy webhook', [
            'order_id' => $order->id,
            'license_id' => $license->id,
        ]);

        // TODO: Send email with license key
    }

    /**
     * Process a Stripe webhook event.
     */
    private function processStripeWebhook(WebhookEvent $event, array $payload): void
    {
        // Create order
        $order = Order::create([
            'provider' => 'stripe',
            'external_id' => $payload['data']['object']['id'] ?? uniqid('pi_'),
            'email' => $payload['data']['object']['billing_details']['email'] ?? 'unknown@example.com',
            'amount_cents' => $payload['data']['object']['amount'] ?? 0,
            'currency' => $payload['data']['object']['currency'] ?? 'usd',
            'meta' => $payload,
        ]);

        // Issue license
        $license = $this->licenseService->issue([
            'order_id' => $order->id,
            'seats' => 1,
            'entitlements' => ['standard'],
        ]);

        Log::info('License issued from Stripe webhook', [
            'order_id' => $order->id,
            'license_id' => $license->id,
        ]);

        // TODO: Send email with license key
    }
}
