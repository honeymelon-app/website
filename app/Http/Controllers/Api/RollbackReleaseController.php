<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\HandlesControllerExceptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\RollbackReleaseRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class RollbackReleaseController extends Controller
{
    use HandlesControllerExceptions;

    /**
     * Rollback to a previous release.
     */
    public function __invoke(RollbackReleaseRequest $request): JsonResponse
    {
        $version = $request->input('version');
        $channel = $request->input('channel');

        Log::info('Rolling back release', ['version' => $version, 'channel' => $channel]);

        try {
            $update = \App\Models\Update::where('channel', $channel)
                ->where('version', $version)
                ->firstOrFail();

            // Unset current latest
            \App\Models\Update::where('channel', $channel)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // Set this version as latest
            $update->update(['is_latest' => true]);

            Log::info('Release rolled back', ['update_id' => $update->id]);

            return ApiResponse::success([
                'id' => $update->id,
                'version' => $update->version,
                'channel' => $update->channel,
            ], 'Release rolled back successfully');
        } catch (\Exception $e) {
            return $this->handleApiException(
                $e,
                'Failed to rollback release',
                ['version' => $version]
            );
        }
    }
}
