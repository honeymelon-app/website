<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Support\LicenseBundle;
use App\Support\LicenseCodec;
use App\Support\LicensePayload;
use App\Support\LicenseSigner;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Cross-platform compatibility tests to ensure licenses issued by the platform
 * can be validated by the Tauri app (Rust).
 *
 * The Rust app expects:
 * - Base32 alphabet: ABCDEFGHJKLMNPQRSTUVWXYZ23456789
 * - Payload length: 42 bytes
 * - Signature length: 64 bytes (Ed25519)
 * - Payload format (big-endian):
 *   - version: 1 byte
 *   - license_id: 16 bytes (UUID)
 *   - order_id: 16 bytes (UUID)
 *   - max_major_version: 1 byte
 *   - issued_at: 8 bytes (uint64)
 */
final class LicenseCompatibilityTest extends TestCase
{
    private string $privateKey;

    private string $publicKey;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('sodium_crypto_sign_keypair')) {
            $this->markTestSkipped('Sodium extension is required for signing.');
        }

        $keypair = sodium_crypto_sign_keypair();
        $this->privateKey = base64_encode(sodium_crypto_sign_secretkey($keypair));
        $this->publicKey = base64_encode(sodium_crypto_sign_publickey($keypair));

        config()->set('license.signing.private_key', $this->privateKey);
        config()->set('license.signing.public_key', $this->publicKey);
    }

    public function test_base32_alphabet_matches_rust(): void
    {
        // Rust alphabet: b"ABCDEFGHJKLMNPQRSTUVWXYZ23456789"
        $rustAlphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        // Generate a key and verify it only contains valid characters
        $blob = random_bytes(42 + 64);
        $key = LicenseCodec::encode($blob);
        $normalized = LicenseCodec::normalize($key);

        foreach (str_split($normalized) as $char) {
            $this->assertStringContainsString(
                $char,
                $rustAlphabet,
                "Character '{$char}' not in Rust Base32 alphabet"
            );
        }
    }

    public function test_payload_length_is_42_bytes(): void
    {
        $this->assertSame(42, LicensePayload::SERIALIZED_LENGTH);
    }

    public function test_signature_length_is_64_bytes(): void
    {
        $this->assertSame(64, LicenseBundle::SIGNATURE_LENGTH);
    }

    public function test_payload_binary_layout_matches_rust(): void
    {
        $licenseId = Str::uuid()->toString();
        $orderId = Str::uuid()->toString();
        $maxMajorVersion = 3;
        $issuedAt = now();

        $license = new License([
            'status' => LicenseStatus::ACTIVE,
            'max_major_version' => $maxMajorVersion,
            'order_id' => $orderId,
        ]);
        $license->id = $licenseId;

        $payload = LicensePayload::fromLicense($license, $issuedAt);

        // Verify length
        $this->assertSame(42, strlen($payload));

        // Parse according to Rust's expected layout
        $offset = 0;

        // Version: 1 byte
        $version = ord($payload[$offset]);
        $this->assertSame(1, $version);
        $offset += 1;

        // License ID: 16 bytes
        $parsedLicenseId = LicensePayload::binaryToUuid(substr($payload, $offset, 16));
        $this->assertSame($licenseId, $parsedLicenseId);
        $offset += 16;

        // Order ID: 16 bytes
        $parsedOrderId = LicensePayload::binaryToUuid(substr($payload, $offset, 16));
        $this->assertSame($orderId, $parsedOrderId);
        $offset += 16;

        // Max major version: 1 byte
        $parsedMaxMajor = ord($payload[$offset]);
        $this->assertSame($maxMajorVersion, $parsedMaxMajor);
        $offset += 1;

        // Issued at: 8 bytes (big-endian uint64)
        $parts = unpack('N2', substr($payload, $offset, 8));
        $parsedIssuedAt = ((int) $parts[1] << 32) | ($parts[2] & 0xFFFFFFFF);
        $this->assertSame($issuedAt->unix(), $parsedIssuedAt);
    }

    public function test_full_license_key_can_be_decoded(): void
    {
        $license = new License([
            'status' => LicenseStatus::ACTIVE,
            'max_major_version' => 2,
            'order_id' => Str::uuid()->toString(),
        ]);
        $license->id = Str::uuid()->toString();

        $issuedAt = now();
        $bundle = LicenseBundle::create($license, $issuedAt);

        // Decode the key (simulating what Rust does)
        $decoded = LicenseBundle::decode($bundle['key']);

        // Verify payload length
        $this->assertSame(42, strlen($decoded['payload']));

        // Verify signature length
        $this->assertSame(64, strlen($decoded['signature']));

        // Verify signature is valid
        $this->assertTrue(LicenseSigner::verify($decoded['payload'], $decoded['signature']));

        // Decode payload and verify contents
        $payloadData = LicensePayload::decode($decoded['payload']);
        $this->assertSame($license->id, $payloadData['license_id']);
        $this->assertSame($license->order_id, $payloadData['order_id']);
        $this->assertSame(2, $payloadData['max_major_version']);
        $this->assertSame($issuedAt->unix(), $payloadData['issued_at']);
    }

    public function test_ed25519_signature_format_is_compatible(): void
    {
        $payload = random_bytes(42);
        $signature = LicenseSigner::sign($payload);

        // Ed25519 signatures are always 64 bytes
        $this->assertSame(64, strlen($signature));

        // Verify the signature is valid with the same key
        $this->assertTrue(LicenseSigner::verify($payload, $signature));

        // Tampered payload should fail
        $tampered = $payload;
        $tampered[0] = chr(ord($tampered[0]) ^ 0xFF);
        $this->assertFalse(LicenseSigner::verify($tampered, $signature));
    }

    public function test_public_key_is_32_bytes_for_ed25519(): void
    {
        $publicKeyBytes = base64_decode($this->publicKey);
        $this->assertSame(32, strlen($publicKeyBytes));
    }

    public function test_normalized_key_only_contains_valid_characters(): void
    {
        $license = new License([
            'status' => LicenseStatus::ACTIVE,
            'max_major_version' => 1,
            'order_id' => Str::uuid()->toString(),
        ]);
        $license->id = Str::uuid()->toString();

        $bundle = LicenseBundle::create($license, now());
        $normalized = LicenseCodec::normalize($bundle['key']);

        // Should only contain uppercase letters (no I, O) and digits 2-9
        $this->assertMatchesRegularExpression('/^[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]+$/', $normalized);
    }
}
