<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\LicenseActivator;
use App\Contracts\LicenseManager;
use App\Enums\ActivationError;
use App\Enums\LicenseStatus;
use App\Support\LicenseCodec;
use Illuminate\Support\Facades\Log;

final class ActivationService implements LicenseActivator
{
    public function __construct(
        protected LicenseManager $licenseService
    ) {}

    /**
     * Attempt to activate a license.
     *
     * @return array{success: bool, license?: array<string, mixed>, error?: string, error_code?: ActivationError}
     */
    public function activate(string $licenseKey, string $appVersion, ?string $deviceId = null): array
    {
        $normalizedKey = LicenseCodec::normalize($licenseKey);
        $license = $this->licenseService->findByKey($normalizedKey);

        if (! $license) {
            Log::info('Activation attempted with unknown license key', [
                'key_prefix' => substr($licenseKey, 0, 10).'...',
            ]);

            return [
                'success' => false,
                'error' => 'License not found.',
                'error_code' => ActivationError::LicenseNotFound,
            ];
        }

        if (! $license->status->allowsActivation()) {
            Log::info('Activation attempted for non-active license', [
                'license_id' => $license->id,
                'status' => $license->status->value,
            ]);

            $message = match ($license->status) {
                LicenseStatus::REFUNDED => 'This license has been refunded and cannot be activated.',
                LicenseStatus::REVOKED => 'This license has been revoked.',
                LicenseStatus::EXPIRED => 'This license has expired.',
                default => 'This license is not active.',
            };

            return [
                'success' => false,
                'error' => $message,
                'error_code' => ActivationError::LicenseNotActive,
            ];
        }

        if ($license->isActivated()) {
            Log::info('Activation attempted for already-activated license', [
                'license_id' => $license->id,
                'activated_at' => $license->activated_at,
            ]);

            return [
                'success' => false,
                'error' => 'This license has already been activated.',
                'error_code' => ActivationError::LicenseAlreadyActivated,
            ];
        }

        if (! $this->licenseService->isValid($licenseKey)) {
            Log::warning('Activation attempted with invalid license signature', [
                'license_id' => $license->id,
            ]);

            return [
                'success' => false,
                'error' => 'License key signature is invalid.',
                'error_code' => ActivationError::LicenseNotFound,
            ];
        }

        $license->markAsActivated($deviceId);

        Log::info('License activated successfully', [
            'license_id' => $license->id,
            'app_version' => $appVersion,
            'device_id' => $deviceId,
        ]);

        return [
            'success' => true,
            'license' => [
                'id' => $license->id,
                'key' => $license->key_plain,
                'order_id' => $license->order_id,
                'status' => $license->status->value,
                'activated_at' => $license->activated_at->timestamp,
                'max_major_version' => $license->max_major_version,
                'product' => 'honeymelon',
                'app_version' => $appVersion,
                'payload' => $license->meta['payload'] ?? null,
                'signature' => $license->meta['signature'] ?? null,
            ],
        ];
    }
}
