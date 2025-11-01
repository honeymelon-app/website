<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtifactCollection;
use App\Http\Resources\ArtifactResource;
use App\Models\Artifact;
use App\Services\DownloadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ArtifactController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloadService
    ) {}

    /**
     * Display a listing of artifacts.
     */
    public function index(Request $request): ArtifactCollection
    {
        $artifacts = Artifact::query()
            ->with('release')
            ->latest('created_at')
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

    /**
     * Handle artifact download with license validation.
     */
    public function download(Request $request): RedirectResponse
    {
        $license = $request->query('license');
        $version = $request->query('version');
        $platform = $request->query('platform', 'darwin-aarch64');

        if (! $version) {
            abort(400, 'Version parameter is required');
        }

        Log::info('Download request', [
            'version' => $version,
            'platform' => $platform,
            'license_last4' => $license ? substr($license, -4) : null,
        ]);

        try {
            $url = $this->downloadService->resolveUrl($version, $platform, $license);

            return redirect($url);
        } catch (AccessDeniedHttpException $e) {
            abort(403, $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Download failed', [
                'version' => $version,
                'platform' => $platform,
                'error' => $e->getMessage(),
            ]);

            abort(404, 'Artifact not found');
        }
    }
}
