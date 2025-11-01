<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UpdateCollection;
use App\Http\Resources\UpdateResource;
use App\Http\Responses\UpdateManifestResponse;
use App\Models\Update;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UpdateController extends Controller
{
    /**
     * Display a listing of updates.
     */
    public function index(Request $request): UpdateCollection
    {
        $updates = Update::query()
            ->latest('published_at')
            ->paginate($request->input('per_page', 20));

        return new UpdateCollection($updates);
    }

    /**
     * Display the specified update.
     */
    public function show(Update $update): UpdateResource
    {
        return new UpdateResource($update);
    }

    /**
     * Get the latest update manifest for a channel (raw Tauri format).
     */
    public function latest(string $channel): UpdateManifestResponse
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

    /**
     * Get a specific update manifest by channel and version (raw Tauri format).
     */
    public function byVersion(string $channel, string $version): UpdateManifestResponse
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
