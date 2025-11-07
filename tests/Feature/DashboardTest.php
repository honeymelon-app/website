<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_cerberus()
    {
        config()->set('cerberus-iam.base_url', 'https://cerberus.example');
        config()->set('cerberus-iam.oauth.client_id', 'client-id');
        config()->set('cerberus-iam.oauth.redirect_uri', 'https://app.test/callback');

        $response = $this->get(route('dashboard'));

        $response->assertRedirect();
        $this->assertStringContainsString(
            'https://cerberus.example/oauth2/authorize',
            $response->headers->get('Location')
        );
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
    }
}
