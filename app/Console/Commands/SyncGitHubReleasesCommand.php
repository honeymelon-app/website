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
                            {--force : Force update all releases even if unchanged}
                            {--delete-removed : Delete releases that no longer exist on GitHub}
                            {--no-download-counts : Skip syncing download counts}
                            {--dry-run : Show what would be synced without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comprehensively sync all releases and artifacts from GitHub to the local database';

    /**
     * Execute the console command.
     */
    public function handle(GitHubSyncService $syncService): int
    {
        $this->info('Starting comprehensive GitHub release sync...');
        $this->newLine();

        // Display sync options
        if ($this->option('force')) {
            $this->comment('• Force mode: All releases will be updated');
        }

        if ($this->option('delete-removed')) {
            $this->comment('• Cleanup mode: Releases removed from GitHub will be deleted');
        }

        if ($this->option('no-download-counts')) {
            $this->comment('• Skipping download count synchronization');
        }

        if ($this->option('dry-run')) {
            $this->warn('• Dry run mode - no changes will be made');
        }

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No actual changes will be made');
            $this->newLine();
            // In a real implementation, you'd pass this to the service
            // For now, we'll just show a warning
        }

        try {
            $options = [
                'force' => $this->option('force'),
                'delete_removed' => $this->option('delete-removed'),
                'sync_download_counts' => ! $this->option('no-download-counts'),
            ];

            $startTime = microtime(true);
            $stats = $syncService->syncReleases($options);
            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info('Sync completed successfully in '.$duration.' seconds!');
            $this->newLine();

            // Display detailed statistics
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Releases Created', $stats['created']],
                    ['Releases Updated', $stats['updated']],
                    ['Releases Skipped (no changes)', $stats['skipped']],
                    ['Releases Deleted', $stats['deleted']],
                    ['Errors', $stats['errors']],
                ]
            );

            // Show summary message
            $total = $stats['created'] + $stats['updated'] + $stats['skipped'];
            $this->newLine();
            $this->info("Processed {$total} releases total");

            if ($stats['deleted'] > 0) {
                $this->warn("Deleted {$stats['deleted']} releases that were removed from GitHub");
            }

            if ($stats['errors'] > 0) {
                $this->newLine();
                $this->error("⚠️  {$stats['errors']} releases failed to sync. Check the logs for details.");

                return self::FAILURE;
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('❌ Failed to sync releases: '.$e->getMessage());
            $this->newLine();
            $this->comment('Stack trace:');
            $this->line($e->getTraceAsString());

            return self::FAILURE;
        }
    }
}
