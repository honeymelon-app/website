<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RollbackReleaseRequest;
use App\Services\UpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RollbackReleaseController extends Controller
{
    public function __construct(private UpdateService $updateService) {}

    /**
     * Rollback to a previous release.
     */
    public function __invoke(RollbackReleaseRequest $request): JsonResponse
    {
        $version = $request->input('version');
        $channel = $request->input('channel');

        try {
            $update = $this->updateService->rollback($version, $channel);

            return response()->json([
                'message' => 'Release rolled back successfully',
                'update' => $update,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to rollback release', [
                'version' => $version,
                'channel' => $channel,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to rollback release',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
