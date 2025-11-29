<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReleaseResource;
use App\Models\Release;
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
}
