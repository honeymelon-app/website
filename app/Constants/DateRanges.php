<?php

declare(strict_types=1);

namespace App\Constants;

/**
 * Common date range constants used throughout the application.
 */
final class DateRanges
{
    /**
     * Dashboard metrics comparison period (30 days).
     */
    public const DASHBOARD_COMPARISON_DAYS = 30;

    /**
     * Temporary URL expiration time (30 minutes).
     */
    public const TEMPORARY_URL_MINUTES = 30;

    /**
     * Cache TTL for license lookups (5 minutes).
     */
    public const LICENSE_CACHE_SECONDS = 300;

    /**
     * Cache TTL for update manifest (5 minutes).
     */
    public const UPDATE_MANIFEST_CACHE_SECONDS = 300;

    /**
     * Default pagination size for admin index pages.
     */
    public const ADMIN_PAGINATION_SIZE = 20;
}
