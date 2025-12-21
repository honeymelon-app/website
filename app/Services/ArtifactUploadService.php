<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\DateRanges;
use App\Contracts\ArtifactUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ArtifactUploadService implements ArtifactUploader
{
    /**
     * Upload an artifact to storage.
     *
     * @param  array{platform: string, filename?: string, sha256?: string, signature?: string, notarized?: bool}  $data
     * @return array{platform: string, source: string, filename: string, size: int, sha256: ?string, signature: ?string, notarized: bool, url: string, path: string, temporary_url: ?string}
     */
    public function upload(UploadedFile $file, array $data): array
    {
        $platform = $data['platform'];
        $disk = Storage::disk('private');

        $originalFilename = $data['filename'] ?? $file->getClientOriginalName() ?? 'artifact.bin';
        $sanitizedFilename = $this->sanitizeFilename($originalFilename);
        $storedFilename = sprintf(
            '%s-%s-%s',
            now()->format('YmdHis'),
            Str::lower(Str::random(6)),
            $sanitizedFilename
        );

        $directory = sprintf('releases/%s', $platform);
        $path = $disk->putFileAs($directory, $file, $storedFilename);
        $realPath = $file->getRealPath();
        $computedHash = $realPath ? hash_file('sha256', $realPath) : null;
        $sha256 = $data['sha256'] ?? $computedHash;
        $size = $file->getSize() ?? 0;
        $signature = $data['signature'] ?? null;

        if ($signature === null) {
            Log::warning('Uploaded artifact missing signature', ['platform' => $platform]);
        }

        return [
            'platform' => $platform,
            'source' => 'r2',
            'filename' => $sanitizedFilename,
            'size' => $size,
            'sha256' => $sha256,
            'signature' => $signature,
            'notarized' => (bool) ($data['notarized'] ?? false),
            'url' => $this->buildObjectUrl($path),
            'path' => $path,
            'temporary_url' => $this->temporaryUrl($path),
        ];
    }

    /**
     * Build an accessible object URL for the stored artifact.
     */
    private function buildObjectUrl(string $path): string
    {
        $config = config('filesystems.disks.private', []);
        $cleanPath = ltrim($path, '/');

        if (! empty($config['url'])) {
            return rtrim($config['url'], '/').'/'.$cleanPath;
        }

        if (! empty($config['endpoint']) && ! empty($config['bucket'])) {
            $endpoint = rtrim((string) $config['endpoint'], '/');
            $bucket = $config['bucket'];

            if (! empty($config['use_path_style_endpoint'])) {
                return "{$endpoint}/{$bucket}/{$cleanPath}";
            }

            $parsed = parse_url($endpoint);
            $scheme = $parsed['scheme'] ?? 'https';
            $host = $parsed['host'] ?? $endpoint;

            return "{$scheme}://{$bucket}.{$host}/{$cleanPath}";
        }

        return Storage::disk('private')->url($cleanPath);
    }

    /**
     * Attempt to generate a temporary signed URL when supported.
     */
    private function temporaryUrl(string $path): ?string
    {
        try {
            return Storage::disk('private')->temporaryUrl($path, now()->addMinutes(DateRanges::TEMPORARY_URL_MINUTES));
        } catch (\Throwable $e) {
            Log::debug('Failed to create temporary artifact URL', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sanitize filename to avoid unsafe characters.
     */
    private function sanitizeFilename(string $filename): string
    {
        $clean = preg_replace('/[^\w\-.]+/u', '-', trim($filename));

        return $clean !== '' && $clean !== null ? $clean : 'artifact.bin';
    }
}
