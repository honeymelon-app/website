<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LicenseStatus;
use App\Models\License;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseService
{
    /**
     * Issue a new license for an order.
     *
     * @param  array{order_id: string, seats?: int, entitlements?: array<string>, updates_until?: \DateTimeInterface}  $orderData
     */
    public function issue(array $orderData): License
    {
        $key = $this->generateKey();

        Log::info('Issuing license', ['key_last4' => substr($key, -4)]);

        $license = License::create([
            'key' => $key,
            'status' => LicenseStatus::ACTIVE,
            'seats' => $orderData['seats'] ?? 1,
            'entitlements' => $orderData['entitlements'] ?? ['standard'],
            'updates_until' => $orderData['updates_until'] ?? now()->addYear(),
            'meta' => [
                'issued_at' => now()->toIso8601String(),
            ],
            'order_id' => $orderData['order_id'],
        ]);

        Log::info('License issued', ['license_id' => $license->id]);

        return $license;
    }

    /**
     * Check if a license key is valid (with caching).
     */
    public function isValid(string $key): bool
    {
        $cacheKey = "license:valid:{$key}";

        return Cache::remember($cacheKey, 300, function () use ($key) {
            $license = License::where('key', $key)->first();

            if (! $license) {
                return false;
            }

            if ($license->status !== LicenseStatus::ACTIVE) {
                return false;
            }

            if ($license->updates_until && $license->updates_until->isPast()) {
                return false;
            }

            return true;
        });
    }

    /**
     * Revoke a license.
     */
    public function revoke(License $license): void
    {
        Log::info('Revoking license', ['license_id' => $license->id]);

        $license->update(['status' => LicenseStatus::REVOKED]);

        // Clear cache
        Cache::forget("license:valid:{$license->key}");

        Log::info('License revoked', ['license_id' => $license->id]);
    }

    /**
     * Generate a license key in format XXXX-XXXX-XXXX-XXXX.
     */
    protected function generateKey(): string
    {
        return strtoupper(implode('-', [
            Str::random(4),
            Str::random(4),
            Str::random(4),
            Str::random(4),
        ]));
    }
}
