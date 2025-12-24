<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_returns_text_response(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->assertSee('User-agent:');
    }

    public function test_robots_txt_includes_sitemap_in_production(): void
    {
        $this->app['env'] = 'production';

        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertSee('Sitemap:');
        $response->assertSee('Allow: /');
        $response->assertSee('Disallow: /admin');
    }

    public function test_robots_txt_disallows_all_in_staging(): void
    {
        $this->app['env'] = 'staging';

        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertSee('Disallow: /');
        $response->assertDontSee('Sitemap:');
    }

    public function test_sitemap_xml_returns_valid_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
    }

    public function test_sitemap_includes_main_pages(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('<loc>'.config('app.url').'</loc>', $content);
        $this->assertStringContainsString('/privacy</loc>', $content);
        $this->assertStringContainsString('/terms</loc>', $content);
    }

    public function test_sitemap_excludes_admin_routes(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertDontSee('/admin');
        $response->assertDontSee('/login');
    }

    public function test_pricing_redirects_to_homepage_anchor(): void
    {
        $response = $this->get('/pricing');

        $response->assertStatus(301);
        $response->assertRedirect('/#pricing');
    }

    public function test_homepage_returns_success(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Welcome'));
    }

    public function test_privacy_page_returns_success(): void
    {
        $response = $this->get('/privacy');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Privacy'));
    }

    public function test_terms_page_returns_success(): void
    {
        $response = $this->get('/terms');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Terms'));
    }
}
