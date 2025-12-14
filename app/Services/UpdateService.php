<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Update;
use Illuminate\Support\Facades\Log;

class UpdateService
{
    /**
     * Rollback to a previous release version.
     *
     * @return array{id: string, version: string, channel: string}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function rollback(string $version, string $channel): array
    {
        Log::info('Rolling back release', ['version' => $version, 'channel' => $channel]);

        $update = Update::where('channel', $channel)
            ->where('version', $version)
            ->firstOrFail();

        Update::where('channel', $channel)
            ->where('is_latest', true)
            ->update(['is_latest' => false]);

        $update->update(['is_latest' => true]);

        Log::info('Release rolled back', ['update_id' => $update->id]);

        return [
            'id' => $update->id,
            'version' => $update->version,
            'channel' => $update->channel,
        ];
    }
}
