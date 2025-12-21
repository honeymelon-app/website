<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCheckoutRequest;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {}

    /**
     * Create a checkout session for license purchase.
     *
     * Expected payload:
     * {
     *   "provider": "stripe",
     *   "amount": 2900,
     *   "currency": "usd",
     *   "success_url": "https://yoursite.com/success",
     *   "cancel_url": "https://yoursite.com/cancel",
     *   "email": "user@example.com"
     * }
     *
     * Returns:
     * {
     *   "checkout_url": "https://checkout.stripe.com/...",
     *   "session_id": "cs_...",
     *   "provider": "stripe"
     * }
     */
    public function __invoke(CreateCheckoutRequest $request): JsonResponse
    {
        try {
            $session = $this->checkoutService->createCheckoutSession([
                'provider' => $request->input('provider'),
                'amount' => $request->integer('amount'),
                'currency' => $request->input('currency', 'usd'),
                'success_url' => $request->input('success_url'),
                'cancel_url' => $request->input('cancel_url'),
                'email' => $request->input('email'),
                'metadata' => [
                    'user_agent' => $request->userAgent(),
                    'ip' => $request->ip(),
                ],
            ]);

            return response()->json($session, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create checkout session', [
                'provider' => $request->input('provider'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to create checkout session',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
