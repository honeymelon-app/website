<?php

declare(strict_types=1);

namespace Tests\Feature\Filters;

use App\Filters\OrderFilter;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    public function test_filters_orders_by_provider(): void
    {
        Order::factory()->lemonsqueezy()->forUser($this->user)->forProduct($this->product)->create();
        Order::factory()->lemonsqueezy()->forUser($this->user)->forProduct($this->product)->create();
        Order::factory()->stripe()->forUser($this->user)->forProduct($this->product)->create();

        $filter = new OrderFilter(request()->merge(['provider' => 'ls']));
        $results = Order::filter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($order) => $order->provider === 'ls'));
    }

    public function test_filters_orders_by_exact_email(): void
    {
        $targetOrder = Order::factory()->forUser($this->user)->forProduct($this->product)->create(['email' => 'test@example.com']);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['email' => 'other@example.com']);

        $filter = new OrderFilter(request()->merge(['email' => 'test@example.com']));
        $results = Order::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($targetOrder->id, $results->first()->id);
    }

    public function test_filters_orders_by_email_search(): void
    {
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['email' => 'john.doe@example.com']);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['email' => 'jane.doe@example.com']);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['email' => 'bob.smith@other.com']);

        $filter = new OrderFilter(request()->merge(['email_search' => 'doe']));
        $results = Order::filter($filter)->get();

        $this->assertCount(2, $results);
    }

    public function test_filters_orders_by_minimum_amount(): void
    {
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['amount_cents' => 1000]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['amount_cents' => 2000]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['amount_cents' => 3000]);

        $filter = new OrderFilter(request()->merge(['min_amount' => '2000']));
        $results = Order::filter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($order) => $order->amount_cents >= 2000));
    }

    public function test_filters_orders_by_maximum_amount(): void
    {
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['amount_cents' => 1000]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['amount_cents' => 2000]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['amount_cents' => 3000]);

        $filter = new OrderFilter(request()->merge(['max_amount' => '2000']));
        $results = Order::filter($filter)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($order) => $order->amount_cents <= 2000));
    }

    public function test_filters_orders_by_amount_range(): void
    {
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['amount_cents' => 1000]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['amount_cents' => 2500]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['amount_cents' => 4000]);

        $filter = new OrderFilter(request()->merge([
            'min_amount' => '2000',
            'max_amount' => '3000',
        ]));
        $results = Order::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals(2500, $results->first()->amount_cents);
    }

    public function test_filters_orders_by_currency(): void
    {
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['currency' => 'usd']);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['currency' => 'usd']);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['currency' => 'eur']);

        $filter = new OrderFilter(request()->merge(['currency' => 'USD']));
        $results = Order::filter($filter)->get();

        $this->assertCount(2, $results);
    }

    public function test_filters_orders_with_licenses(): void
    {
        $orderWithLicense = Order::factory()->forUser($this->user)->forProduct($this->product)->create();
        License::factory()->create(['order_id' => $orderWithLicense->id]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create();

        $filter = new OrderFilter(request()->merge(['has_license' => 'yes']));
        $results = Order::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($orderWithLicense->id, $results->first()->id);
    }

    public function test_filters_orders_without_licenses(): void
    {
        $orderWithLicense = Order::factory()->forUser($this->user)->forProduct($this->product)->create();
        License::factory()->create(['order_id' => $orderWithLicense->id]);
        $orderWithoutLicense = Order::factory()->forUser($this->user)->forProduct($this->product)->create();

        $filter = new OrderFilter(request()->merge(['has_license' => 'no']));
        $results = Order::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($orderWithoutLicense->id, $results->first()->id);
    }

    public function test_filters_orders_by_license_status_with(): void
    {
        $orderWithLicense = Order::factory()->forUser($this->user)->forProduct($this->product)->create();
        License::factory()->create(['order_id' => $orderWithLicense->id]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create();

        $filter = new OrderFilter(request()->merge(['license_status' => 'with']));
        $results = Order::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($orderWithLicense->id, $results->first()->id);
    }

    public function test_filters_orders_by_license_status_without(): void
    {
        $orderWithLicense = Order::factory()->forUser($this->user)->forProduct($this->product)->create();
        License::factory()->create(['order_id' => $orderWithLicense->id]);
        $orderWithoutLicense = Order::factory()->forUser($this->user)->forProduct($this->product)->create();

        $filter = new OrderFilter(request()->merge(['license_status' => 'without']));
        $results = Order::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($orderWithoutLicense->id, $results->first()->id);
    }

    public function test_filters_orders_created_after_date(): void
    {
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['created_at' => now()->subDays(10)]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['created_at' => now()->subDays(5)]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['created_at' => now()]);

        $filter = new OrderFilter(request()->merge(['created_after' => now()->subDays(7)->toDateString()]));
        $results = Order::filter($filter)->get();

        $this->assertCount(2, $results);
    }

    public function test_filters_orders_created_before_date(): void
    {
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['created_at' => now()->subDays(10)]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['created_at' => now()->subDays(5)]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['created_at' => now()]);

        $filter = new OrderFilter(request()->merge(['created_before' => now()->subDays(7)->toDateString()]));
        $results = Order::filter($filter)->get();

        $this->assertCount(1, $results);
    }

    public function test_filters_orders_within_date_range(): void
    {
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['created_at' => now()->subDays(15)]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['created_at' => now()->subDays(7)]);
        Order::factory()->forUser($this->user)->forProduct($this->product)->create(['created_at' => now()]);

        $filter = new OrderFilter(request()->merge([
            'created_after' => now()->subDays(10)->toDateString(),
            'created_before' => now()->subDays(5)->toDateString(),
        ]));
        $results = Order::filter($filter)->get();

        $this->assertCount(1, $results);
    }

    public function test_applies_multiple_filters_together(): void
    {
        Order::factory()->lemonsqueezy()->forUser($this->user)->forProduct($this->product)->create([
            'email' => 'match@example.com',
            'amount_cents' => 5000,
        ]);
        Order::factory()->stripe()->forUser($this->user)->forProduct($this->product)->create([
            'email' => 'match@example.com',
            'amount_cents' => 5000,
        ]);
        Order::factory()->lemonsqueezy()->forUser($this->user)->forProduct($this->product)->create([
            'email' => 'other@example.com',
            'amount_cents' => 5000,
        ]);
        Order::factory()->lemonsqueezy()->forUser($this->user)->forProduct($this->product)->create([
            'email' => 'match@example.com',
            'amount_cents' => 1000,
        ]);

        $filter = new OrderFilter(request()->merge([
            'provider' => 'ls',
            'email' => 'match@example.com',
            'min_amount' => '3000',
        ]));
        $results = Order::filter($filter)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('ls', $results->first()->provider);
        $this->assertEquals('match@example.com', $results->first()->email);
        $this->assertGreaterThanOrEqual(3000, $results->first()->amount_cents);
    }

    public function test_returns_all_orders_when_no_filters_applied(): void
    {
        Order::factory()->forUser($this->user)->forProduct($this->product)->count(5)->create();

        $filter = new OrderFilter(request());
        $results = Order::filter($filter)->get();

        $this->assertCount(5, $results);
    }

    public function test_eager_loads_license_relationship(): void
    {
        $order = Order::factory()->forUser($this->user)->forProduct($this->product)->create();
        License::factory()->create(['order_id' => $order->id]);

        $filter = new OrderFilter(request());
        $results = Order::filter($filter)->get();

        $this->assertTrue($results->first()->relationLoaded('license'));
    }
}
