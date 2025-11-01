<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Artifact;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DownloadService
{
    public function __construct(
        private readonly LicenseService $licenseService
    ) {}

    /**
     * Resolve the download URL for an artifact.
     *
     * @throws ModelNotFoundException
     * @throws AccessDeniedHttpException
     */
    public function resolveUrl(string $version, string $platform, ?string $licenseKey = null): string
    {
        Log::info('Resolving download URL', [
            'version' => $version,
            'platform' => $platform,
            'license_last4' => $licenseKey ? substr($licenseKey, -4) : null,
        ]);

        // Validate license if provided
        if ($licenseKey && ! $this->licenseService->isValid($licenseKey)) {
            Log::warning('Invalid license key for download', [
                'license_last4' => substr($licenseKey, -4),
            ]);
            throw new AccessDeniedHttpException('Invalid or expired license key');
        }

        // Find the artifact
        $artifact = Artifact::whereHas('release', function ($query) use ($version) {
            $query->where('version', $version);
        })
            ->where('platform', $platform)
            ->firstOrFail();

        Log::info('Artifact found', [
            'artifact_id' => $artifact->id,
            'url' => $artifact->url,
        ]);

        // For GitHub source, return the URL directly
        if ($artifact->source === 'github') {
            return $artifact->url;
        }

        // For R2/S3, we would generate a signed URL here
        // For now, just return the URL (implement signed URLs later)
        return $artifact->url ?? $artifact->path ?? '';
    }
}
