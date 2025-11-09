<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ArtifactController;
use App\Http\Controllers\Api\ArtifactDownloadController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\GithubWebhookController;
use App\Http\Controllers\Api\LatestUpdateController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\PublishReleaseController;
use App\Http\Controllers\Api\ReleaseController;
use App\Http\Controllers\Api\RollbackReleaseController;
use App\Http\Controllers\Api\UpdateByVersionController;
use App\Http\Controllers\Api\UpdateController;
use App\Http\Controllers\Api\UploadArtifactController;
use App\Http\Controllers\Api\WebhookEventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::prefix('updates')->group(function () {
    Route::get('/{channel}/latest.json', LatestUpdateController::class);
    Route::get('/{channel}/{version}.json', UpdateByVersionController::class);
});

Route::get('/download', ArtifactDownloadController::class);

// Checkout routes (public)
Route::post('/checkout', CheckoutController::class);

Route::prefix('webhooks')->group(function () {
    Route::post('/lemonsqueezy', [WebhookEventController::class, 'lemonsqueezy']);
    Route::post('/stripe', [WebhookEventController::class, 'stripe']);
    Route::post('/github/release', [GithubWebhookController::class, 'store']);
})->middleware('client');

Route::post('artifacts/upload', UploadArtifactController::class)->middleware('client');

// Resource API routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('artifacts', ArtifactController::class)->only(['index', 'show']);
    Route::apiResource('releases', ReleaseController::class)->only(['index', 'show']);
    Route::apiResource('updates', UpdateController::class)->only(['index', 'show']);
    Route::apiResource('licenses', LicenseController::class)->only(['index', 'show']);
});

// Admin API routes (protected)
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/releases/publish', PublishReleaseController::class);
    Route::post('/releases/rollback', RollbackReleaseController::class);

    Route::post('/licenses/revoke', function (Request $request) {
        // TODO: Implement license revocation endpoint
        return response()->json(['message' => 'Not implemented'], 501);
    });

    Route::get('/health', function () {
        $checks = [
            'database' => fn () => \DB::connection()->getPdo() !== null,
            'cache' => fn () => \Cache::store()->getStore() !== null,
        ];

        $results = [];
        $healthy = true;

        foreach ($checks as $name => $check) {
            try {
                $results[$name] = $check() ? 'ok' : 'failed';
            } catch (\Exception $e) {
                $results[$name] = 'failed';
                $healthy = false;
            }
        }

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $results,
        ], $healthy ? 200 : 503);
    });
});

// Authenticated user endpoint
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
