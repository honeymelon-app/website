<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Filters\ArtifactFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArtifactCollection;
use App\Http\Resources\ArtifactResource;
use App\Models\Artifact;
use Illuminate\Http\Request;

final class ArtifactController extends Controller
{
    /**
     * Display a listing of artifacts.
     */
    public function index(Request $request, ArtifactFilter $filter): ArtifactCollection
    {
        $artifacts = Artifact::query()
            ->filter($filter)
            ->paginate($request->input('per_page', 20));

        return new ArtifactCollection($artifacts);
    }

    /**
     * Display the specified artifact.
     */
    public function show(Artifact $artifact): ArtifactResource
    {
        return new ArtifactResource($artifact->load('release'));
    }
}
