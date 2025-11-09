<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ArtifactDownloadController;
use App\Http\Controllers\Api\GithubWebhookController;
use App\Http\Controllers\Api\UploadArtifactController;
use App\Http\Controllers\Api\WebhookEventController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
| No auth required.
*/
Route::get('/download', ArtifactDownloadController::class)->name('artifacts.download');

/*
|--------------------------------------------------------------------------
| Webhooks (protected)
|--------------------------------------------------------------------------
| If these should be public, move them to the Public section
| and apply signature verification in their controllers.
*/
Route::prefix('webhooks')
    ->middleware('client')
    ->name('webhooks.')
    ->group(function () {
        Route::post('/lemonsqueezy', [WebhookEventController::class, 'lemonsqueezy'])->name('lemonsqueezy');
        Route::post('/stripe', [WebhookEventController::class, 'stripe'])->name('stripe');
        Route::post('/github/release', [GithubWebhookController::class, 'store'])->name('github.release');
    });

/*
|--------------------------------------------------------------------------
| Resource APIs (protected)
|--------------------------------------------------------------------------
*/
Route::middleware('client')->group(function () {
    Route::post('artifacts/upload', UploadArtifactController::class)->name('artifacts.upload');
});
