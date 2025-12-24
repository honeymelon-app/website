<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class SeoController extends Controller
{
    /**
     * Generate robots.txt with environment-aware directives.
     */
    public function robots(): Response
    {
        $isProduction = app()->environment('production');
        $sitemapUrl = URL::to('/sitemap.xml');

        $content = $isProduction
            ? $this->productionRobots($sitemapUrl)
            : $this->stagingRobots();

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Generate XML sitemap for public pages.
     */
    public function sitemap(): Response
    {
        $content = Cache::remember('sitemap.xml', now()->addHours(24), function () {
            return $this->generateSitemap();
        });

        return response($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Production robots.txt - allows indexing.
     */
    private function productionRobots(string $sitemapUrl): string
    {
        return <<<ROBOTS
User-agent: *
Allow: /

# Disallow admin and auth routes
Disallow: /admin
Disallow: /login
Disallow: /forgot-password
Disallow: /reset-password
Disallow: /confirm-password
Disallow: /dashboard

# Sitemap
Sitemap: {$sitemapUrl}
ROBOTS;
    }

    /**
     * Staging robots.txt - prevents indexing.
     */
    private function stagingRobots(): string
    {
        return <<<'ROBOTS'
User-agent: *
Disallow: /

# This is a staging/development environment
# Indexing is disabled to prevent duplicate content issues
ROBOTS;
    }

    /**
     * Generate the sitemap XML content.
     */
    private function generateSitemap(): string
    {
        $baseUrl = config('app.url');
        $now = now()->toW3cString();

        $urls = [
            // Main landing page (highest priority)
            [
                'loc' => $baseUrl,
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            // Legal pages
            [
                'loc' => $baseUrl.'/privacy',
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ],
            [
                'loc' => $baseUrl.'/terms',
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc']).'</loc>'."\n";
            $xml .= '    <lastmod>'.$url['lastmod'].'</lastmod>'."\n";
            $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$url['priority'].'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
