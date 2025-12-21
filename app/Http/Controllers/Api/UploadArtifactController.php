<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Constants\DateRanges;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadArtifactRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadArtifactController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UploadArtifactRequest $request): JsonResponse
    {
        $data = $request->validated();
        $artifactFile = $request->file('artifact');
        if ($artifactFile === null) {
            abort(422, 'Artifact file is required');
        }

        $platform = $data['platform'];
        $disk = Storage::disk('private');

        $originalFilename = $data['filename'] ?? $artifactFile->getClientOriginalName() ?? 'artifact.bin';
        $sanitizedFilename = $this->sanitizeFilename($originalFilename);
        $storedFilename = sprintf(
            '%s-%s-%s',
            now()->format('YmdHis'),
            Str::lower(Str::random(6)),
            $sanitizedFilename
        );

        $directory = sprintf('releases/%s', $platform);
        $path = $disk->putFileAs($directory, $artifactFile, $storedFilename);
        $realPath = $artifactFile->getRealPath();
        $computedHash = $realPath ? hash_file('sha256', $realPath) : null;
        $sha256 = $data['sha256'] ?? $computedHash;
        $size = $artifactFile->getSize() ?? 0;
        $signature = $data['signature'] ?? null;

        if ($signature === null) {
            Log::warning('Uploaded artifact missing signature', ['platform' => $platform]);
        }

        $responseData = [
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

        return response()->json([
            'message' => 'Artifact uploaded successfully',
            'data' => $responseData,
        ], 201);
    }

    /**
     * Build an accessible object URL for the stored artifact.
     */
    protected function buildObjectUrl(string $path): string
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
    protected function temporaryUrl(string $path): ?string
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
    protected function sanitizeFilename(string $filename): string
    {
        $clean = preg_replace('/[^\w\-.]+/u', '-', trim($filename));

        return $clean !== '' ? $clean : 'artifact.bin';
    }
}
