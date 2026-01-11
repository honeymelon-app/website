<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Support\Semver;
use Illuminate\Support\Facades\Log;

class ActivationService
{
    /**
     * Activation error codes.
     */
    public const ERROR_LICENSE_NOT_FOUND = 'license_not_found';

    public const ERROR_LICENSE_NOT_ACTIVE = 'license_not_active';

    public const ERROR_LICENSE_ALREADY_ACTIVATED = 'license_already_activated';

    public const ERROR_LICENSE_VERSION_NOT_ALLOWED = 'license_version_not_allowed';

    public const ERROR_INVALID_APP_VERSION = 'invalid_app_version';

    public function __construct(
        protected LicenseService $licenseService
    ) {}

    /**
     * Attempt to activate a license.
     *
     * @return array{success: bool, license?: array, error?: string, error_code?: string}
     */
    public function activate(string $licenseKey, string $appVersion, ?string $deviceId = null): array
    {
        $appMajor = Semver::major($appVersion);
        if ($appMajor === null) {
            return [
                'success' => false,
                'error' => 'Invalid app version.',
                'error_code' => self::ERROR_INVALID_APP_VERSION,
            ];
        }

        // Find and validate the license in one call
        ['license' => $license, 'is_valid' => $isValid] = $this->licenseService->findAndValidate($licenseKey);

        if (! $license) {
            Log::info('Activation attempted with unknown license key', [
                'key_prefix' => substr($licenseKey, 0, 10).'...',
            ]);

            return [
                'success' => false,
                'error' => 'License not found.',
                'error_code' => self::ERROR_LICENSE_NOT_FOUND,
            ];
        }

        // Check if license status allows activation
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
                'error_code' => self::ERROR_LICENSE_NOT_ACTIVE,
            ];
        }

        // Verify the key signature is valid
        if (! $isValid) {
            Log::warning('Activation attempted with invalid license signature', [
                'license_id' => $license->id,
            ]);

            return [
                'success' => false,
                'error' => 'License key signature is invalid.',
                'error_code' => self::ERROR_LICENSE_NOT_FOUND,
            ];
        }

        // Check if this is a lifetime license (255 = works with any major version)
        $isLifetimeLicense = (int) ($license->max_major_version ?? 0) === 255;

        // Enforce major-version licensing BEFORE checking activation status
        // Lifetime licenses work with any major version
        // Version-specific licenses only work with their designated major version
        if (! $isLifetimeLicense) {
            $maxMajor = (int) ($license->max_major_version ?? 0);
            if ($appMajor > $maxMajor) {
                Log::info('Activation attempted for unsupported app major version', [
                    'license_id' => $license->id,
                    'app_version' => $appVersion,
                    'app_major' => $appMajor,
                    'max_major_version' => $maxMajor,
                ]);

                return [
                    'success' => false,
                    'error' => "This license is valid up to Honeymelon {$maxMajor}.x.",
                    'error_code' => self::ERROR_LICENSE_VERSION_NOT_ALLOWED,
                ];
            }
        }

        // Check if already activated
        // Lifetime licenses (255) can be activated unlimited times on any device
        // Version-specific licenses can only be re-activated on the same device (supports reinstall)
        if ($license->isActivated() && ! $isLifetimeLicense) {
            // For non-lifetime licenses, allow idempotent re-activation on the same device only
            if ($deviceId !== null && $license->device_id !== null && hash_equals($license->device_id, $deviceId)) {
                Log::info('Activation replay accepted for same device', [
                    'license_id' => $license->id,
                    'device_id' => $deviceId,
                ]);

                return [
                    'success' => true,
                    'license' => $this->formatLicenseResponse($license, $appVersion),
                ];
            }

            Log::info('Activation attempted for already-activated license', [
                'license_id' => $license->id,
                'activated_at' => $license->activated_at,
            ]);

            return [
                'success' => false,
                'error' => 'This license has already been activated on another device.',
                'error_code' => self::ERROR_LICENSE_ALREADY_ACTIVATED,
            ];
        }

        // Activate the license
        // For lifetime licenses, track activation count but don't bind to device
        // For version-specific licenses, bind to first device
        $license->markAsActivated($isLifetimeLicense ? null : $deviceId, $isLifetimeLicense);

        Log::info('License activated successfully', [
            'license_id' => $license->id,
            'app_version' => $appVersion,
            'device_id' => $deviceId,
            'is_lifetime' => $isLifetimeLicense,
        ]);

        // Return the license data - the app will store this locally
        // The original signed key is already verifiable by the app
        return [
            'success' => true,
            'license' => $this->formatLicenseResponse($license, $appVersion),
        ];
    }

    /**
     * @return array{
     *   id: string,
     *   key: ?string,
     *   order_id: string,
     *   status: string,
     *   activated_at: int,
     *   max_major_version: int,
     *   product: string,
     *   app_version: string,
     *   payload: ?string,
     *   signature: ?string
     * }
     */
    private function formatLicenseResponse(License $license, string $appVersion): array
    {
        return [
            'id' => $license->id,
            'key' => $license->key_plain,
            'order_id' => $license->order_id,
            'status' => $license->status->value,
            'activated_at' => $license->activated_at?->timestamp ?? now()->timestamp,
            'max_major_version' => (int) ($license->max_major_version ?? 0),
            'product' => 'honeymelon',
            'app_version' => $appVersion,
            // Include the signed payload and signature for offline verification
            'payload' => $license->meta['payload'] ?? null,
            'signature' => $license->meta['signature'] ?? null,
        ];
    }
}
