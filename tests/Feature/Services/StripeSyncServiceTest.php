<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Product;
use App\Services\StripeSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StripeSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_creates_missing_products_from_stripe(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');

        $stripeProduct = \Stripe\Product::constructFrom([
            'id' => 'prod_123',
            'name' => 'Honeymelon Pro',
            'description' => 'Pro tier',
            'default_price' => 'price_123',
            'active' => true,
            'metadata' => [],
        ], null);

        $stripeProductsPage = (object) [
            'data' => [$stripeProduct],
            'has_more' => false,
        ];

        $service = Mockery::mock(StripeSyncService::class)
            ->makePartial();

        $service
            ->shouldReceive('fetchStripeProducts')
            ->once()
            ->withAnyArgs()
            ->andReturn($stripeProductsPage);

        $service
            ->shouldReceive('fetchStripePrice')
            ->once()
            ->with('price_123')
            ->andReturn([
                'id' => 'price_123',
                'amount' => 9900,
                'currency' => 'usd',
            ]);

        $stats = $service->syncProducts();

        $this->assertSame(['synced' => 1, 'skipped' => 0, 'errors' => 0], $stats);

        $this->assertDatabaseCount('products', 1);
        $product = Product::query()->firstOrFail();

        $this->assertSame('prod_123', $product->stripe_product_id);
        $this->assertSame('price_123', $product->stripe_price_id);
        $this->assertSame(9900, $product->price_cents);
        $this->assertSame('usd', $product->currency);
        $this->assertSame('honeymelon-pro', $product->slug);
    }

    public function test_it_generates_a_unique_slug_when_creating_products_from_stripe(): void
    {
        config()->set('services.stripe.secret', 'sk_test_123');

        Product::factory()->create([
            'slug' => 'honeymelon-pro',
            'stripe_product_id' => null,
        ]);

        $stripeProduct = \Stripe\Product::constructFrom([
            'id' => 'prod_456',
            'name' => 'Honeymelon Pro',
            'description' => null,
            'default_price' => null,
            'active' => true,
            'metadata' => [],
        ], null);

        $stripeProductsPage = (object) [
            'data' => [$stripeProduct],
            'has_more' => false,
        ];

        $service = Mockery::mock(StripeSyncService::class)
            ->makePartial();

        $service
            ->shouldReceive('fetchStripeProducts')
            ->once()
            ->withAnyArgs()
            ->andReturn($stripeProductsPage);

        $service
            ->shouldReceive('fetchStripePrice')
            ->never();

        $stats = $service->syncProducts();

        $this->assertSame(['synced' => 1, 'skipped' => 0, 'errors' => 0], $stats);

        $this->assertDatabaseCount('products', 2);

        $created = Product::query()->where('stripe_product_id', 'prod_456')->firstOrFail();
        $this->assertSame('honeymelon-pro-2', $created->slug);
    }
}
