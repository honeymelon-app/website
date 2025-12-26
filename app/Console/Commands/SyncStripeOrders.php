<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StripeSyncService;
use Illuminate\Console\Command;

class SyncStripeOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:sync-orders
                            {--limit=100 : Number of orders to fetch from Stripe}
                            {--starting-after= : Stripe charge ID to start after for pagination}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync order/transaction details from Stripe';

    /**
     * Execute the console command.
     */
    public function handle(StripeSyncService $syncService): int
    {
        $limit = (int) $this->option('limit');
        $startingAfter = $this->option('starting-after');

        $this->info('Syncing orders from Stripe...');

        if ($startingAfter) {
            $this->info("Starting after charge: {$startingAfter}");
        }

        $stats = $syncService->syncOrders($limit, $startingAfter);

        $this->table(
            ['Created', 'Synced', 'Skipped', 'Errors'],
            [[$stats['created'], $stats['synced'], $stats['skipped'], $stats['errors']]]
        );

        if ($stats['errors'] > 0) {
            $this->error('Some orders failed to sync. Check the logs for details.');

            return Command::FAILURE;
        }

        $this->info('Stripe orders sync completed successfully.');

        return Command::SUCCESS;
    }
}
