<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Password', [
            'managePasswordUrl' => config('cerberus-iam.management_urls.security'),
        ]);
    }
}
