<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\LicenseStatus;
use App\Enums\ReleaseChannel;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArtifactResource;
use App\Http\Resources\LicenseResource;
use App\Models\Artifact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    /**
     * Display the user dashboard with licenses and latest downloads.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $licenses = $user->licenses()
            ->with(['product', 'order'])
            ->where('status', LicenseStatus::ACTIVE)
            ->latest()
            ->get();

        // Get the latest stable artifact for download
        $latestArtifact = Artifact::query()
            ->with('release')
            ->whereHas('release', function ($query) {
                $query->where('channel', ReleaseChannel::STABLE)
                    ->whereNotNull('published_at');
            })
            ->where('platform', 'darwin-aarch64')
            ->latest('created_at')
            ->first();

        return Inertia::render('Dashboard', [
            'licenses' => LicenseResource::collection($licenses),
            'latestArtifact' => $latestArtifact ? (new ArtifactResource($latestArtifact))->resolve() : null,
            'hasActiveLicense' => $licenses->isNotEmpty(),
        ]);
    }
}
