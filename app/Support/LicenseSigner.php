<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class LicenseSigner
{
    /**
     * Sign a payload using the configured private key.
     */
    public static function sign(string $payload): string
    {
        $privateKey = config('license.signing.private_key');
        if (! $privateKey) {
            throw new RuntimeException('LICENSE_SIGNING_PRIVATE_KEY is not configured.');
        }

        $private = base64_decode($privateKey, true);
        if ($private === false) {
            throw new RuntimeException('Invalid base64 private key.');
        }

        if (! function_exists('sodium_crypto_sign_detached')) {
            throw new RuntimeException('Sodium extension is required to sign licenses.');
        }

        return sodium_crypto_sign_detached($payload, $private);
    }

    /**
     * Verify payload/signature against configured public key.
     */
    public static function verify(string $payload, string $signature): bool
    {
        $publicKey = config('license.signing.public_key');
        if (! $publicKey) {
            return false;
        }

        $public = base64_decode($publicKey, true);
        if ($public === false) {
            return false;
        }

        if (! function_exists('sodium_crypto_sign_verify_detached')) {
            return false;
        }

        return sodium_crypto_sign_verify_detached($signature, $payload, $public);
    }
}
