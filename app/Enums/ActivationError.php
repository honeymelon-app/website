<?php

declare(strict_types=1);

namespace App\Enums;

use Symfony\Component\HttpFoundation\Response;

enum ActivationError: string
{
    case LicenseNotFound = 'license_not_found';
    case LicenseNotActive = 'license_not_active';
    case LicenseAlreadyActivated = 'license_already_activated';

    /**
     * Get the HTTP status code for this error.
     */
    public function httpStatus(): int
    {
        return match ($this) {
            self::LicenseNotFound => Response::HTTP_NOT_FOUND,
            self::LicenseNotActive => Response::HTTP_FORBIDDEN,
            self::LicenseAlreadyActivated => Response::HTTP_CONFLICT,
        };
    }

    /**
     * Get a human-readable label for this error.
     */
    public function label(): string
    {
        return match ($this) {
            self::LicenseNotFound => 'License not found',
            self::LicenseNotActive => 'License not active',
            self::LicenseAlreadyActivated => 'License already activated',
        };
    }
}
