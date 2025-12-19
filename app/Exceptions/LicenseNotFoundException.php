<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LicenseNotFoundException extends Exception
{
    public function __construct(
        string $message = 'License not found.',
        public readonly ?string $licenseKey = null
    ) {
        parent::__construct($message, 404);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => $this->getMessage(),
            'code' => 'LICENSE_NOT_FOUND',
        ], 404);
    }
}
