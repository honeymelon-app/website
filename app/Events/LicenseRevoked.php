<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\License;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class LicenseRevoked
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly License $license,
        public readonly string $reason = 'manual'
    ) {}
}
