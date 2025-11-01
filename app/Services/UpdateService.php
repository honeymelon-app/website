<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Release;
use App\Models\Update;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateService
{
    /**
     * Build and publish an update manifest for a release and channel.
     */
    public function buildAndPublish(Release $release, string $channel): Update
    {
        Log::info('Building and publishing update', [
            'release_id' => $release->id,
            'version' => $release->version,
            'channel' => $channel,
        ]);

        return DB::transaction(function () use ($release, $channel) {
            // Unset current latest for this channel
            Update::where('channel', $channel)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // Build manifest from release and artifacts
            $manifest = $this->buildManifest($release);

            // Create new update as latest
            $update = Update::create([
                'release_id' => $release->id,
                'channel' => $channel,
                'version' => $release->version,
                'manifest' => $manifest,
                'is_latest' => true,
                'published_at' => $release->published_at ?? now(),
            ]);

            Log::info('Update published', [
                'update_id' => $update->id,
                'version' => $update->version,
                'channel' => $channel,
            ]);

            return $update;
        });
    }

    /**
     * Build a Tauri updater manifest from a release.
     *
     * @return array{version: string, notes: string, pub_date: string, platforms: array<string, array{signature: string, url: string, sha256: string}>}
     */
    protected function buildManifest(Release $release): array
    {
        $platforms = [];

        foreach ($release->artifacts as $artifact) {
            $platforms[$artifact->platform] = [
                'signature' => $artifact->signature,
                'url' => $artifact->url,
                'sha256' => $artifact->sha256,
            ];
        }

        return [
            'version' => $release->version,
            'notes' => $release->notes ?? '',
            'pub_date' => ($release->published_at ?? now())->format('c'),
            'platforms' => $platforms,
        ];
    }
}
