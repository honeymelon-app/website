<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Artifact;
use App\Models\Release;
use Inertia\Inertia;
use Inertia\Response;

class DownloadController extends Controller
{
    /**
     * Display the download page with the latest artifacts.
     */
    public function index(): Response
    {
        $latestRelease = Release::query()
            ->with('artifacts')
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->first();

        $artifacts = $latestRelease?->artifacts->map(function (Artifact $artifact) {
            return [
                'id' => $artifact->id,
                'platform' => $artifact->platform,
                'filename' => $artifact->filename,
                'size' => $artifact->size,
                'sha256' => $artifact->sha256,
                'signature' => $artifact->signature,
                'notarized' => $artifact->notarized,
                'url' => $artifact->url,
                'created_at' => $artifact->created_at?->toIso8601String(),
            ];
        }) ?? collect();

        return Inertia::render('Download', [
            'release' => $latestRelease ? [
                'id' => $latestRelease->id,
                'version' => $latestRelease->version,
                'tag' => $latestRelease->tag,
                'channel' => $latestRelease->channel->value,
                'notes' => $latestRelease->notes,
                'published_at' => $latestRelease->published_at?->toIso8601String(),
            ] : null,
            'artifacts' => $artifacts,
        ]);
    }
}
