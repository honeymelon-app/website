<?php

namespace App\Enums;

enum LicenseStatus: string
{
    case ACTIVE = 'active';
    case REFUNDED = 'refunded';
    case REVOKED = 'revoked';
    case EXPIRED = 'expired';

    /**
     * Check if the status allows activation.
     */
    public function allowsActivation(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::REFUNDED => 'Refunded',
            self::REVOKED => 'Revoked',
            self::EXPIRED => 'Expired',
        };
    }
}
