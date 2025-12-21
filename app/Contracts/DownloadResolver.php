<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Artifact;
use App\Models\User;
use App\Services\DownloadService;
use Illuminate\Container\Attributes\Bind;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[Bind(DownloadService::class)]
interface DownloadResolver
{
    /**
     * Resolve the download URL for an artifact.
     *
     * @throws ModelNotFoundException
     * @throws AccessDeniedHttpException
     */
    public function resolveUrl(
        string $version,
        string $platform,
        ?string $licenseKey = null,
        ?User $user = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): string;

    /**
     * Get the latest stable artifact for a platform.
     */
    public function getLatestStable(string $platform = 'darwin-aarch64'): ?Artifact;
}
