<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\ReleaseChannel;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArtifactResource;
use App\Models\Artifact;
use Inertia\Inertia;
use Inertia\Response;

class DownloadController extends Controller
{
    /**
     * Redirect to the home page with the download section in focus.
     * Passes the latest darwin-aarch64 artifact for the download section.
     */
    public function __invoke(): Response
    {
        $latestArtifact = Artifact::query()
            ->with('release')
            ->whereHas('release', function ($query) {
                $query->where('channel', ReleaseChannel::STABLE)
                    ->whereNotNull('published_at');
            })
            ->where('platform', 'darwin-aarch64')
            ->latest('created_at')
            ->first();

        return Inertia::render('Welcome', [
            'artifact' => $latestArtifact ? (new ArtifactResource($latestArtifact))->resolve() : null,
        ])->withViewData(['scrollTo' => 'download']);
    }
}
