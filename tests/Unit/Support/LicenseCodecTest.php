<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\LicenseCodec;
use PHPUnit\Framework\TestCase;

final class LicenseCodecTest extends TestCase
{
    public function test_encode_and_decode_round_trip(): void
    {
        $payload = random_bytes(64);

        $encoded = LicenseCodec::encode($payload);
        $this->assertMatchesRegularExpression('/^[A-Z2-9-]+$/', $encoded);

        $decoded = LicenseCodec::decode($encoded);

        $this->assertSame($payload, $decoded);
    }

    public function test_normalize_strips_separators(): void
    {
        $key = 'abcde-fghij-klmno';

        $this->assertSame('ABCDEFGHIJKLMNO', LicenseCodec::normalize($key));
    }
}
