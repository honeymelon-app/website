<?php

declare(strict_types=1);

namespace App\Support;

final class Semver
{
    /**
     * Extract the major version from a semver-like string.
     *
     * Accepts values like "1", "1.2", "1.2.3", "v1.2.3".
     */
    public static function major(string $version): ?int
    {
        $value = trim($version);
        if ($value === '') {
            return null;
        }

        if (! preg_match('/^v?(\d+)/i', $value, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }
}
