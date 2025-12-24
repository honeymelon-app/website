<?php

use App\Http\Controllers\Web\Settings\PasswordController;
use App\Http\Controllers\Web\Settings\ProductController;
use App\Http\Controllers\Web\Settings\ProfileController;
use App\Http\Controllers\Web\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('user-password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');

    Route::get('settings/product', [ProductController::class, 'edit'])->name('product.edit');
    Route::put('settings/product', [ProductController::class, 'update'])->name('product.update');
    Route::post('settings/product/sync', [ProductController::class, 'sync'])->name('product.sync');
    Route::post('settings/product/preview', [ProductController::class, 'preview'])->name('product.preview');
});
