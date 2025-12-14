<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReleaseCollection;
use App\Http\Resources\ReleaseResource;
use App\Models\Release;
use App\Services\ReleaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ReleaseController extends Controller
{
    public function __construct(private ReleaseService $releaseService) {}

    /**
     * Display a listing of releases.
     */
    public function index(): Response
    {
        $releases = Release::query()
            ->with('user')
            ->withCount('artifacts')
            ->latest('published_at')
            ->paginate(20);

        return Inertia::render('admin/releases/Index', [
            'releases' => new ReleaseCollection($releases),
        ]);
    }

    /**
     * Display the specified release.
     */
    public function show(Release $release): Response
    {
        return Inertia::render('admin/releases/Show', [
            'release' => (new ReleaseResource($release->load('artifacts', 'user')))->resolve(),
        ]);
    }

    /**
     * Remove the specified release from storage.
     */
    public function destroy(Release $release): RedirectResponse
    {
        $version = $release->version;

        try {
            $this->releaseService->deleteRelease($release);

            return redirect()
                ->route('admin.releases.index')
                ->with('success', "Release {$version} has been deleted successfully.");
        } catch (\Exception $e) {
            Log::error('Failed to delete release', [
                'release_id' => $release->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.releases.show', $release)
                ->with('error', 'Failed to delete release: '.$e->getMessage());
        }
    }
}
