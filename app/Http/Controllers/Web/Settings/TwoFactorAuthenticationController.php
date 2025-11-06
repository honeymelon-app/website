<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Show the user's two-factor authentication settings page.
     *
     * Note: Two-factor authentication is now managed by Cerberus IAM.
     * This controller is kept for compatibility but 2FA should be
     * configured through the Cerberus IAM dashboard.
     */
    public function show(TwoFactorAuthenticationRequest $request): Response
    {
        $request->ensureStateIsValid();

        return Inertia::render('settings/TwoFactor', [
            'twoFactorEnabled' => false,
            'requiresConfirmation' => false,
            'message' => 'Two-factor authentication is managed through Cerberus IAM.',
        ]);
    }
}
