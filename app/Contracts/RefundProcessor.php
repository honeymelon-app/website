<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Order;
use App\Services\RefundService;
use Illuminate\Container\Attributes\Bind;
use RuntimeException;

#[Bind(RefundService::class)]
interface RefundProcessor
{
    /**
     * Process a refund for an order.
     *
     * @throws RuntimeException
     */
    public function refund(Order $order, ?string $reason = null): Order;
}
