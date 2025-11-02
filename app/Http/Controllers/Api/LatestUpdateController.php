<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\UpdateManifestResponse;
use App\Models\Update;
use Illuminate\Support\Facades\Cache;

class LatestUpdateController extends Controller
{
    /**
     * Get the latest update manifest for a channel (raw Tauri format).
     */
    public function __invoke(string $channel): UpdateManifestResponse
    {
        $update = Cache::remember(
            "update:latest:{$channel}",
            300,
            fn () => Update::where('channel', $channel)
                ->where('is_latest', true)
                ->first()
        );

        if (! $update) {
            abort(404, 'No update available for this channel');
        }

        return UpdateManifestResponse::fromUpdate($update, 300);
    }
}
