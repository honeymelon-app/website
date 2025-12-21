<?php

use App\Enums\ReleaseChannel;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\ArtifactController;
use App\Http\Controllers\Web\Admin\LicenseController;
use App\Http\Controllers\Web\Admin\OrderController;
use App\Http\Controllers\Web\Admin\ReleaseController;
use App\Http\Controllers\Web\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Web\Auth\NewPasswordController;
use App\Http\Controllers\Web\Auth\PasswordResetLinkController;
use App\Http\Controllers\Web\DownloadController;
use App\Http\Resources\ArtifactResource;
use App\Http\Resources\ProductResource;
use App\Models\Artifact;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $latestArtifact = Artifact::query()
        ->with('release')
        ->whereHas('release', function ($query) {
            $query->where('channel', ReleaseChannel::STABLE)
                ->whereNotNull('published_at');
        })
        ->where('platform', 'darwin-aarch64')
        ->latest('created_at')
        ->first();

    $product = Product::query()
        ->where('is_active', true)
        ->first();

    return Inertia::render('Welcome', [
        'artifact' => $latestArtifact ? (new ArtifactResource($latestArtifact))->resolve() : null,
        'product' => $product ? (new ProductResource($product))->resolve() : null,
    ]);
})->name('home');

Route::get('/download', DownloadController::class)->name('download');

Route::get('/pricing', function () {
    return Inertia::render('Pricing');
})->name('pricing');

Route::get('/privacy', function () {
    return Inertia::render('Privacy');
})->name('privacy');

Route::get('/terms', function () {
    return Inertia::render('Terms');
})->name('terms');

/*
|--------------------------------------------------------------------------
| Admin Auth Routes (No public registration)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Redirect dashboard to admin dashboard (no customer dashboard - admin only platform)
    Route::get('dashboard', fn () => redirect()->route('admin.dashboard'))->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('releases', ReleaseController::class)->only(['index', 'show', 'destroy']);
        Route::resource('artifacts', ArtifactController::class)->only(['index', 'show', 'destroy']);
        Route::resource('licenses', LicenseController::class)->only(['index', 'show', 'store']);
        Route::post('licenses/{license}/revoke', [LicenseController::class, 'revoke'])->name('licenses.revoke');
        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::post('orders/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
    });
});

require __DIR__.'/settings.php';
