<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('admin')->middleware(['auth', 'verified'])->name('admin.')->group(function () {
    Route::resource('releases', \App\Http\Controllers\Web\Admin\ReleaseController::class)->only(['index', 'show']);
    Route::resource('artifacts', \App\Http\Controllers\Web\Admin\ArtifactController::class)->only(['index', 'show']);
    Route::resource('licenses', \App\Http\Controllers\Web\Admin\LicenseController::class)->only(['index', 'show']);
    Route::resource('orders', \App\Http\Controllers\Web\Admin\OrderController::class)->only(['index', 'show']);
    Route::resource('updates', \App\Http\Controllers\Web\Admin\UpdateController::class)->only(['index', 'show']);
});

require __DIR__.'/settings.php';
