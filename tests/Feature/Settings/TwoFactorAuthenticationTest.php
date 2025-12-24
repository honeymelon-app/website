<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_settings_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('two-factor.show'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('settings/TwoFactor')
                    ->has('qrCode')
                    ->has('setupKey')
                    ->has('recoveryCodes')
                    ->has('requiresConfirmation')
            );
    }

    public function test_two_factor_page_shows_qr_code_when_enabled_but_not_confirmed(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('TESTSECRET'),
            'two_factor_confirmed_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('two-factor.show'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('settings/TwoFactor')
                    ->whereNot('qrCode', null)
                    ->whereNot('setupKey', null)
                    ->where('recoveryCodes', null)
            );
    }

    public function test_two_factor_page_shows_recovery_codes_when_requested(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('TESTSECRET'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('two-factor.show', ['showRecoveryCodes' => true]))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('settings/TwoFactor')
                    ->whereNot('recoveryCodes', null)
            );
    }
}
