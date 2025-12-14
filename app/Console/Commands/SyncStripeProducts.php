<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StripeSyncService;
use Illuminate\Console\Command;

class SyncStripeProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:sync {--product= : Sync a specific product by slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product and price details from Stripe';

    /**
     * Execute the console command.
     */
    public function handle(StripeSyncService $syncService): int
    {
        $this->info('Syncing products from Stripe...');

        $stats = $syncService->syncProducts();

        $this->table(
            ['Synced', 'Skipped', 'Errors'],
            [[$stats['synced'], $stats['skipped'], $stats['errors']]]
        );

        if ($stats['errors'] > 0) {
            $this->error('Some products failed to sync. Check the logs for details.');

            return Command::FAILURE;
        }

        $this->info('Stripe sync completed successfully.');

        return Command::SUCCESS;
    }
}
