<?php

declare(strict_types=1);

namespace App\Support;

class PlatformDetector
{
    /**
     * Detect platform from artifact filename.
     *
     * Returns platform identifier (darwin-aarch64, darwin-x86_64) or null if not detected.
     *
     * Examples:
     * - Honeymelon_1.0.0_aarch64.dmg -> darwin-aarch64
     * - Honeymelon_1.0.0_x64.dmg -> darwin-x86_64
     * - Honeymelon_1.0.0_aarch64.dmg.tar.gz -> darwin-aarch64
     * - Honeymelon_1.0.0_x64.dmg.tar.gz -> darwin-x86_64
     * - app_1.0.0_aarch64.dmg -> darwin-aarch64
     * - app_1.0.0_arm64.dmg -> darwin-aarch64
     * - app_1.0.0_intel.dmg -> darwin-x86_64
     * - app_1.0.0_x86_64.dmg -> darwin-x86_64
     * - signature.sig -> null (skipped)
     * - checksum.sha256 -> null (skipped)
     *
     * @param  string  $filename  The artifact filename to analyze
     * @return string|null Platform identifier or null if not detected/applicable
     */
    public static function detect(string $filename): ?string
    {
        // Skip signature and sha256 files
        if (str_ends_with($filename, '.sig') || str_ends_with($filename, '.sha256')) {
            return null;
        }

        // Match aarch64 or arm64 variants
        if (preg_match('/(aarch64|arm64)/i', $filename)) {
            return 'darwin-aarch64';
        }

        // Match x64, x86_64, or intel variants
        if (preg_match('/(x64|x86_64|intel)/i', $filename)) {
            return 'darwin-x86_64';
        }

        // If it's a dmg file without architecture info, assume Apple Silicon
        if (str_contains($filename, '.dmg')) {
            return 'darwin-aarch64';
        }

        return null;
    }
}
