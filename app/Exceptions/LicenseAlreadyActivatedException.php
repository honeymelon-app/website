<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\License;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LicenseAlreadyActivatedException extends Exception
{
    public function __construct(
        string $message = 'License has already been activated.',
        public readonly ?License $license = null
    ) {
        parent::__construct($message, 409);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => $this->getMessage(),
            'code' => 'LICENSE_ALREADY_ACTIVATED',
        ], 409);
    }
}
