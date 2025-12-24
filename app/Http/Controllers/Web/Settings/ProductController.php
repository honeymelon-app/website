<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\StripeSyncService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly StripeSyncService $stripeSyncService
    ) {}

    /**
     * Show the product settings page.
     */
    public function edit(): Response
    {
        $product = Product::query()->first();

        return Inertia::render('settings/Product', [
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'stripe_product_id' => $product->stripe_product_id,
                'stripe_price_id' => $product->stripe_price_id,
                'price_cents' => $product->price_cents,
                'currency' => $product->currency,
                'is_active' => $product->is_active,
            ] : null,
        ]);
    }

    /**
     * Update the product settings.
     */
    public function update(UpdateProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $product = Product::query()->first();

        if ($product) {
            $product->update($data);
        } else {
            $product = Product::create(array_merge($data, [
                'slug' => 'honeymelon',
            ]));
        }

        if ($product->stripe_product_id) {
            try {
                $this->stripeSyncService->pushToStripe($product);
            } catch (\Throwable $e) {
                return back()->with('error', 'Product saved locally but failed to sync to Stripe: '.$e->getMessage());
            }
        }

        return back()->with('success', 'Product settings updated successfully.');
    }

    /**
     * Sync product from Stripe.
     */
    public function sync(): RedirectResponse
    {
        $product = Product::query()->first();

        if (! $product?->stripe_product_id) {
            return back()->with('error', 'No Stripe product ID configured.');
        }

        try {
            $synced = $this->stripeSyncService->syncProduct($product);

            if ($synced) {
                return back()->with('success', 'Product synced from Stripe successfully.');
            }

            return back()->with('info', 'Product is already up to date with Stripe.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to sync from Stripe: '.$e->getMessage());
        }
    }

    /**
     * Preview what would be synced from Stripe.
     */
    public function preview(): RedirectResponse
    {
        $product = Product::query()->first();

        if (! $product?->stripe_product_id) {
            return back()->with('error', 'No Stripe product ID configured.');
        }

        try {
            $stripeData = $this->stripeSyncService->getProductDetails($product->stripe_product_id);

            if (! $stripeData) {
                return back()->with('error', 'Could not fetch product from Stripe.');
            }

            return back()->with('stripe_preview', $stripeData);
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to fetch from Stripe: '.$e->getMessage());
        }
    }
}
