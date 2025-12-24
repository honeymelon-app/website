<?php

declare(strict_types=1);

/**
 * Application Helper Functions
 *
 * These helpers provide convenient access to configured Fetch HTTP clients.
 *
 * Example usage:
 *
 * // GitHub API
 * $releases = github_client()->get('/repos/owner/repo/releases')->json();
 *
 * // Stripe API
 * $customer = stripe_client()->get('/v1/customers/cus_123')->json();
 */

use Fetch\Interfaces\ClientHandler;

if (! function_exists('github_client')) {
    /**
     * Get a configured Fetch HTTP client for GitHub API.
     *
     * The client is pre-configured with:
     * - Base URI: https://api.github.com
     * - Authentication: Bearer token from config
     * - Timeout: 30 seconds
     * - Retries: 3 attempts with 100ms delay
     */
    function github_client(): ClientHandler
    {
        return app('http.github');
    }
}

if (! function_exists('stripe_client')) {
    /**
     * Get a configured Fetch HTTP client for Stripe API.
     *
     * The client is pre-configured with:
     * - Base URI: https://api.stripe.com
     * - Authentication: Bearer token from config
     * - Timeout: 30 seconds
     * - Retries: 3 attempts with 100ms delay
     */
    function stripe_client(): ClientHandler
    {
        return app('http.stripe');
    }
}
