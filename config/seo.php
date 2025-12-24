<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Name
    |--------------------------------------------------------------------------
    |
    | The name of your site, used in title templates and structured data.
    |
    */

    'site_name' => 'Honeymelon',

    /*
    |--------------------------------------------------------------------------
    | Title Template
    |--------------------------------------------------------------------------
    |
    | Template for page titles. Use %s as placeholder for the page title.
    |
    */

    'title_template' => '%s | Honeymelon',

    /*
    |--------------------------------------------------------------------------
    | Default Description
    |--------------------------------------------------------------------------
    |
    | The default meta description used when pages don't specify their own.
    | Must be truthful about the free download + paid license model.
    |
    */

    'default_description' => 'Honeymelon is a native macOS media converter for Apple Silicon. Free download, paid license required. Convert video, audio, and images with remux-first intelligence. No subscriptions, files stay local.',

    /*
    |--------------------------------------------------------------------------
    | Default OG Image
    |--------------------------------------------------------------------------
    |
    | Path to the default OpenGraph image (relative to public directory).
    | Recommended size: 1200x630 pixels.
    |
    */

    'default_og_image' => '/images/og-image.png',

    /*
    |--------------------------------------------------------------------------
    | Twitter Handle
    |--------------------------------------------------------------------------
    |
    | Your Twitter/X handle for Twitter Cards (without @).
    |
    */

    'twitter_handle' => env('SEO_TWITTER_HANDLE', ''),

    /*
    |--------------------------------------------------------------------------
    | Robots
    |--------------------------------------------------------------------------
    |
    | Default robots directive. Set to 'noindex, nofollow' for staging.
    |
    */

    'robots' => env('SEO_ROBOTS', 'index, follow'),

    /*
    |--------------------------------------------------------------------------
    | Search Console Verification
    |--------------------------------------------------------------------------
    |
    | Google Search Console verification meta tag content.
    |
    */

    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION', ''),

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    |
    | Analytics configuration for optional measurement.
    |
    */

    'analytics' => [
        'plausible_domain' => env('PLAUSIBLE_DOMAIN', ''),
        'ga4_id' => env('GA4_MEASUREMENT_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Information (for structured data)
    |--------------------------------------------------------------------------
    |
    | Static product information for JSON-LD structured data.
    |
    */

    'product' => [
        'name' => 'Honeymelon',
        'operating_system' => 'macOS',
        'application_category' => 'MultimediaApplication',
        'description' => 'Native macOS media converter for Apple Silicon. Free download, paid license required to use. Convert video, audio, and images offline with remux-first intelligence.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization (for structured data)
    |--------------------------------------------------------------------------
    |
    | Organization information for JSON-LD.
    |
    */

    'organization' => [
        'name' => 'Honeymelon',
        'logo' => '/images/logo.png',
    ],

];
