<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\License;
use DateTimeInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Canonical binary representation of a license payload.
 *
 * Layout (big-endian):
 *  - version:              1 byte
 *  - license uuid:        16 bytes
 *  - order uuid:          16 bytes
 *  - max major version:    1 byte
 *  - issued_at (epoch):    8 bytes (uint64)
 */
final class LicensePayload
{
    private const VERSION = 1;

    public const SERIALIZED_LENGTH = 42;

    public static function fromLicense(License $license, ?DateTimeInterface $issuedAt = null): string
    {
        $licenseId = self::uuidToBinary($license->getKey());
        $orderId = self::uuidToBinary((string) $license->order_id);
        $maxMajorVersion = (int) max(1, min(255, $license->max_major_version ?? 1));
        $issuedTimestamp = max(0, ($issuedAt ?? now())->getTimestamp());

        $payload = pack('C', self::VERSION);
        $payload .= $licenseId;
        $payload .= $orderId;
        $payload .= pack('C', $maxMajorVersion);
        $payload .= self::packUint64($issuedTimestamp);

        return $payload;
    }

    /**
     * @return array{version:int,license_id:string,order_id:string,max_major_version:int,issued_at:int}
     */
    public static function decode(string $payload): array
    {
        if (strlen($payload) !== self::SERIALIZED_LENGTH) {
            throw new InvalidArgumentException('Unexpected payload length.');
        }

        $offset = 0;
        $version = ord($payload[$offset]);
        $offset += 1;

        if ($version !== self::VERSION) {
            throw new InvalidArgumentException("Unsupported payload version: {$version}");
        }

        $licenseId = self::binaryToUuid(substr($payload, $offset, 16));
        $offset += 16;

        $orderId = self::binaryToUuid(substr($payload, $offset, 16));
        $offset += 16;

        $maxMajorVersion = ord($payload[$offset]);
        $offset += 1;

        $issuedAt = self::unpackUint64(substr($payload, $offset, 8));

        return [
            'version' => $version,
            'license_id' => $licenseId,
            'order_id' => $orderId,
            'max_major_version' => $maxMajorVersion,
            'issued_at' => $issuedAt,
        ];
    }

    public static function uuidToBinary(string $uuid): string
    {
        $normalized = Str::replace('-', '', trim($uuid));
        if (strlen($normalized) !== 32) {
            throw new InvalidArgumentException("Invalid UUID: {$uuid}");
        }

        $binary = hex2bin($normalized);
        if ($binary === false) {
            throw new InvalidArgumentException("Failed to parse UUID: {$uuid}");
        }

        return $binary;
    }

    public static function binaryToUuid(string $binary): string
    {
        if (strlen($binary) !== 16) {
            throw new InvalidArgumentException('Binary UUID must be 16 bytes.');
        }

        $hex = bin2hex($binary);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }

    private static function packUint64(int $value): string
    {
        $high = ($value >> 32) & 0xFFFFFFFF;
        $low = $value & 0xFFFFFFFF;

        return pack('N2', $high, $low);
    }

    private static function unpackUint64(string $bytes): int
    {
        $parts = unpack('N2', $bytes);
        if (! is_array($parts) || count($parts) !== 2) {
            throw new InvalidArgumentException('Failed to unpack uint64.');
        }

        return ((int) $parts[1] << 32) | ($parts[2] & 0xFFFFFFFF);
    }
}
