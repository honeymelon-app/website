<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Admin;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseResetActivationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->user = User::factory()->create();
        $this->order = Order::factory()->for($this->user)->create();
    }

    public function test_admin_can_reset_activated_license(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
            'activated_at' => now(),
            'device_id' => 'test-device-123',
            'activation_count' => 3,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.reset-activation', $license));

        $response->assertRedirect(route('admin.licenses.show', $license))
            ->assertSessionHas('success', 'License activation has been reset. The license can now be activated on a new device.');

        $license->refresh();
        $this->assertNull($license->activated_at);
        $this->assertNull($license->device_id);
        $this->assertEquals(0, $license->activation_count);
        $this->assertEquals(LicenseStatus::ACTIVE, $license->status);
    }

    public function test_cannot_reset_non_activated_license(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
            'activated_at' => null,
            'device_id' => null,
            'activation_count' => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.reset-activation', $license));

        $response->assertForbidden();

        $license->refresh();
        $this->assertNull($license->activated_at);
        $this->assertNull($license->device_id);
        $this->assertEquals(0, $license->activation_count);
    }

    public function test_cannot_reset_revoked_license(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::REVOKED,
            'activated_at' => now(),
            'device_id' => 'test-device-123',
            'activation_count' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.reset-activation', $license));

        $response->assertForbidden();

        $license->refresh();
        $this->assertNotNull($license->activated_at);
        $this->assertEquals('test-device-123', $license->device_id);
        $this->assertEquals(1, $license->activation_count);
    }

    public function test_cannot_reset_refunded_license(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::REFUNDED,
            'activated_at' => now(),
            'device_id' => 'test-device-123',
            'activation_count' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.licenses.reset-activation', $license));

        $response->assertForbidden();

        $license->refresh();
        $this->assertNotNull($license->activated_at);
        $this->assertEquals('test-device-123', $license->device_id);
        $this->assertEquals(1, $license->activation_count);
    }

    public function test_guests_cannot_reset_license_activation(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
            'activated_at' => now(),
            'device_id' => 'test-device-123',
            'activation_count' => 1,
        ]);

        $response = $this->post(route('admin.licenses.reset-activation', $license));

        $response->assertRedirect(route('login'));

        $license->refresh();
        $this->assertNotNull($license->activated_at);
        $this->assertEquals('test-device-123', $license->device_id);
        $this->assertEquals(1, $license->activation_count);
    }

    public function test_reset_activation_allows_reactivation_on_different_device(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
            'activated_at' => now(),
            'device_id' => 'old-device',
            'activation_count' => 1,
        ]);

        // Reset activation
        $this->actingAs($this->admin)
            ->post(route('admin.licenses.reset-activation', $license))
            ->assertRedirect();

        $license->refresh();

        // Now try to activate on a new device
        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => $license->key_plain,
            'app_version' => '1.0.0',
            'device_id' => 'new-device',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $license->refresh();
        $this->assertNotNull($license->activated_at);
        $this->assertEquals('new-device', $license->device_id);
        $this->assertEquals(1, $license->activation_count);
    }
}
