<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Encode/decode binary license blobs into a Windows-style key (e.g. XXXXX-XXXXX...).
 *
 * This uses a Crockford-like Base32 alphabet without ambiguous characters.
 */
final class LicenseCodec
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * Mapping from character to value.
     *
     * @var array<string, int>|null
     */
    private static ?array $decodeMap = null;

    /**
     * Encode raw binary into a hyphenated Base32 key.
     */
    public static function encode(string $binary, int $groupSize = 5): string
    {
        if ($binary === '') {
            throw new InvalidArgumentException('Cannot encode empty payload.');
        }

        $alphabet = self::ALPHABET;
        $buffer = 0;
        $bitsInBuffer = 0;
        $output = '';

        foreach (str_split($binary) as $char) {
            $buffer = ($buffer << 8) | ord($char);
            $bitsInBuffer += 8;

            while ($bitsInBuffer >= 5) {
                $bitsInBuffer -= 5;
                $index = ($buffer >> $bitsInBuffer) & 0b11111;
                $output .= $alphabet[$index];
            }
        }

        if ($bitsInBuffer > 0) {
            $index = ($buffer << (5 - $bitsInBuffer)) & 0b11111;
            $output .= $alphabet[$index];
        }

        if ($groupSize > 0) {
            $output = trim(chunk_split($output, $groupSize, '-'), '-');
        }

        return $output;
    }

    /**
     * Decode a license key into raw binary data.
     */
    public static function decode(string $key): string
    {
        $normalized = self::normalize($key);
        if ($normalized === '') {
            throw new InvalidArgumentException('License key is empty.');
        }

        $map = self::decodeMap();
        $buffer = 0;
        $bitsInBuffer = 0;
        $output = '';

        foreach (str_split($normalized) as $char) {
            if (! isset($map[$char])) {
                throw new InvalidArgumentException("Invalid license character: {$char}");
            }
            $value = $map[$char];
            $buffer = ($buffer << 5) | $value;
            $bitsInBuffer += 5;

            if ($bitsInBuffer >= 8) {
                $bitsInBuffer -= 8;
                $byte = ($buffer >> $bitsInBuffer) & 0xFF;
                $output .= chr($byte);
            }
        }

        // Ensure no leftover bits containing data (only zero padding allowed).
        if (($buffer & ((1 << $bitsInBuffer) - 1)) !== 0) {
            throw new InvalidArgumentException('License key has invalid padding bits.');
        }

        return $output;
    }

    /**
     * Normalize a key by stripping separators and uppercasing.
     */
    public static function normalize(string $key): string
    {
        $upper = strtoupper($key);

        return preg_replace('/[^A-Z2-9]/', '', $upper) ?? '';
    }

    /**
     * Lazily build the decode lookup table.
     *
     * @return array<string, int>
     */
    private static function decodeMap(): array
    {
        if (self::$decodeMap !== null) {
            return self::$decodeMap;
        }

        $map = [];
        foreach (str_split(self::ALPHABET) as $index => $char) {
            $map[$char] = $index;
        }

        self::$decodeMap = $map;

        return $map;
    }
}
