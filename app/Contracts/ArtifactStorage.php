<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Artifact;
use App\Services\ArtifactStorageService;
use Illuminate\Container\Attributes\Bind;

#[Bind(ArtifactStorageService::class)]
interface ArtifactStorage
{
    /**
     * Generate a download URL for an artifact.
     */
    public function generateDownloadUrl(Artifact $artifact): ?string;

    /**
     * Check the storage sync status for an artifact.
     *
     * @return array{synced: bool, type: string, message: string, storage_size?: int, size_match?: bool}
     */
    public function checkSyncStatus(Artifact $artifact): array;

    /**
     * Delete an artifact from storage.
     */
    public function delete(string $path): bool;

    /**
     * Check if a file exists in storage.
     */
    public function exists(string $path): bool;
}
