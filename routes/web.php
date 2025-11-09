<?php

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

Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->middleware('cerberus.auth:web')->name('dashboard');

Route::prefix('admin')->middleware('cerberus.auth:web')->name('admin.')->group(function () {
    Route::resource('releases', \App\Http\Controllers\Web\Admin\ReleaseController::class)->only(['index', 'show']);
    Route::resource('artifacts', \App\Http\Controllers\Web\Admin\ArtifactController::class)->only(['index', 'show']);
    Route::resource('licenses', \App\Http\Controllers\Web\Admin\LicenseController::class)->only(['index', 'show', 'store']);
    Route::resource('orders', \App\Http\Controllers\Web\Admin\OrderController::class)->only(['index', 'show']);
    Route::resource('updates', \App\Http\Controllers\Web\Admin\UpdateController::class)->only(['index', 'show']);
    Route::delete('objects/{path}', [\App\Http\Controllers\Web\Admin\ObjectController::class, 'destroy'])->where('path', '.*')->name('objects.destroy');
    Route::resource('objects', \App\Http\Controllers\Web\Admin\ObjectController::class)->only(['index']);
});

require __DIR__.'/settings.php';
