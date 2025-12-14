<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Artifact;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class ArtifactStorageService
{
    private FilesystemAdapter $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('s3');
    }

    /**
     * Get the storage disk instance.
     */
    public function getDisk(): FilesystemAdapter
    {
        return $this->disk;
    }

    /**
     * Generate a download URL for an artifact.
     */
    public function generateDownloadUrl(Artifact $artifact): ?string
    {
        if ($artifact->source === 'github' && $artifact->url) {
            return $artifact->url;
        }

        if (! empty($artifact->path)) {
            try {
                if ($this->disk->exists($artifact->path)) {
                    return $this->disk->temporaryUrl($artifact->path, now()->addHour());
                }
            } catch (\Exception) {
                return $artifact->url;
            }
        }

        return $artifact->url;
    }

    /**
     * Check the storage sync status for an artifact.
     *
     * @return array{synced: bool, type: string, message: string, storage_size?: int, size_match?: bool}
     */
    public function checkSyncStatus(Artifact $artifact): array
    {
        if ($artifact->source === 'github') {
            return [
                'synced' => true,
                'type' => 'github',
                'message' => 'Hosted on GitHub',
            ];
        }

        if (empty($artifact->path)) {
            return [
                'synced' => false,
                'type' => 'missing_path',
                'message' => 'No storage path defined',
            ];
        }

        try {
            $exists = $this->disk->exists($artifact->path);

            if ($exists) {
                $size = $this->disk->size($artifact->path);

                return [
                    'synced' => true,
                    'type' => 'r2',
                    'message' => 'Synced to R2',
                    'storage_size' => $size,
                    'size_match' => $artifact->size === $size,
                ];
            }

            return [
                'synced' => false,
                'type' => 'not_found',
                'message' => 'File not found in R2',
            ];
        } catch (\Exception $e) {
            return [
                'synced' => false,
                'type' => 'error',
                'message' => 'Error checking R2: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Enrich an artifact with storage status and download URL.
     */
    public function enrichArtifact(Artifact $artifact): Artifact
    {
        $artifact->setAttribute('storage_status', $this->checkSyncStatus($artifact));
        $artifact->setAttribute('download_url', $this->generateDownloadUrl($artifact));

        return $artifact;
    }
}
