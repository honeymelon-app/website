<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\HandlesControllerExceptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCheckoutRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    use HandlesControllerExceptions;

    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {}

    /**
     * Create a checkout session for license purchase.
     */
    public function __invoke(CreateCheckoutRequest $request): JsonResponse
    {
        try {
            $product = Product::findOrFail($request->validated('product_id'));

            $session = $this->checkoutService->createCheckoutSession([
                'provider' => $request->validated('provider'),
                'product' => $product,
                'success_url' => $request->validated('success_url'),
                'cancel_url' => $request->validated('cancel_url'),
                'email' => $request->input('email'),
                'metadata' => [
                    'product_id' => $product->id,
                    'user_agent' => $request->userAgent(),
                    'ip' => $request->ip(),
                ],
            ]);

            return ApiResponse::created($session, null, dataKey: null);
        } catch (\Exception $e) {
            return $this->handleApiException(
                $e,
                'Failed to create checkout session',
                [
                    'provider' => $request->validated('provider'),
                    'product_id' => $request->validated('product_id'),
                ]
            );
        }
    }
}
