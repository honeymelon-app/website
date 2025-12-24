<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        $showRecoveryCodes = $request->boolean('showRecoveryCodes');

        return Inertia::render('settings/TwoFactor', [
            'qrCode' => $user->two_factor_secret
                ? $user->twoFactorQrCodeSvg()
                : null,
            'setupKey' => $user->two_factor_secret
                ? decrypt($user->two_factor_secret)
                : null,
            'recoveryCodes' => $showRecoveryCodes && $user->two_factor_confirmed_at
                ? json_decode(decrypt($user->two_factor_recovery_codes), true)
                : null,
            'requiresConfirmation' => config('fortify.features.two-factor-authentication.confirm', false),
        ]);
    }
}
