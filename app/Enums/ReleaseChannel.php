<?php

declare(strict_types=1);

namespace App\Enums;

enum ReleaseChannel: string
{
    case STABLE = 'stable';
    case BETA = 'beta';
    case ALPHA = 'alpha';
    case RC = 'rc';

    /**
     * Check if this channel is a prerelease channel.
     */
    public function isPrerelease(): bool
    {
        return $this !== self::STABLE;
    }

    /**
     * Get a human-readable label for the channel.
     */
    public function label(): string
    {
        return match ($this) {
            self::STABLE => 'Stable',
            self::BETA => 'Beta',
            self::ALPHA => 'Alpha',
            self::RC => 'Release Candidate',
        };
    }
}
