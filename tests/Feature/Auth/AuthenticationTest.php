<?php
namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('cerberus-iam.base_url', 'https://cerberus.example');
        config()->set('cerberus-iam.oauth', [
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'redirect_uri' => 'https://app.test/callback',
            'scopes' => ['openid', 'profile', 'email'],
        ]);
    }

    public function test_login_route_redirects_to_cerberus_authorize_endpoint(): void
    {
        $response = $this->get(route('login'));

        $response->assertRedirect();

        $this->assertStringContainsString(
            'https://cerberus.example/oauth2/authorize',
            $response->headers->get('Location')
        );
    }

    public function test_logout_clears_session_and_redirects_home(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
