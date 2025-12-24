# SEO Implementation Guide

This document explains how to configure and extend the SEO implementation for the Honeymelon marketing site.

## Server-Side Rendering (SSR)

SSR is enabled for this application, which provides significant SEO benefits:

- **Search engines receive fully rendered HTML** instead of an empty shell
- **Faster perceived load time** for users (content visible before JS hydrates)
- **Better Core Web Vitals** (LCP, FID scores)

### SSR Commands

```bash
# Build both client and SSR bundles
npm run build:ssr

# Start SSR server (production)
php artisan inertia:start-ssr

# Check if SSR server is running
php artisan inertia:check-ssr

# Stop SSR server (for redeployment)
php artisan inertia:stop-ssr
```

### SSR Configuration

SSR settings are in `config/inertia.php`:

```php
'ssr' => [
    'enabled' => true,
    'url' => 'http://127.0.0.1:13714',
],
```

### Development with SSR

For local development, you can run SSR alongside Vite:

```bash
# Terminal 1: Start Vite dev server
npm run dev

# Terminal 2: Start SSR server
php artisan inertia:start-ssr
```

## Configuration

All SEO settings are centralized in `config/seo.php`:

```php
return [
    'site_name' => 'Honeymelon',
    'title_template' => ':title | Honeymelon',
    'default_description' => 'Convert videos...',
    'default_og_image' => '/images/og-image.png',
    'twitter_handle' => '@honeymelonapp',
    // ...
];
```

### Environment Variables

Add these to your `.env` file as needed:

```env
# Google Search Console verification (optional)
GOOGLE_SEARCH_CONSOLE_VERIFICATION=your-verification-code

# Analytics - Plausible (recommended, privacy-friendly)
PLAUSIBLE_DOMAIN=honeymelon.app

# Analytics - Google Analytics 4 (optional)
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
```

## Adding SEO to a New Page

### 1. Import the Composable

```vue
<script setup lang="ts">
import { useSeoMeta } from '@/composables'
import { usePage, Head } from '@inertiajs/vue3'

const page = usePage()

const { headTags, jsonLdScript } = useSeoMeta({
  title: 'Page Title',
  description: 'Page description for search results.',
  canonical: `${page.props.appUrl}/your-page`,
  ogType: 'website',
})
</script>

<template>
  <Head>
    <title>{{ headTags.title }}</title>
    <meta name="description" :content="headTags.description" />
    <link rel="canonical" :href="headTags.canonical" />

    <!-- Open Graph -->
    <meta property="og:title" :content="headTags.ogTitle" />
    <meta property="og:description" :content="headTags.ogDescription" />
    <meta property="og:url" :content="headTags.canonical" />
    <meta property="og:type" :content="headTags.ogType" />
    <meta property="og:image" :content="headTags.ogImage" />
    <meta property="og:site_name" :content="headTags.siteName" />

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" :content="headTags.ogTitle" />
    <meta name="twitter:description" :content="headTags.ogDescription" />
    <meta name="twitter:image" :content="headTags.ogImage" />
  </Head>

  <!-- Page content -->
</template>
```

### 2. Add JSON-LD Structured Data (Optional)

For pages that benefit from rich results:

```vue
<script setup lang="ts">
import {
  useSeoMeta,
  generateSoftwareApplicationSchema,
  generateFaqSchema
} from '@/composables'

// For FAQ pages
const faqSchema = generateFaqSchema([
  { question: 'How does it work?', answer: 'It converts videos...' },
])

// For product pages
const productSchema = generateSoftwareApplicationSchema({
  name: 'Honeymelon',
  description: 'Video converter for macOS',
  price: 29,
  priceCurrency: 'USD',
  operatingSystem: 'macOS',
  applicationCategory: 'MultimediaApplication',
})
</script>

<template>
  <Head>
    <!-- Other meta tags... -->
    <component :is="'script'" type="application/ld+json">
      {{ JSON.stringify(faqSchema) }}
    </component>
  </Head>
</template>
```

## Sitemap

The sitemap is generated dynamically at `/sitemap.xml` and cached for 24 hours.

### Adding Pages to the Sitemap

Edit `app/Http/Controllers/SeoController.php`:

```php
$pages = [
    ['url' => config('app.url'), 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['url' => config('app.url') . '/privacy', 'priority' => '0.3', 'changefreq' => 'monthly'],
    ['url' => config('app.url') . '/terms', 'priority' => '0.3', 'changefreq' => 'monthly'],
    // Add new pages here:
    ['url' => config('app.url') . '/new-page', 'priority' => '0.8', 'changefreq' => 'weekly'],
];
```

### Clearing the Sitemap Cache

```bash
php artisan cache:forget sitemap
```

## robots.txt

The robots.txt is environment-aware:

- **Production**: Allows crawling, includes sitemap reference
- **Staging/Local**: Disallows all crawling

To customize, edit `app/Http/Controllers/SeoController.php`.

## Open Graph Image

Create an OG image at `public/images/og-image.png`:

- **Dimensions**: 1200 × 630 pixels
- **Format**: PNG or JPG
- **Content**: App name, tagline, visual preview

The image path is configured in `config/seo.php` under `default_og_image`.

## Validation Tools

### Test robots.txt
```bash
curl http://localhost:8000/robots.txt
```

### Test sitemap.xml
```bash
curl http://localhost:8000/sitemap.xml
```

### Validate Structured Data
- [Google Rich Results Test](https://search.google.com/test/rich-results)
- [Schema.org Validator](https://validator.schema.org/)

### Validate Open Graph
- [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
- [Twitter Card Validator](https://cards-dev.twitter.com/validator)

## Google Search Console Setup

1. Add property in [Google Search Console](https://search.google.com/search-console)
2. Choose "URL prefix" method
3. Select "HTML tag" verification
4. Copy the verification code (just the content value)
5. Add to `.env`:
   ```
   GOOGLE_SEARCH_CONSOLE_VERIFICATION=your-code-here
   ```
6. Submit sitemap: `https://yourdomain.com/sitemap.xml`

## Available Composable Options

```typescript
interface SeoMetaOptions {
  title: string           // Page title
  description: string     // Meta description (150-160 chars recommended)
  canonical?: string      // Canonical URL (absolute)
  ogType?: string         // Open Graph type: 'website', 'article', etc.
  ogImage?: string        // Custom OG image URL (optional)
  noindex?: boolean       // Add noindex directive (default: false)
}
```

## JSON-LD Schema Helpers

### generateSoftwareApplicationSchema
For the main product page. Includes pricing, OS, category.

### generateOrganizationSchema
For company/about pages. Includes name, URL, logo, social profiles.

### generateFaqSchema
For FAQ sections. Generates FAQPage schema for rich results.

## Tests

Run SEO-specific tests:

```bash
php artisan test tests/Feature/SeoRoutesTest.php
```

Tests cover:
- robots.txt response and environment behavior
- sitemap.xml validity and content
- Pricing redirect (301 to /#pricing)
- Marketing page responses
