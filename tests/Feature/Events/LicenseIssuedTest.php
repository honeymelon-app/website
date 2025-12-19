<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Events\LicenseIssued;
use App\Listeners\SendLicenseEmailListener;
use App\Mail\LicenseKeyMail;
use App\Models\License;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LicenseIssuedTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_issued_event_is_dispatched_when_license_is_created(): void
    {
        Event::fake([LicenseIssued::class]);

        $order = Order::factory()->create();
        $licenseService = app(\App\Contracts\LicenseManager::class);

        $license = $licenseService->issue([
            'order_id' => $order->id,
            'max_major_version' => 1,
        ]);

        Event::assertDispatched(LicenseIssued::class, function ($event) use ($license) {
            return $event->license->id === $license->id;
        });
    }

    public function test_send_license_email_listener_queues_email(): void
    {
        Mail::fake();

        $order = Order::factory()->create(['email' => 'test@example.com']);
        $license = License::factory()->create([
            'order_id' => $order->id,
            'key_plain' => 'TEST-LICENSE-KEY',
        ]);

        $event = new LicenseIssued($license);
        $listener = new SendLicenseEmailListener;
        $listener->handle($event);

        Mail::assertQueued(LicenseKeyMail::class, function ($mail) use ($order) {
            return $mail->hasTo($order->email);
        });
    }

    public function test_send_license_email_listener_does_not_send_when_order_has_empty_email(): void
    {
        Mail::fake();

        // Use mock to simulate order with empty email
        $order = Order::factory()->create(['email' => 'test@example.com']);
        $license = License::factory()->create(['order_id' => $order->id]);

        // Temporarily update the order's email to empty string (simulating edge case)
        // In practice, this tests the listener's conditional check
        $order->update(['email' => '']);

        $event = new LicenseIssued($license->fresh());
        $listener = new SendLicenseEmailListener;
        $listener->handle($event);

        Mail::assertNothingQueued();
    }

    public function test_send_license_email_listener_implements_should_queue(): void
    {
        $listener = new SendLicenseEmailListener;

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $listener);
    }
}
