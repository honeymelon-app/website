<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Services\PaymentProviders\PaymentProviderFactory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RefundService
{
    public function __construct(
        private readonly PaymentProviderFactory $providerFactory
    ) {}

    /**
     * Process a refund for an order.
     *
     * @throws RuntimeException
     */
    public function refund(Order $order, ?string $reason = null): Order
    {
        if ($order->isRefunded()) {
            throw new RuntimeException('Order has already been refunded.');
        }

        if ($order->provider === 'manual') {
            throw new RuntimeException('Manual orders cannot be refunded through the system.');
        }

        $provider = $this->providerFactory->make($order->provider);

        return DB::transaction(function () use ($order, $provider, $reason) {
            // Process refund with payment provider
            $result = $provider->refund(
                paymentId: $order->external_id,
                amount: null, // Full refund
                reason: $reason
            );

            // Update order with refund info
            $order->update([
                'refund_id' => $result['refund_id'],
                'refunded_at' => now(),
                'meta' => array_merge($order->meta ?? [], [
                    'refund_reason' => $reason,
                    'refund_status' => $result['status'],
                    'refund_amount' => $result['amount'],
                ]),
            ]);

            // Revoke associated license - use REFUNDED status for refunds
            if ($order->license) {
                $order->license->markAsRefunded();
            }

            return $order->fresh(['license']);
        });
    }
}
