<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Admin;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderIndexTest extends TestCase
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

    #[Test]
    public function guests_cannot_access_orders_index(): void
    {
        $this->get(route('admin.orders.index'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_users_can_access_orders_index(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->has('orders')
                ->has('filters')
                ->has('sorting')
                ->has('pagination'));
    }

    #[Test]
    public function orders_are_paginated(): void
    {
        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->count(20)
            ->create();

        $this->actingAs($this->user)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->has('orders.data', 15)
                ->has('orders.meta')
                ->has('orders.links'));
    }

    #[Test]
    public function orders_can_be_filtered_by_provider(): void
    {
        Order::factory()
            ->lemonsqueezy()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->count(2)
            ->create();

        Order::factory()
            ->stripe()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create();

        $this->actingAs($this->user)
            ->get(route('admin.orders.index', ['provider' => 'ls']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->has('orders.data', 2)
                ->where('filters.provider', 'ls'));
    }

    #[Test]
    public function orders_can_be_filtered_by_email_search(): void
    {
        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['email' => 'john.doe@example.com']);

        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['email' => 'jane.smith@other.com']);

        $this->actingAs($this->user)
            ->get(route('admin.orders.index', ['email_search' => 'doe']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->has('orders.data', 1)
                ->where('filters.email_search', 'doe'));
    }

    #[Test]
    public function orders_can_be_filtered_by_license_status(): void
    {
        $orderWithLicense = Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create();

        License::factory()->create(['order_id' => $orderWithLicense->id]);

        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create();

        $this->actingAs($this->user)
            ->get(route('admin.orders.index', ['license_status' => 'with']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->has('orders.data', 1)
                ->where('filters.license_status', 'with'));
    }

    #[Test]
    public function orders_can_be_sorted_by_email(): void
    {
        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['email' => 'zebra@example.com']);

        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['email' => 'alpha@example.com']);

        $this->actingAs($this->user)
            ->get(route('admin.orders.index', ['sort' => 'email', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->where('sorting.column', 'email')
                ->where('sorting.direction', 'asc')
                ->where('orders.data.0.email', 'alpha@example.com'));
    }

    #[Test]
    public function orders_can_be_sorted_by_amount(): void
    {
        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['amount_cents' => 1000]);

        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['amount_cents' => 5000]);

        $this->actingAs($this->user)
            ->get(route('admin.orders.index', ['sort' => 'amount_cents', 'direction' => 'desc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->where('sorting.column', 'amount_cents')
                ->where('sorting.direction', 'desc')
                ->where('orders.data.0.amount_cents', 5000));
    }

    #[Test]
    public function orders_default_to_newest_first(): void
    {
        $oldOrder = Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['created_at' => now()->subDays(5)]);

        $newOrder = Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['created_at' => now()]);

        $this->actingAs($this->user)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->where('orders.data.0.id', $newOrder->id));
    }

    #[Test]
    public function page_size_can_be_changed(): void
    {
        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->count(30)
            ->create();

        $this->actingAs($this->user)
            ->get(route('admin.orders.index', ['per_page' => 25]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->has('orders.data', 25)
                ->where('pagination.pageSize', 25));
    }

    #[Test]
    public function invalid_page_size_defaults_to_15(): void
    {
        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->count(20)
            ->create();

        $this->actingAs($this->user)
            ->get(route('admin.orders.index', ['per_page' => 999]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->where('pagination.pageSize', 15));
    }

    #[Test]
    public function invalid_sort_column_is_ignored(): void
    {
        Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create();

        $this->actingAs($this->user)
            ->get(route('admin.orders.index', ['sort' => 'invalid_column']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->where('sorting.column', null));
    }

    #[Test]
    public function filters_and_sorting_can_be_combined(): void
    {
        Order::factory()
            ->lemonsqueezy()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['email' => 'zebra@example.com', 'amount_cents' => 2000]);

        Order::factory()
            ->lemonsqueezy()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['email' => 'alpha@example.com', 'amount_cents' => 3000]);

        Order::factory()
            ->stripe()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create(['email' => 'beta@example.com', 'amount_cents' => 5000]);

        $this->actingAs($this->user)
            ->get(route('admin.orders.index', [
                'provider' => 'ls',
                'sort' => 'amount_cents',
                'direction' => 'desc',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->has('orders.data', 2)
                ->where('filters.provider', 'ls')
                ->where('sorting.column', 'amount_cents')
                ->where('sorting.direction', 'desc')
                ->where('orders.data.0.amount_cents', 3000));
    }

    #[Test]
    public function orders_include_license_relationship(): void
    {
        $order = Order::factory()
            ->forUser($this->user)
            ->forProduct($this->product)
            ->create();

        $license = License::factory()->create(['order_id' => $order->id]);

        $this->actingAs($this->user)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/orders/Index')
                ->has('orders.data.0.license'));
    }
}
