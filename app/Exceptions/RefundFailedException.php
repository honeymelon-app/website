<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RefundFailedException extends Exception
{
    public function __construct(
        string $message = 'Refund processing failed.',
        public readonly ?Order $order = null,
        public readonly ?string $reason = null
    ) {
        parent::__construct($message, 422);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => $this->getMessage(),
            'code' => 'REFUND_FAILED',
            'reason' => $this->reason,
        ], 422);
    }
}
