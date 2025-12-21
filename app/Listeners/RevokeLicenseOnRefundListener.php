<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderRefunded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\WithoutRelations;

#[WithoutRelations]
#[DeleteWhenMissingModels]
final class RevokeLicenseOnRefundListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(OrderRefunded $event): void
    {
        $license = $event->order->license;

        if ($license && $license->isActive()) {
            $license->markAsRefunded();
        }
    }
}
