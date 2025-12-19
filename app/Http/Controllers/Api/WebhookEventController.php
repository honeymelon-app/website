<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\PaymentProviderResolver;
use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use App\Services\WebhookProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class WebhookEventController extends Controller
{
    public function __construct(
        private readonly WebhookProcessingService $webhookService,
        private readonly PaymentProviderResolver $providerFactory
    ) {}

    /**
     * Handle Stripe webhook.
     */
    public function stripe(Request $request): JsonResponse
    {
        Log::info('Received Stripe webhook', ['payload' => $request->all()]);

        $provider = $this->providerFactory->make('stripe');

        if (! $provider->verifyWebhookSignature($request->getContent(), $request->header('Stripe-Signature', ''))) {
            Log::warning('Invalid Stripe webhook signature');

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->all();

        $event = WebhookEvent::create([
            'provider' => 'stripe',
            'type' => $this->webhookService->mapStripeEventType($payload),
            'payload' => $payload,
            'processed_at' => null,
        ]);

        try {
            $orderData = $provider->parseWebhookPayload($payload);

            if (! $orderData) {
                Log::info('Non-payment webhook event, skipping', ['event_id' => $event->id]);
                $event->update(['processed_at' => now()]);

                return response()->json(['message' => 'Event acknowledged']);
            }

            $this->webhookService->processPayment($event, $orderData);
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
}
