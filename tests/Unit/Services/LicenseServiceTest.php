<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\LicenseStatus;
use App\Models\Order;
use App\Services\LicenseService;
use App\Support\LicenseBundle;
use App\Support\LicenseCodec;
use App\Support\LicensePayload;
use App\Support\LicenseSigner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LicenseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('sodium_crypto_sign_keypair')) {
            $this->markTestSkipped('Sodium extension is required for signing.');
        }

        Cache::flush();

        $keypair = sodium_crypto_sign_keypair();
        config()->set('license.signing.private_key', base64_encode($keypair));
        config()->set('license.signing.public_key', base64_encode(sodium_crypto_sign_publickey($keypair)));
    }

    public function test_issue_generates_signed_license(): void
    {
        $order = Order::factory()->create();

        $service = app(LicenseService::class);
        $license = $service->issue([
            'order_id' => $order->getKey(),
            'max_major_version' => 2,
        ]);

        $this->assertSame(LicenseStatus::ACTIVE, $license->status);
        $this->assertNotNull($license->key_plain);
        $this->assertSame(64, strlen($license->key));
        $this->assertArrayHasKey('signature', $license->meta);
        $this->assertArrayHasKey('payload', $license->meta);
        $this->assertSame(2, $license->max_major_version);

        $bundle = LicenseBundle::decode($license->key_plain);
        $this->assertTrue(LicenseSigner::verify($bundle['payload'], $bundle['signature']));
    }

    public function test_is_valid_checks_signature(): void
    {
        $order = Order::factory()->create();
        $service = app(LicenseService::class);

        $license = $service->issue([
            'order_id' => $order->getKey(),
        ]);

        $this->assertTrue($service->isValid($license->key_plain));

        $invalidBytes = random_bytes(LicensePayload::SERIALIZED_LENGTH + LicenseBundle::SIGNATURE_LENGTH);
        $invalidKey = LicenseCodec::encode($invalidBytes);

        $this->assertFalse($service->isValid($invalidKey));
    }
}
