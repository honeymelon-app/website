<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_settings_page_can_be_rendered()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('two-factor.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/TwoFactor')
                ->where('twoFactorEnabled', false)
            );
    }
}
