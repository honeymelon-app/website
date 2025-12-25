<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderRefundTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_cannot_refund_orders(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->stripe()->forUser($user)->forProduct($product)->create();

        $this->post(route('admin.orders.refund', $order), ['reason' => 'Customer requested'])
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function admins_can_refund_orders(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->stripe()->forUser($user)->forProduct($product)->create();

        $this->mock(RefundService::class, function ($mock) use ($order) {
            $mock->shouldReceive('refund')
                ->once()
                ->withArgs(function ($orderArg, $reason) use ($order): bool {
                    return $orderArg instanceof Order
                        && $orderArg->is($order)
                        && $reason === 'Customer requested';
                })
                ->andReturn($order);
        });

        $this->actingAs($user)
            ->post(route('admin.orders.refund', $order), ['reason' => 'Customer requested'])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('success', 'Order has been refunded successfully. The associated license has been revoked.');
    }

    #[Test]
    public function refund_reason_is_validated(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->stripe()->forUser($user)->forProduct($product)->create();

        $this->mock(RefundService::class, function ($mock) {
            $mock->shouldNotReceive('refund');
        });

        $this->actingAs($user)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.refund', $order), ['reason' => str_repeat('a', 501)])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHasErrors(['reason']);
    }
}
