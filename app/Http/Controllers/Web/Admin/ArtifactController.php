<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Filters\ArtifactFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArtifactResource;
use App\Models\Artifact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ArtifactController extends Controller
{
    private const DEFAULT_PAGE_SIZE = 15;

    private const ALLOWED_PAGE_SIZES = [10, 15, 25, 50, 100];

    private const SORTABLE_COLUMNS = [
        'filename',
        'platform',
        'source',
        'size',
        'created_at',
    ];

    /**
     * Display a listing of artifacts.
     */
    public function index(Request $request, ArtifactFilter $filter): Response
    {
        $pageSize = $this->getValidatedPageSize($request);
        $sortColumn = $this->getValidatedSortColumn($request);
        $sortDirection = $this->getValidatedSortDirection($request);

        $query = Artifact::query()
            ->with('release')
            ->filter($filter);

        if ($sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->latest('created_at');
        }

        $artifacts = $query->paginate($pageSize)->withQueryString();

        // Check R2 sync status for each artifact
        $disk = Storage::disk('s3');
        $artifactsWithSync = $artifacts->getCollection()->map(function ($artifact) use ($disk) {
            $syncStatus = $this->checkSyncStatus($artifact, $disk);
            $downloadUrl = $this->generateDownloadUrl($artifact, $disk);

            return array_merge(
                (new ArtifactResource($artifact))->resolve(),
                [
                    'storage_status' => $syncStatus,
                    'download_url' => $downloadUrl,
                ]
            );
        });

        return Inertia::render('admin/artifacts/Index', [
            'artifacts' => [
                'data' => $artifactsWithSync,
                'meta' => [
                    'current_page' => $artifacts->currentPage(),
                    'from' => $artifacts->firstItem(),
                    'last_page' => $artifacts->lastPage(),
                    'per_page' => $artifacts->perPage(),
                    'to' => $artifacts->lastItem(),
                    'total' => $artifacts->total(),
                ],
                'links' => [
                    'first' => $artifacts->url(1),
                    'last' => $artifacts->url($artifacts->lastPage()),
                    'prev' => $artifacts->previousPageUrl(),
                    'next' => $artifacts->nextPageUrl(),
                ],
            ],
            'filters' => $request->only([
                'platform',
                'source',
                'notarized',
                'release_id',
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
     * Display the specified artifact.
     */
    public function show(Artifact $artifact): Response
    {
        $disk = Storage::disk('s3');
        $syncStatus = $this->checkSyncStatus($artifact, $disk);
        $downloadUrl = $this->generateDownloadUrl($artifact, $disk);

        return Inertia::render('admin/artifacts/Show', [
            'artifact' => array_merge(
                (new ArtifactResource($artifact->load('release')))->resolve(),
                [
                    'storage_status' => $syncStatus,
                    'download_url' => $downloadUrl,
                ]
            ),
        ]);
    }

    /**
     * Delete the specified artifact.
     */
    public function destroy(Artifact $artifact): RedirectResponse
    {
        $artifact->delete(); // Observer will handle R2 deletion

        return redirect()->route('admin.artifacts.index')
            ->with('success', 'Artifact deleted successfully.');
    }

    /**
     * Generate a download URL for an artifact.
     */
    private function generateDownloadUrl(Artifact $artifact, $disk): ?string
    {
        // GitHub-sourced artifacts use the direct URL
        if ($artifact->source === 'github' && $artifact->url) {
            return $artifact->url;
        }

        // For R2/S3 sourced artifacts, generate a temporary signed URL
        if (! empty($artifact->path)) {
            try {
                if ($disk->exists($artifact->path)) {
                    return $disk->temporaryUrl($artifact->path, now()->addHour());
                }
            } catch (\Exception $e) {
                // Fall back to direct URL if signing fails
                return $artifact->url;
            }
        }

        return $artifact->url;
    }

    /**
     * Check the storage sync status for an artifact.
     */
    private function checkSyncStatus(Artifact $artifact, $disk): array
    {
        // GitHub-sourced artifacts don't need R2 sync check
        if ($artifact->source === 'github') {
            return [
                'synced' => true,
                'type' => 'github',
                'message' => 'Hosted on GitHub',
            ];
        }

        // For R2/S3 sourced artifacts, check if file exists
        if (empty($artifact->path)) {
            return [
                'synced' => false,
                'type' => 'missing_path',
                'message' => 'No storage path defined',
            ];
        }

        try {
            $exists = $disk->exists($artifact->path);

            if ($exists) {
                $size = $disk->size($artifact->path);

                return [
                    'synced' => true,
                    'type' => 'r2',
                    'message' => 'Synced to R2',
                    'storage_size' => $size,
                    'size_match' => $artifact->size === $size,
                ];
            }

            return [
                'synced' => false,
                'type' => 'not_found',
                'message' => 'File not found in R2',
            ];
        } catch (\Exception $e) {
            return [
                'synced' => false,
                'type' => 'error',
                'message' => 'Error checking R2: '.$e->getMessage(),
            ];
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
