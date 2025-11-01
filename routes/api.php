<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ArtifactController;
use App\Http\Controllers\Api\ReleaseController;
use App\Http\Controllers\Api\UpdateController;
use App\Http\Controllers\Api\WebhookEventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::prefix('updates')->group(function () {
    Route::get('/{channel}/latest.json', [UpdateController::class, 'latest']);
    Route::get('/{channel}/{version}.json', [UpdateController::class, 'byVersion']);
});

Route::get('/download', [ArtifactController::class, 'download']);

Route::prefix('webhooks')->group(function () {
    Route::post('/lemonsqueezy', [WebhookEventController::class, 'lemonsqueezy']);
    Route::post('/stripe', [WebhookEventController::class, 'stripe']);
});

// Admin API routes (protected)
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/releases/publish', [ReleaseController::class, 'publish']);
    Route::post('/releases/rollback', [ReleaseController::class, 'rollback']);

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
