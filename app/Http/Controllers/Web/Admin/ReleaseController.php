<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReleaseResource;
use App\Models\Release;
use App\Services\GithubService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ReleaseController extends Controller
{
    /**
     * Display a listing of releases.
     */
    public function index(): Response
    {
        $releases = Release::query()
            ->with('user')
            ->latest('published_at')
            ->paginate(20);

        return Inertia::render('admin/releases/Index', [
            'releases' => [
                'data' => ReleaseResource::collection($releases->items())->resolve(),
                'meta' => [
                    'current_page' => $releases->currentPage(),
                    'from' => $releases->firstItem(),
                    'last_page' => $releases->lastPage(),
                    'per_page' => $releases->perPage(),
                    'to' => $releases->lastItem(),
                    'total' => $releases->total(),
                ],
                'links' => [
                    'first' => $releases->url(1),
                    'last' => $releases->url($releases->lastPage()),
                    'prev' => $releases->previousPageUrl(),
                    'next' => $releases->nextPageUrl(),
                ],
            ],
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
     * Also deletes the corresponding GitHub release and tag.
     */
    public function destroy(Release $release, GithubService $githubService): RedirectResponse
    {
        $tag = $release->tag;
        $version = $release->version;

        Log::info('Deleting release', [
            'release_id' => $release->id,
            'version' => $version,
            'tag' => $tag,
        ]);

        try {
            DB::transaction(function () use ($release, $githubService, $tag) {
                // Delete associated artifacts from database
                // Note: R2 files should be cleaned up separately or via a job
                $release->artifacts()->delete();

                // Delete the release from database
                $release->delete();

                // Delete from GitHub (release + tag)
                try {
                    $githubService->deleteReleaseAndTag($tag);
                    Log::info('GitHub release and tag deleted', ['tag' => $tag]);
                } catch (\Exception $e) {
                    // Log but don't fail - the local delete succeeded
                    Log::warning('Failed to delete GitHub release/tag', [
                        'tag' => $tag,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

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
