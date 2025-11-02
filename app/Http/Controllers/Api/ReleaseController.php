<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Filters\ReleaseFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReleaseCollection;
use App\Http\Resources\ReleaseResource;
use App\Models\Release;
use Illuminate\Http\Request;

class ReleaseController extends Controller
{
    /**
     * Display a listing of releases.
     */
    public function index(Request $request, ReleaseFilter $filter): ReleaseCollection
    {
        $releases = Release::query()
            ->filter($filter)
            ->paginate($request->input('per_page', 20));

        return new ReleaseCollection($releases);
    }

    /**
     * Display the specified release.
     */
    public function show(Release $release): ReleaseResource
    {
        return new ReleaseResource($release->load('artifacts', 'updates'));
    }
}
