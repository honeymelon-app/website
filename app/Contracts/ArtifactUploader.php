<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Services\ArtifactUploadService;
use Illuminate\Container\Attributes\Bind;
use Illuminate\Http\UploadedFile;

#[Bind(ArtifactUploadService::class)]
interface ArtifactUploader
{
    /**
     * Upload an artifact to storage.
     *
     * @param  array{platform: string, filename?: string, sha256?: string, signature?: string, notarized?: bool}  $data
     * @return array{platform: string, source: string, filename: string, size: int, sha256: ?string, signature: ?string, notarized: bool, url: string, path: string, temporary_url: ?string}
     */
    public function upload(UploadedFile $file, array $data): array;
}
