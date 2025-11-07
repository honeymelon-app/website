<?php

use App\Http\Controllers\Web\Settings\PasswordController;
use App\Http\Controllers\Web\Settings\ProfileController;
use App\Http\Controllers\Web\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('cerberus.auth:web')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');
});
