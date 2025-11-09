<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\ArtifactController;
use App\Http\Controllers\Web\Admin\LicenseController;
use App\Http\Controllers\Web\Admin\ObjectController;
use App\Http\Controllers\Web\Admin\OrderController;
use App\Http\Controllers\Web\Admin\ReleaseController;
use App\Http\Controllers\Web\Admin\UpdateController;
use App\Http\Controllers\Web\DownloadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => false,
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

Route::get('/login', function () {
    $guard = auth()->guard('web');

    if ($guard->check()) {
        return redirect()->intended(config('cerberus-iam.redirect_after_login', '/dashboard'));
    }

    return $guard->redirectToCerberus();
})->name('login');

Route::post('/logout', function () {
    auth()->guard('web')->logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware('cerberus.auth:web')->name('dashboard');

Route::prefix('admin')->middleware('cerberus.auth:web')->name('admin.')->group(function () {
    Route::resource('releases', ReleaseController::class)->only(['index', 'show']);
    Route::resource('artifacts', ArtifactController::class)->only(['index', 'show']);
    Route::resource('licenses', LicenseController::class)->only(['index', 'show', 'store']);
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::resource('updates', UpdateController::class)->only(['index', 'show']);
    Route::delete('objects/{path}', [ObjectController::class, 'destroy'])->where('path', '.*')->name('objects.destroy');
    Route::resource('objects', ObjectController::class)->only(['index']);
});

require __DIR__.'/settings.php';
