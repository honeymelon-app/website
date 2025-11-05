<?php

use App\Http\Controllers\Web\DownloadController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
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

Route::get('dashboard', function () {
    return Inertia::render('admin/Index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('admin')->middleware(['auth', 'verified'])->name('admin.')->group(function () {
    Route::resource('releases', \App\Http\Controllers\Web\Admin\ReleaseController::class)->only(['index', 'show']);
    Route::resource('artifacts', \App\Http\Controllers\Web\Admin\ArtifactController::class)->only(['index', 'show']);
    Route::resource('licenses', \App\Http\Controllers\Web\Admin\LicenseController::class)->only(['index', 'show', 'store']);
    Route::resource('orders', \App\Http\Controllers\Web\Admin\OrderController::class)->only(['index', 'show']);
    Route::resource('updates', \App\Http\Controllers\Web\Admin\UpdateController::class)->only(['index', 'show']);
});

Route::get('/register', fn (): RedirectResponse => redirect()->route('login'))->name('register');
Route::post('/register', fn (): RedirectResponse => redirect()->route('login'));

require __DIR__.'/settings.php';
