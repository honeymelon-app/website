<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ArtifactDownloadController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\GithubWebhookController;
use App\Http\Controllers\Api\LicenseActivationController;
use App\Http\Controllers\Api\UploadArtifactController;
use App\Http\Controllers\Api\WebhookEventController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
| No auth required.
*/
Route::get('/download', ArtifactDownloadController::class)
    ->middleware('throttle:downloads')
    ->name('artifacts.download');

Route::post('/checkout', CheckoutController::class)
    ->middleware('throttle:api')
    ->name('checkout');

Route::post('/licenses/activate', LicenseActivationController::class)
    ->middleware('throttle:api')
    ->name('licenses.activate');

/*
|--------------------------------------------------------------------------
| Webhooks (protected)
|--------------------------------------------------------------------------
| If these should be public, move them to the Public section
| and apply signature verification in their controllers.
*/
Route::prefix('webhooks')
    ->name('webhooks.')
    ->group(function () {
        Route::post('/stripe', [WebhookEventController::class, 'stripe'])->name('stripe');
        Route::post('/github/release', [GithubWebhookController::class, 'store'])
            ->middleware('client')
            ->name('github.release');
    });

/*
|--------------------------------------------------------------------------
| Resource APIs (protected)
|--------------------------------------------------------------------------
*/
Route::middleware('client')->group(function () {
    Route::post('artifacts/upload', UploadArtifactController::class)->name('artifacts.upload');
});
