<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Stripe\Price;
use Stripe\Product as StripeProduct;
use Stripe\Stripe;

final class StripeSyncService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Sync all products from Stripe.
     *
     * @return array{synced: int, skipped: int, errors: int}
     */
    public function syncProducts(): array
    {
        $stats = ['synced' => 0, 'skipped' => 0, 'errors' => 0];

        Log::info('Starting Stripe product sync');

        $products = Product::whereNotNull('stripe_product_id')->get();

        foreach ($products as $product) {
            try {
                $result = $this->syncProduct($product);
                $stats[$result ? 'synced' : 'skipped']++;
            } catch (\Throwable $e) {
                Log::error('Failed to sync product from Stripe', [
                    'product_id' => $product->id,
                    'stripe_product_id' => $product->stripe_product_id,
                    'error' => $e->getMessage(),
                ]);
                $stats['errors']++;
            }
        }

        Log::info('Stripe product sync completed', $stats);

        return $stats;
    }

    /**
     * Sync a single product from Stripe.
     */
    public function syncProduct(Product $product): bool
    {
        if (! $product->stripe_product_id) {
            Log::debug('Skipping product without Stripe ID', ['product_id' => $product->id]);

            return false;
        }

        $stripeProduct = $this->fetchStripeProduct($product->stripe_product_id);

        if (! $stripeProduct) {
            return false;
        }

        $updates = [];

        if ($stripeProduct->name && $stripeProduct->name !== $product->name) {
            $updates['name'] = $stripeProduct->name;
        }

        if ($stripeProduct->description && $stripeProduct->description !== $product->description) {
            $updates['description'] = $stripeProduct->description;
        }

        $defaultPriceId = $stripeProduct->default_price;
        if ($defaultPriceId && $defaultPriceId !== $product->stripe_price_id) {
            $updates['stripe_price_id'] = $defaultPriceId;
        }

        if ($defaultPriceId) {
            $priceData = $this->fetchStripePrice($defaultPriceId);
            if ($priceData) {
                if ($priceData['amount'] !== $product->price_cents) {
                    $updates['price_cents'] = $priceData['amount'];
                }
                if ($priceData['currency'] !== $product->currency) {
                    $updates['currency'] = $priceData['currency'];
                }
            }
        }

        if (empty($updates)) {
            Log::debug('Product already in sync', ['product_id' => $product->id]);

            return false;
        }

        $product->update($updates);

        Log::info('Product synced from Stripe', [
            'product_id' => $product->id,
            'updates' => array_keys($updates),
        ]);

        return true;
    }

    /**
     * Fetch a product from Stripe.
     */
    protected function fetchStripeProduct(string $stripeProductId): ?StripeProduct
    {
        try {
            return StripeProduct::retrieve($stripeProductId);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch Stripe product', [
                'stripe_product_id' => $stripeProductId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fetch a price from Stripe.
     *
     * @return array{id: string, amount: int, currency: string}|null
     */
    protected function fetchStripePrice(string $stripePriceId): ?array
    {
        try {
            $price = Price::retrieve($stripePriceId);

            return [
                'id' => $price->id,
                'amount' => $price->unit_amount,
                'currency' => $price->currency,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to fetch Stripe price', [
                'stripe_price_id' => $stripePriceId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get current product details from Stripe (without saving to database).
     *
     * @return array{name: string, description: ?string, price_cents: int, currency: string, stripe_product_id: string, stripe_price_id: string}|null
     */
    public function getProductDetails(string $stripeProductId): ?array
    {
        $stripeProduct = $this->fetchStripeProduct($stripeProductId);

        if (! $stripeProduct) {
            return null;
        }

        $result = [
            'name' => $stripeProduct->name,
            'description' => $stripeProduct->description,
            'stripe_product_id' => $stripeProduct->id,
            'stripe_price_id' => null,
            'price_cents' => 0,
            'currency' => 'usd',
        ];

        if ($stripeProduct->default_price) {
            $priceData = $this->fetchStripePrice($stripeProduct->default_price);
            if ($priceData) {
                $result['stripe_price_id'] = $priceData['id'];
                $result['price_cents'] = $priceData['amount'];
                $result['currency'] = $priceData['currency'];
            }
        }

        return $result;
    }

    /**
     * Push local product changes to Stripe.
     *
     * @return array{product_updated: bool, price_created: bool, new_price_id: ?string}
     */
    public function pushToStripe(Product $product): array
    {
        $result = [
            'product_updated' => false,
            'price_created' => false,
            'new_price_id' => null,
        ];

        if (! $product->stripe_product_id) {
            Log::debug('Cannot push to Stripe: no Stripe product ID', ['product_id' => $product->id]);

            return $result;
        }

        $this->updateStripeProduct($product);
        $result['product_updated'] = true;

        $priceResult = $this->syncPriceToStripe($product);
        if ($priceResult) {
            $result['price_created'] = true;
            $result['new_price_id'] = $priceResult;
        }

        return $result;
    }

    /**
     * Update a product in Stripe with local values.
     */
    protected function updateStripeProduct(Product $product): void
    {
        try {
            StripeProduct::update($product->stripe_product_id, [
                'name' => $product->name,
                'description' => $product->description ?? '',
            ]);

            Log::info('Updated Stripe product', [
                'product_id' => $product->id,
                'stripe_product_id' => $product->stripe_product_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update Stripe product', [
                'product_id' => $product->id,
                'stripe_product_id' => $product->stripe_product_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync price to Stripe. Creates a new price if amount changed (Stripe prices are immutable).
     *
     * @return string|null The new price ID if created, null otherwise
     */
    protected function syncPriceToStripe(Product $product): ?string
    {
        if (! $product->stripe_price_id) {
            return $this->createStripePrice($product);
        }

        $currentPrice = $this->fetchStripePrice($product->stripe_price_id);

        if (! $currentPrice) {
            return $this->createStripePrice($product);
        }

        if ($currentPrice['amount'] === $product->price_cents &&
            $currentPrice['currency'] === $product->currency) {
            Log::debug('Price unchanged, skipping Stripe price update', [
                'product_id' => $product->id,
            ]);

            return null;
        }

        return $this->createStripePrice($product);
    }

    /**
     * Create a new price in Stripe and set it as the default.
     */
    protected function createStripePrice(Product $product): ?string
    {
        try {
            $price = Price::create([
                'product' => $product->stripe_product_id,
                'unit_amount' => $product->price_cents,
                'currency' => $product->currency,
            ]);

            StripeProduct::update($product->stripe_product_id, [
                'default_price' => $price->id,
            ]);

            $product->update(['stripe_price_id' => $price->id]);

            Log::info('Created new Stripe price', [
                'product_id' => $product->id,
                'stripe_price_id' => $price->id,
                'amount' => $product->price_cents,
            ]);

            return $price->id;
        } catch (\Throwable $e) {
            Log::error('Failed to create Stripe price', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
