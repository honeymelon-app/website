<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Enums\LicenseStatus;
use App\Events\OrderRefunded;
use App\Listeners\RevokeLicenseOnRefundListener;
use App\Models\License;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRefundedTest extends TestCase
{
    use RefreshDatabase;

    public function test_revoke_license_on_refund_listener_marks_license_as_refunded(): void
    {
        $order = Order::factory()->create();
        $license = License::factory()->active()->create(['order_id' => $order->id]);

        $this->assertTrue($license->isActive());

        $event = new OrderRefunded($order, $order->amount_cents);
        $listener = new RevokeLicenseOnRefundListener;
        $listener->handle($event);

        $license->refresh();

        $this->assertTrue($license->isRefunded());
        $this->assertEquals(LicenseStatus::REFUNDED, $license->status);
    }

    public function test_revoke_license_on_refund_listener_does_nothing_when_no_license(): void
    {
        $order = Order::factory()->create();

        $event = new OrderRefunded($order, $order->amount_cents);
        $listener = new RevokeLicenseOnRefundListener;

        // Should not throw exception
        $listener->handle($event);

        $this->assertNull($order->fresh()->license);
    }

    public function test_revoke_license_on_refund_listener_does_nothing_when_license_already_revoked(): void
    {
        $order = Order::factory()->create();
        $license = License::factory()->revoked()->create(['order_id' => $order->id]);

        $event = new OrderRefunded($order, $order->amount_cents);
        $listener = new RevokeLicenseOnRefundListener;
        $listener->handle($event);

        $license->refresh();

        // Should remain revoked (not changed to refunded)
        $this->assertTrue($license->isRevoked());
    }

    public function test_revoke_license_on_refund_listener_implements_should_queue(): void
    {
        $listener = new RevokeLicenseOnRefundListener;

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $listener);
    }

    public function test_order_refunded_event_contains_correct_data(): void
    {
        $order = Order::factory()->create(['amount_cents' => 2999]);

        $event = new OrderRefunded($order, 2999);

        $this->assertSame($order->id, $event->order->id);
        $this->assertEquals(2999, $event->refundAmount);
    }
}
