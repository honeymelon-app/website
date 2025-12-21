<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LicenseIssued;
use App\Mail\LicenseKeyMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

final class SendLicenseEmailListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(LicenseIssued $event): void
    {
        $license = $event->license;
        $order = $license->order;

        if ($order?->email) {
            Mail::to($order->email)->queue(new LicenseKeyMail($license));
        }
    }
}
