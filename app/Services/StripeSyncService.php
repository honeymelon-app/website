<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Stripe\Charge;
use Stripe\Price;
use Stripe\Product as StripeProduct;
use Stripe\Stripe;

class StripeSyncService
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

        $startingAfter = null;

        do {
            $stripeProducts = $this->fetchStripeProducts(startingAfter: $startingAfter);

            if (! $stripeProducts) {
                $stats['errors']++;
                break;
            }

            foreach ($stripeProducts->data as $stripeProduct) {
                // Skip products that are not active and are archived (Stripe fields)
                if (! (bool) ($stripeProduct->active ?? true)) {
                    $stats['skipped']++;
                    $startingAfter = $stripeProduct->id;

                    continue;
                }

                try {
                    $result = $this->syncOrCreateProductFromStripe($stripeProduct);
                    $stats[$result ? 'synced' : 'skipped']++;
                } catch (\Throwable $e) {
                    $stats['errors']++;
                }

                $startingAfter = $stripeProduct->id;
            }
        } while ($stripeProducts->has_more);

        return $stats;
    }

    /**
     * Fetch products from Stripe (paginated).
     */
    public function fetchStripeProducts(?string $startingAfter = null, int $limit = 100): ?object
    {
        try {
            $params = [
                'limit' => $limit,
                'expand' => ['data.default_price'],
            ];

            if ($startingAfter) {
                $params['starting_after'] = $startingAfter;
            }

            return StripeProduct::all($params);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Sync (or create) a local product from a Stripe product object.
     */
    protected function syncOrCreateProductFromStripe(StripeProduct $stripeProduct): bool
    {
        $created = false;

        $product = Product::query()
            ->where('stripe_product_id', $stripeProduct->id)
            ->first();

        if (! $product) {
            $slugSource = null;
            $metadata = (array) ($stripeProduct->metadata ?? []);
            if (isset($metadata['slug']) && is_string($metadata['slug']) && $metadata['slug'] !== '') {
                $slugSource = $metadata['slug'];
            }

            $product = Product::create([
                'name' => $stripeProduct->name,
                'slug' => $this->generateUniqueProductSlug($slugSource ?: $stripeProduct->name),
                'description' => $stripeProduct->description,
                'stripe_product_id' => $stripeProduct->id,
                'stripe_price_id' => null,
                'price_cents' => 0,
                'currency' => 'usd',
                'is_active' => (bool) ($stripeProduct->active ?? true),
            ]);

            $created = true;
        }

        $synced = $this->syncProductFromStripeProduct($product, $stripeProduct);

        return $created || $synced;
    }

    /**
     * Sync a local product from an already-fetched Stripe product.
     */
    protected function syncProductFromStripeProduct(Product $product, StripeProduct $stripeProduct): bool
    {
        $updates = [];

        if ($stripeProduct->name && $stripeProduct->name !== $product->name) {
            $updates['name'] = $stripeProduct->name;
        }

        if ($stripeProduct->description !== $product->description) {
            $updates['description'] = $stripeProduct->description;
        }

        $defaultPriceId = $stripeProduct->default_price?->id ?? $stripeProduct->default_price;
        if ($defaultPriceId && $defaultPriceId !== $product->stripe_price_id) {
            $updates['stripe_price_id'] = $defaultPriceId;
        }

        if ($defaultPriceId) {
            $priceData = null;

            if (is_object($stripeProduct->default_price) && isset($stripeProduct->default_price->unit_amount, $stripeProduct->default_price->currency)) {
                $priceData = [
                    'id' => $stripeProduct->default_price->id,
                    'amount' => $stripeProduct->default_price->unit_amount,
                    'currency' => $stripeProduct->default_price->currency,
                ];
            } else {
                $priceData = $this->fetchStripePrice($defaultPriceId);
            }

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
            return false;
        }

        $product->update($updates);

        return true;
    }

    protected function generateUniqueProductSlug(string $source): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'product';
        }

        $slug = $base;
        $suffix = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Sync a single product from Stripe.
     */
    public function syncProduct(Product $product): bool
    {
        if (! $product->stripe_product_id) {
            return false;
        }

        $stripeProduct = $this->fetchStripeProduct($product->stripe_product_id);

        if (! $stripeProduct) {
            return false;
        }

        return $this->syncProductFromStripeProduct($product, $stripeProduct);
    }

    /**
     * Fetch a product from Stripe.
     */
    protected function fetchStripeProduct(string $stripeProductId): ?StripeProduct
    {
        try {
            return StripeProduct::retrieve($stripeProductId);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Fetch a price from Stripe.
     *
     * @return array{id: string, amount: int, currency: string}|null
     */
    public function fetchStripePrice(string $stripePriceId): ?array
    {
        try {
            $price = Price::retrieve($stripePriceId);

            return [
                'id' => $price->id,
                'amount' => $price->unit_amount,
                'currency' => $price->currency,
            ];
        } catch (\Throwable $e) {
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

        } catch (\Throwable $e) {
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

            return $price->id;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Sync orders from Stripe to local database.
     *
     * @return array{synced: int, created: int, skipped: int, errors: int}
     */
    public function syncOrders(?int $limit = 100, ?string $startingAfter = null): array
    {
        $stats = ['synced' => 0, 'created' => 0, 'skipped' => 0, 'errors' => 0];

        try {
            // Fetch charges from Stripe (completed payments)
            $params = [
                'limit' => $limit,
                'expand' => ['data.customer'],
            ];

            if ($startingAfter) {
                $params['starting_after'] = $startingAfter;
            }

            $charges = Charge::all($params);

            foreach ($charges->data as $charge) {
                try {
                    if ($charge->status !== 'succeeded') {
                        $stats['skipped']++;

                        continue;
                    }

                    $result = $this->syncOrder($charge);
                    $stats[$result]++;
                } catch (\Throwable $e) {
                    $stats['errors']++;
                }
            }

        } catch (\Throwable $e) {
            $stats['errors']++;
        }

        return $stats;
    }

    /**
     * Sync a single order from a Stripe charge.
     *
     * @return string 'synced', 'created', or 'skipped'
     */
    protected function syncOrder(Charge $charge): string
    {
        // Check if order already exists
        $existingOrder = Order::where('provider', 'stripe')
            ->where('external_id', $charge->id)
            ->first();

        $email = $charge->billing_details?->email ?? $charge->receipt_email;
        $amountCents = $charge->amount;
        $currency = strtolower($charge->currency);

        // Extract metadata
        $meta = [];
        if ($charge->metadata && count((array) $charge->metadata) > 0) {
            $meta = (array) $charge->metadata;
        }

        // Try to find the user by email
        $user = null;
        if ($email) {
            $user = User::where('email', $email)->first();
        }

        // Try to find the product
        $product = null;
        $productId = $meta['product_id'] ?? null;
        if ($productId) {
            $product = Product::find($productId);
        }

        // If no product found via metadata, try to find it via price ID or product ID in metadata
        if (! $product && isset($meta['price_id'])) {
            $product = Product::where('stripe_price_id', $meta['price_id'])->first();
        }

        if (! $product && isset($meta['stripe_product_id'])) {
            $product = Product::where('stripe_product_id', $meta['stripe_product_id'])->first();
        }

        $orderData = [
            'provider' => 'stripe',
            'external_id' => $charge->id,
            'email' => $email,
            'amount_cents' => $amountCents,
            'currency' => $currency,
            'meta' => $meta,
            'user_id' => $user?->id,
            'product_id' => $product?->id,
        ];

        if ($existingOrder) {
            // Update existing order if data has changed
            $updated = false;
            foreach ($orderData as $key => $value) {
                if ($existingOrder->{$key} !== $value) {
                    $updated = true;

                    break;
                }
            }

            if ($updated) {
                $existingOrder->update($orderData);

                return 'synced';
            }

            return 'skipped';
        }

        // Create new order
        $order = Order::create($orderData);

        return 'created';
    }
}
