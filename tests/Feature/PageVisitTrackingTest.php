<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PageVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageVisitTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_visit_is_tracked(): void
    {
        $this->assertDatabaseCount('page_visits', 0);

        $response = $this->get('/');

        $response->assertStatus(200);

        $this->assertDatabaseCount('page_visits', 1);
        $this->assertDatabaseHas('page_visits', [
            'path' => '/',
            'route_name' => 'home',
        ]);
    }

    public function test_pricing_redirects_to_homepage_anchor(): void
    {
        $response = $this->get('/pricing');

        $response->assertStatus(301);
        $response->assertRedirect('/#pricing');

        // Redirects don't track page visits
        $this->assertDatabaseMissing('page_visits', [
            'path' => 'pricing',
        ]);
    }

    public function test_download_page_visit_is_tracked(): void
    {
        $response = $this->get('/download');

        $response->assertStatus(200);

        $this->assertDatabaseHas('page_visits', [
            'path' => 'download',
            'route_name' => 'download',
        ]);
    }

    public function test_api_requests_are_not_tracked(): void
    {
        $response = $this->getJson('/');

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_post_requests_are_not_tracked(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_admin_routes_are_not_tracked(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_visit_records_device_type(): void
    {
        $this->get('/', [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
        ]);

        $visit = PageVisit::first();
        $this->assertEquals('mobile', $visit->device_type);
    }

    public function test_visit_records_desktop_device(): void
    {
        $this->get('/', [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ]);

        $visit = PageVisit::first();
        $this->assertEquals('desktop', $visit->device_type);
        $this->assertEquals('Chrome', $visit->browser);
        $this->assertEquals('macOS', $visit->platform);
    }

    public function test_visit_records_referrer(): void
    {
        $this->get('/', [
            'Referer' => 'https://google.com/search?q=honeymelon',
        ]);

        $visit = PageVisit::first();
        $this->assertEquals('https://google.com/search?q=honeymelon', $visit->referrer);
    }

    public function test_visit_records_session_id(): void
    {
        $this->get('/');

        $visit = PageVisit::first();
        $this->assertNotNull($visit->session_id);
    }
}
