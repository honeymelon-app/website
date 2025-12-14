<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtifactCollection;
use App\Http\Resources\ArtifactResource;
use App\Models\Artifact;
use App\Services\ArtifactStorageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ArtifactController extends Controller
{
    public function __construct(private ArtifactStorageService $storageService) {}

    /**
     * Display a listing of artifacts.
     */
    public function index(): Response
    {
        $artifacts = Artifact::query()
            ->with('release')
            ->latest('created_at')
            ->paginate(20)
            ->through(fn (Artifact $artifact) => $this->storageService->enrichArtifact($artifact));

        return Inertia::render('admin/artifacts/Index', [
            'artifacts' => new ArtifactCollection($artifacts),
        ]);
    }

    /**
     * Display the specified artifact.
     */
    public function show(Artifact $artifact): Response
    {
        $this->storageService->enrichArtifact($artifact);

        return Inertia::render('admin/artifacts/Show', [
            'artifact' => (new ArtifactResource($artifact->load('release')))->resolve(),
        ]);
    }

    /**
     * Delete the specified artifact.
     */
    public function destroy(Artifact $artifact): RedirectResponse
    {
        $artifact->delete();

        return redirect()->route('admin.artifacts.index')
            ->with('success', 'Artifact deleted successfully.');
    }
}
