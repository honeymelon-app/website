<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadArtifactRequest;
use App\Services\ArtifactUploadService;
use Illuminate\Http\JsonResponse;

final class UploadArtifactController extends Controller
{
    public function __construct(private ArtifactUploadService $uploadService) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(UploadArtifactRequest $request): JsonResponse
    {
        $artifactFile = $request->file('artifact');

        if ($artifactFile === null) {
            abort(422, 'Artifact file is required');
        }

        $result = $this->uploadService->upload($artifactFile, $request->validated());

        return response()->json([
            'message' => 'Artifact uploaded successfully',
            'data' => $result,
        ], 201);
    }
}
