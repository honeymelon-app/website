<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\GitHubSyncService;
use Illuminate\Console\Command;

class SyncGitHubReleasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:sync-releases
                            {--dry-run : Show what would be synced without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync releases and artifacts from GitHub to the local database';

    /**
     * Execute the console command.
     */
    public function handle(GitHubSyncService $syncService): int
    {
        $this->info('Starting GitHub release sync...');

        if ($this->option('dry-run')) {
            $this->warn('Dry run mode - no changes will be made');
            $this->newLine();
        }

        try {
            $stats = $syncService->syncReleases();

            $this->newLine();
            $this->info('Sync completed successfully!');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Created', $stats['created']],
                    ['Updated', $stats['updated']],
                    ['Skipped', $stats['skipped']],
                    ['Errors', $stats['errors']],
                ]
            );

            if ($stats['errors'] > 0) {
                $this->warn('Some releases failed to sync. Check the logs for details.');

                return self::FAILURE;
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to sync releases: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
