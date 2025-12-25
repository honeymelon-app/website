<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Release;
use App\Services\ReleaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ReconcileArtifactsFromStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Dry-run by default. Use --apply to write changes.
     *
     * @var string
     */
    protected $signature = 'artifacts:reconcile-storage
                            {--disk=r2 : Storage disk to scan (default: r2)}
                            {--prefix=releases/ : Prefix/folder to scan (default: releases/)}
                            {--apply : Create/update database records (otherwise dry-run)}
                            {--limit= : Max files to scan (for safety)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile Artifact database rows from objects already present in storage (R2/S3).';

    public function handle(ReleaseService $releaseService): int
    {
        $disk = (string) $this->option('disk');
        $prefix = (string) $this->option('prefix');
        $apply = (bool) $this->option('apply');
        $limit = $this->option('limit') !== null ? max(0, (int) $this->option('limit')) : null;

        $this->info('Reconciling artifacts from storage...');
        $this->line('Disk: '.$disk);
        $this->line('Prefix: '.$prefix);
        $this->line('Mode: '.($apply ? 'APPLY' : 'DRY RUN'));

        $storage = Storage::disk($disk);

        if (! $storage->directoryExists($prefix)) {
            $this->error('Prefix does not exist on disk: '.$prefix);

            return self::FAILURE;
        }

        $paths = $storage->allFiles($prefix);

        if ($limit !== null) {
            $paths = array_slice($paths, 0, $limit);
        }

        $stats = [
            'scanned' => 0,
            'skipped' => 0,
            'matched_release' => 0,
            'created' => 0,
            'updated' => 0,
            'no_release' => 0,
        ];

        foreach ($paths as $path) {
            $stats['scanned']++;

            if ($this->shouldSkipPath($path)) {
                $stats['skipped']++;

                continue;
            }

            $platform = $this->platformFromPath($path);
            if ($platform === null) {
                $stats['skipped']++;

                continue;
            }

            $filename = $this->originalFilenameFromPath($path);
            if ($filename === null) {
                $stats['skipped']++;

                continue;
            }

            $version = $this->extractVersionFromFilename($filename);
            if ($version === null) {
                $stats['no_release']++;

                continue;
            }

            $release = Release::query()->where('version', $version)->first()
                ?? Release::query()->where('tag', 'v'.$version)->first()
                ?? Release::query()->where('tag', $version)->first();

            if (! $release) {
                $stats['no_release']++;

                continue;
            }

            $stats['matched_release']++;

            $size = null;
            try {
                $size = $storage->size($path);
            } catch (\Throwable) {
                // Leave as null.
            }

            $existing = $release->artifacts()
                ->where('platform', $platform)
                ->where('filename', $filename)
                ->first();

            if (! $apply) {
                continue;
            }

            if ($existing) {
                $existing->fill([
                    'source' => 'r2',
                    'path' => $path,
                ]);

                if ($size !== null) {
                    $existing->size = $size;
                }

                if ($existing->isDirty()) {
                    $existing->save();
                    $stats['updated']++;
                }

                continue;
            }

            $releaseService->attachArtifact($release, [
                'platform' => $platform,
                'source' => 'r2',
                'filename' => $filename,
                'size' => $size ?? 0,
                'sha256' => null,
                'signature' => null,
                'notarized' => false,
                'url' => null,
                'path' => $path,
            ]);

            $stats['created']++;
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Scanned', $stats['scanned']],
                ['Skipped (non-artifacts)', $stats['skipped']],
                ['Matched Release', $stats['matched_release']],
                ['Created', $stats['created']],
                ['Updated', $stats['updated']],
                ['No Release Match', $stats['no_release']],
            ]
        );

        if (! $apply) {
            $this->newLine();
            $this->comment('Dry run: no database changes were made. Use --apply to write.');
        }

        return self::SUCCESS;
    }

    protected function shouldSkipPath(string $path): bool
    {
        $lower = strtolower($path);

        if (str_ends_with($lower, '.sig') || str_ends_with($lower, '.sha256')) {
            return true;
        }

        if (str_ends_with($lower, 'sha256sums.txt')) {
            return true;
        }

        return false;
    }

    protected function platformFromPath(string $path): ?string
    {
        // Expected: releases/{platform}/{storedFilename}
        $segments = explode('/', trim($path, '/'));

        if (count($segments) < 3) {
            return null;
        }

        if ($segments[0] !== 'releases') {
            return null;
        }

        return $segments[1] !== '' ? $segments[1] : null;
    }

    protected function originalFilenameFromPath(string $path): ?string
    {
        $basename = basename($path);

        // Stored format: {YmdHis}-{random6}-{originalFilename}
        $parts = explode('-', $basename);

        if (count($parts) < 3) {
            return null;
        }

        return implode('-', array_slice($parts, 2));
    }

    protected function extractVersionFromFilename(string $filename): ?string
    {
        // Match common semver: 1.2.3, optionally prefixed by v and optionally with prerelease/build.
        if (preg_match('/\bv?(\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?)\b/', $filename, $m) === 1) {
            return $m[1];
        }

        return null;
    }
}
