<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Support\LicenseBundle;
use App\Support\LicenseSigner;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LicenseBundleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('sodium_crypto_sign_keypair')) {
            $this->markTestSkipped('Sodium extension is required for signing.');
        }

        $keypair = sodium_crypto_sign_keypair();
        config()->set('license.signing.private_key', base64_encode($keypair));
        config()->set('license.signing.public_key', base64_encode(sodium_crypto_sign_publickey($keypair)));
    }

    public function test_bundle_round_trip(): void
    {
        $license = new License([
            'status' => LicenseStatus::ACTIVE,
            'max_major_version' => 1,
            'order_id' => Str::uuid()->toString(),
        ]);

        $license->id = Str::uuid()->toString();

        $issuedAt = now();
        $bundle = LicenseBundle::create($license, $issuedAt);

        $this->assertArrayHasKey('key', $bundle);
        $this->assertArrayHasKey('payload', $bundle);
        $this->assertArrayHasKey('signature', $bundle);

        $decoded = LicenseBundle::decode($bundle['key']);

        $this->assertSame($bundle['payload'], $decoded['payload']);
        $this->assertSame($bundle['signature'], $decoded['signature']);
        $this->assertTrue(LicenseSigner::verify($decoded['payload'], $decoded['signature']));

        $decodedPayload = \App\Support\LicensePayload::decode($decoded['payload']);
        $this->assertSame($license->id, $decodedPayload['license_id']);
        $this->assertSame($license->order_id, $decodedPayload['order_id']);
        $this->assertSame($license->max_major_version ?? 1, $decodedPayload['max_major_version']);
        $this->assertSame($issuedAt->unix(), $decodedPayload['issued_at']);
    }
}
