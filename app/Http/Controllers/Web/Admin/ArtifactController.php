<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtifactCollection;
use App\Http\Resources\ArtifactResource;
use App\Models\Artifact;
use Inertia\Inertia;
use Inertia\Response;

class ArtifactController extends Controller
{
    /**
     * Display a listing of artifacts.
     */
    public function index(): Response
    {
        $artifacts = Artifact::query()
            ->with('release')
            ->latest('created_at')
            ->paginate(20);

        return Inertia::render('admin/artifacts/Index', [
            'artifacts' => new ArtifactCollection($artifacts),
        ]);
    }

    /**
     * Display the specified artifact.
     */
    public function show(Artifact $artifact): Response
    {
        return Inertia::render('admin/artifacts/Show', [
            'artifact' => new ArtifactResource($artifact->load('release')),
        ]);
    }
}
