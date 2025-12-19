<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivateLicenseRequest;
use App\Services\ActivationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LicenseActivationController extends Controller
{
    public function __construct(
        private readonly ActivationService $activationService
    ) {}

    /**
     * Activate a license.
     */
    public function __invoke(ActivateLicenseRequest $request): JsonResponse
    {
        $result = $this->activationService->activate(
            licenseKey: $request->validated('license_key'),
            appVersion: $request->validated('app_version'),
            deviceId: $request->validated('device_id')
        );

        if (! $result['success']) {
            $errorCode = $result['error_code'] ?? null;
            $statusCode = $errorCode?->httpStatus() ?? Response::HTTP_BAD_REQUEST;

            return response()->json([
                'success' => false,
                'error' => $result['error'],
                'error_code' => $errorCode?->value ?? 'unknown_error',
            ], $statusCode);
        }

        return response()->json([
            'success' => true,
            'license' => $result['license'],
        ]);
    }
}
