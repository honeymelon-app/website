<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LicenseActivationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('sodium_crypto_sign_keypair')) {
            $this->markTestSkipped('Sodium extension is required for signing.');
        }

        Cache::flush();

        $keypair = sodium_crypto_sign_keypair();
        $secret = sodium_crypto_sign_secretkey($keypair);
        $public = sodium_crypto_sign_publickey($keypair);

        config()->set('license.signing.private_key', base64_encode($secret));
        config()->set('license.signing.public_key', base64_encode($public));

        $this->user = User::factory()->create();
        $this->order = Order::factory()->for($this->user)->create();
    }

    public function test_can_activate_valid_license(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
            'activated_at' => null,
        ]);

        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => $license->key_plain,
            'app_version' => '1.0.0',
            'device_id' => 'test-device-123',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'license' => [
                    'id' => $license->id,
                    'status' => 'active',
                    'max_major_version' => $license->max_major_version,
                    'product' => 'honeymelon',
                    'app_version' => '1.0.0',
                ],
            ]);

        $license->refresh();
        $this->assertNotNull($license->activated_at);
        $this->assertEquals(1, $license->activation_count);
        $this->assertEquals('test-device-123', $license->device_id);
    }

    public function test_cannot_activate_already_activated_license(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
            'activated_at' => now(),
            'activation_count' => 1,
            'device_id' => 'test-device-123',
        ]);

        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => $license->key_plain,
            'app_version' => '1.0.0',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'error' => 'This license has already been activated.',
                'error_code' => 'license_already_activated',
            ]);
    }

    public function test_can_reactivate_already_activated_license_on_same_device(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
            'activated_at' => now(),
            'activation_count' => 1,
            'device_id' => 'test-device-123',
        ]);

        $activatedAt = $license->activated_at;

        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => $license->key_plain,
            'app_version' => '1.0.0',
            'device_id' => 'test-device-123',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'license' => [
                    'id' => $license->id,
                    'status' => 'active',
                    'product' => 'honeymelon',
                    'app_version' => '1.0.0',
                ],
            ]);

        $license->refresh();
        $this->assertEquals(1, $license->activation_count);
        $this->assertEquals('test-device-123', $license->device_id);
        $this->assertNotNull($license->activated_at);
        $this->assertNotNull($activatedAt);
        $this->assertEquals($activatedAt->timestamp, $license->activated_at->timestamp);
    }

    public function test_cannot_activate_refunded_license(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::REFUNDED,
            'activated_at' => null,
        ]);

        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => $license->key_plain,
            'app_version' => '1.0.0',
        ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'error' => 'This license has been refunded and cannot be activated.',
                'error_code' => 'license_not_active',
            ]);
    }

    public function test_cannot_activate_revoked_license(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::REVOKED,
            'activated_at' => null,
        ]);

        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => $license->key_plain,
            'app_version' => '1.0.0',
        ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'error' => 'This license has been revoked.',
                'error_code' => 'license_not_active',
            ]);
    }

    public function test_cannot_activate_unknown_license(): void
    {
        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => 'INVALID-LICENSE-KEY-12345',
            'app_version' => '1.0.0',
        ]);

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'error' => 'License not found.',
                'error_code' => 'license_not_found',
            ]);
    }

    public function test_activation_requires_license_key(): void
    {
        $response = $this->postJson('/api/licenses/activate', [
            'app_version' => '1.0.0',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['license_key']);
    }

    public function test_activation_requires_app_version(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => $license->key_plain,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['app_version']);
    }

    public function test_cannot_activate_license_for_higher_app_major_version(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
            'activated_at' => null,
            'max_major_version' => 1,
        ]);

        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => $license->key_plain,
            'app_version' => '2.0.0',
        ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'error_code' => 'license_version_not_allowed',
            ]);
    }

    public function test_activation_rejects_invalid_app_version(): void
    {
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
            'activated_at' => null,
        ]);

        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => $license->key_plain,
            'app_version' => 'not-a-version',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error_code' => 'invalid_app_version',
            ]);
    }

    public function test_refunded_license_cannot_be_reactivated(): void
    {
        // Create and activate a license
        $license = License::factory()->for($this->user)->for($this->order)->create([
            'status' => LicenseStatus::ACTIVE,
            'activated_at' => now(),
            'activation_count' => 1,
        ]);

        // Simulate refund
        $license->markAsRefunded();

        // Reset activation (simulating a reinstall)
        $license->update(['activated_at' => null, 'activation_count' => 0]);

        $response = $this->postJson('/api/licenses/activate', [
            'license_key' => $license->key_plain,
            'app_version' => '1.0.0',
        ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'error_code' => 'license_not_active',
            ]);
    }
}
