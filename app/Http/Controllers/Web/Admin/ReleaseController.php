<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReleaseCollection;
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

        return Inertia::render('Admin/Releases/Index', [
            'releases' => new ReleaseCollection($releases),
        ]);
    }

    /**
     * Display the specified release.
     */
    public function show(Release $release): Response
    {
        return Inertia::render('Admin/Releases/Show', [
            'release' => new ReleaseResource($release->load('artifacts', 'updates', 'user')),
        ]);
    }
}
