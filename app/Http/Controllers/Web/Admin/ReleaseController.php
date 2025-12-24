<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Filters\ReleaseFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReleaseResource;
use App\Models\Release;
use App\Services\GithubService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ReleaseController extends Controller
{
    private const DEFAULT_PAGE_SIZE = 15;

    private const ALLOWED_PAGE_SIZES = [10, 15, 25, 50, 100];

    private const SORTABLE_COLUMNS = [
        'version',
        'tag',
        'channel',
        'published_at',
        'created_at',
    ];

    /**
     * Display a listing of releases.
     */
    public function index(Request $request, ReleaseFilter $filter): Response
    {
        $pageSize = $this->getValidatedPageSize($request);
        $sortColumn = $this->getValidatedSortColumn($request);
        $sortDirection = $this->getValidatedSortDirection($request);

        $query = Release::query()
            ->with('user')
            ->withCount('artifacts')
            ->filter($filter);

        if ($sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->latest('published_at');
        }

        $releases = $query->paginate($pageSize)->withQueryString();

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
            'filters' => $request->only([
                'version',
                'tag',
                'channel',
                'major',
                'search',
            ]),
            'sorting' => [
                'column' => $sortColumn,
                'direction' => $sortDirection,
            ],
            'pagination' => [
                'pageSize' => $pageSize,
                'allowedPageSizes' => self::ALLOWED_PAGE_SIZES,
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
            return $this->handleWebException(
                $e,
                'admin.releases.show',
                'Failed to delete release',
                ['release_id' => $release->id],
                [$release]
            );
        }
    }

    private function getValidatedPageSize(Request $request): int
    {
        $pageSize = (int) $request->input('per_page', self::DEFAULT_PAGE_SIZE);

        return in_array($pageSize, self::ALLOWED_PAGE_SIZES, true)
            ? $pageSize
            : self::DEFAULT_PAGE_SIZE;
    }

    private function getValidatedSortColumn(Request $request): ?string
    {
        $column = $request->input('sort');

        return in_array($column, self::SORTABLE_COLUMNS, true) ? $column : null;
    }

    private function getValidatedSortDirection(Request $request): string
    {
        $direction = $request->input('direction', 'desc');

        return in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';
    }
}
