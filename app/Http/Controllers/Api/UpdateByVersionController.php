<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\UpdateManifestResponse;
use App\Models\Update;
use Illuminate\Support\Facades\Cache;

class UpdateByVersionController extends Controller
{
    /**
     * Get a specific update manifest by channel and version (raw Tauri format).
     */
    public function __invoke(string $channel, string $version): UpdateManifestResponse
    {
        $update = Cache::remember(
            "update:{$channel}:{$version}",
            300,
            fn () => Update::where('channel', $channel)
                ->where('version', $version)
                ->first()
        );

        if (! $update) {
            abort(404, 'Update not found');
        }

        return UpdateManifestResponse::fromUpdate($update, 300);
    }
}
