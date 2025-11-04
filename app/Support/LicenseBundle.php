<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\License;
use DateTimeInterface;
use RuntimeException;

final class LicenseBundle
{
    public const SIGNATURE_LENGTH = 64;

    /**
     * Compose human-facing key and internal binary data for a license.
     *
     * @return array{key:string,payload:string,signature:string}
     */
    public static function create(License $license, ?DateTimeInterface $issuedAt = null): array
    {
        $payload = LicensePayload::fromLicense($license, $issuedAt);
        $signature = LicenseSigner::sign($payload);

        $groupSize = (int) config('license.key.group_size', 5);
        $blob = $payload.$signature;
        $key = LicenseCodec::encode($blob, $groupSize);

        return [
            'key' => $key,
            'payload' => $payload,
            'signature' => $signature,
        ];
    }

    /**
     * Decode license key back into payload and signature.
     *
     * @return array{payload:string,signature:string}
     */
    public static function decode(string $key): array
    {
        $blob = LicenseCodec::decode($key);
        $payloadLength = LicensePayload::SERIALIZED_LENGTH;
        $signatureLength = self::SIGNATURE_LENGTH; // Ed25519 signature size

        $expectedLength = $payloadLength + $signatureLength;
        if (strlen($blob) !== $expectedLength) {
            throw new RuntimeException('Unexpected license blob length.');
        }

        $payload = substr($blob, 0, $payloadLength);
        $signature = substr($blob, $payloadLength, $signatureLength);

        return [
            'payload' => $payload,
            'signature' => $signature,
        ];
    }
}
