<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Admin;

use App\Models\License;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_issue_license_and_creates_manual_order(): void
    {
        $user = User::factory()->create();

        $email = 'test@example.com';

        $this
            ->actingAs($user)
            ->post(route('admin.licenses.store'), [
                'email' => $email,
                'max_major_version' => 2,
            ])
            ->assertRedirect(route('admin.licenses.index'))
            ->assertSessionHas('license_key')
            ->assertSessionHas('license_email', $email);

        $order = Order::query()
            ->where('provider', 'manual')
            ->where('email', $email)
            ->first();

        $this->assertNotNull($order);
        $this->assertSame(0, $order->amount_cents);
        $this->assertSame('USD', $order->currency);

        $license = License::query()
            ->where('order_id', $order->id)
            ->first();

        $this->assertNotNull($license);
        $this->assertSame(2, $license->max_major_version);
    }
}
