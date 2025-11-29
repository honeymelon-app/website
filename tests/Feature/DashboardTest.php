<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_are_redirected_to_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_authenticated_users_can_visit_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('admin/Index')
                ->has('metrics')
                ->has('metrics.total_orders')
                ->has('metrics.total_revenue')
                ->has('metrics.active_licenses')
                ->has('metrics.total_releases')
                ->has('recent_orders')
                ->has('recent_licenses')
                ->has('charts')
                ->has('charts.orders_over_time')
                ->has('charts.licenses_by_status')
                ->has('charts.artifacts_by_platform')
        );
    }
}
