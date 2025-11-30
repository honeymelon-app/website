<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LicenseActivationController extends Controller
{
    public function __construct(
        protected ActivationService $activationService
    ) {}

    /**
     * Activate a license.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'license_key' => ['required', 'string', 'max:255'],
            'app_version' => ['required', 'string', 'max:50'],
            'device_id' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $result = $this->activationService->activate(
            licenseKey: $request->input('license_key'),
            appVersion: $request->input('app_version'),
            deviceId: $request->input('device_id')
        );

        if (! $result['success']) {
            $statusCode = match ($result['error_code'] ?? null) {
                ActivationService::ERROR_LICENSE_NOT_FOUND => Response::HTTP_NOT_FOUND,
                ActivationService::ERROR_LICENSE_NOT_ACTIVE => Response::HTTP_FORBIDDEN,
                ActivationService::ERROR_LICENSE_ALREADY_ACTIVATED => Response::HTTP_CONFLICT,
                default => Response::HTTP_BAD_REQUEST,
            };

            return response()->json([
                'success' => false,
                'error' => $result['error'],
                'error_code' => $result['error_code'] ?? 'unknown_error',
            ], $statusCode);
        }

        return response()->json([
            'success' => true,
            'license' => $result['license'],
        ]);
    }
}
