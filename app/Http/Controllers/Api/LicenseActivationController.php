<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivateLicenseRequest;
use App\Http\Responses\ApiResponse;
use App\Services\ActivationService;
use Illuminate\Http\JsonResponse;

class LicenseActivationController extends Controller
{
    public function __construct(
        protected ActivationService $activationService
    ) {}

    /**
     * Activate a license.
     */
    public function __invoke(ActivateLicenseRequest $request): JsonResponse
    {
        $result = $this->activationService->activate(
            licenseKey: $request->validated('license_key'),
            appVersion: $request->validated('app_version'),
            deviceId: $request->input('device_id')
        );

        if (! $result['success']) {
            return match ($result['error_code'] ?? null) {
                ActivationService::ERROR_LICENSE_NOT_FOUND => ApiResponse::notFound(
                    $result['error'],
                    $result['error_code']
                ),
                ActivationService::ERROR_LICENSE_NOT_ACTIVE => ApiResponse::forbidden(
                    $result['error'],
                    $result['error_code']
                ),
                ActivationService::ERROR_LICENSE_VERSION_NOT_ALLOWED => ApiResponse::forbidden(
                    $result['error'],
                    $result['error_code']
                ),
                ActivationService::ERROR_LICENSE_ALREADY_ACTIVATED => ApiResponse::conflict(
                    $result['error'],
                    $result['error_code']
                ),
                ActivationService::ERROR_INVALID_APP_VERSION => ApiResponse::error(
                    $result['error'],
                    status: 422,
                    errorCode: $result['error_code']
                ),
                default => ApiResponse::error($result['error'], errorCode: $result['error_code'] ?? 'unknown_error'),
            };
        }

        // Return the license data directly (already formatted by ActivationService)
        return ApiResponse::success(
            $result['license'],
            dataKey: 'license'
        );
    }
}
