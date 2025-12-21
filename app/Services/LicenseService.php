<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\DateRanges;
use App\Contracts\LicenseManager;
use App\Enums\LicenseStatus;
use App\Events\LicenseIssued;
use App\Models\License;
use App\Support\LicenseBundle;
use App\Support\LicenseCodec;
use App\Support\LicenseSigner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LicenseService implements LicenseManager
{
    /**
     * Issue a new signed license for an order.
     *
     * @param  array{order_id: string, max_major_version?: int}  $orderData
     */
    public function issue(array $orderData): License
    {
        $license = License::make([
            'status' => LicenseStatus::ACTIVE,
            'max_major_version' => max(0, min(999, (int) ($orderData['max_major_version'] ?? 0))),
            'order_id' => $orderData['order_id'],
        ]);

        $license->id = (string) Str::uuid();

        $issuedAt = now();

        $bundle = LicenseBundle::create($license, $issuedAt);

        $license->key_plain = $bundle['key'];
        $license->key = $this->hashKey($bundle['key']);
        $license->meta = [
            'issued_at' => $issuedAt->toIso8601String(),
            'max_major_version' => $license->max_major_version,
            'signature' => base64_encode($bundle['signature']),
            'payload' => base64_encode($bundle['payload']),
            'version' => 1,
        ];

        $license->save();

        Log::info('License issued', [
            'license_id' => $license->id,
            'key_last_6' => substr($bundle['key'], -6),
        ]);

        event(new LicenseIssued($license));

        return $license->refresh();
    }

    /**
     * Check if a license key is valid (with caching).
     */
    public function isValid(string $key): bool
    {
        $normalized = LicenseCodec::normalize($key);
        $hashed = $this->hashKey($normalized);

        $cacheKey = "license:valid:{$hashed}";

        return Cache::remember($cacheKey, DateRanges::LICENSE_CACHE_SECONDS, function () use ($key, $hashed) {
            $license = License::where('key', $hashed)->first();

            if (! $license) {
                return false;
            }

            if ($license->status !== LicenseStatus::ACTIVE) {
                return false;
            }

            try {
                $bundle = LicenseBundle::decode($key);
            } catch (Throwable $exception) {
                Log::warning('Failed to decode license key', [
                    'license_id' => $license->id,
                    'message' => $exception->getMessage(),
                ]);

                return false;
            }

            $isValid = LicenseSigner::verify($bundle['payload'], $bundle['signature']);

            if (! $isValid) {
                Log::warning('License signature verification failed', [
                    'license_id' => $license->id,
                ]);
            }

            return $isValid;
        });
    }

    /**
     * Revoke a license.
     */
    public function revoke(License $license): void
    {
        Log::info('Revoking license', ['license_id' => $license->id]);

        $license->update(['status' => LicenseStatus::REVOKED]);

        Cache::forget("license:valid:{$license->key}");

        Log::info('License revoked', ['license_id' => $license->id]);
    }

    /**
     * Find a license by its key.
     */
    public function findByKey(string $key): ?License
    {
        $normalized = LicenseCodec::normalize($key);
        $hashed = $this->hashKey($normalized);

        return License::where('key', $hashed)->first();
    }

    /**
     * Find and validate a license by its key, returning both the license and validation status.
     *
     * @return array{license: ?License, is_valid: bool}
     */
    public function findAndValidate(string $key): array
    {
        $license = $this->findByKey($key);

        if (! $license) {
            return ['license' => null, 'is_valid' => false];
        }

        $isValid = $this->isValid($key);

        return ['license' => $license, 'is_valid' => $isValid];
    }

    protected function hashKey(string $key): string
    {
        $normalized = LicenseCodec::normalize($key);

        return hash('sha256', $normalized);
    }
}
