<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(): Response
    {
        return Inertia::render('settings/TwoFactor', [
            'message' => 'Two-factor authentication is managed through Cerberus IAM.',
        ]);
    }
}
