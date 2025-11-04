<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\LicenseService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class IssueLicenseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:issue {order_id? : Existing Order ID or reference} {--email=} {--provider=manual} {--external=} {--major=1} {--json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Issue a license for an order or create an ad-hoc license';

    /**
     * Execute the console command.
     */
    public function handle(LicenseService $licenses): void
    {
        $orderId = $this->argument('order_id');
        $order = null;

        if ($orderId) {
            $order = Order::query()
                ->where('id', $orderId)
                ->orWhere('reference', $orderId)
                ->first();

            if (! $order) {
                $this->error("Order [{$orderId}] not found. Provide --email to create an ad-hoc order.");

                return;
            }
        } else {
            $email = (string) $this->option('email');
            if ($email === '') {
                $this->error('An existing order ID or the --email option is required.');

                return;
            }

            $provider = (string) $this->option('provider') ?: 'manual';
            $external = (string) ($this->option('external') ?: 'manual-'.Str::uuid());

            $order = Order::create([
                'provider' => substr($provider, 0, 16),
                'external_id' => substr($external, 0, 64),
                'email' => $email,
                'meta' => array_filter([
                    'issued_via' => 'artisan',
                    'note' => 'manually issued license',
                ]),
            ]);

            if (! $this->option('json')) {
                $this->info("Created ad-hoc order [{$order->id}] for {$email}.");
            }
        }

        $maxMajor = (int) max(1, $this->option('major') ?? 1);

        $license = $licenses->issue([
            'order_id' => $order->getKey(),
            'max_major_version' => $maxMajor,
        ]);

        if ($this->option('json')) {
            $this->line(json_encode([
                'id' => $license->id,
                'key' => $license->key_plain,
                'max_major_version' => $license->max_major_version,
                'issued_at' => $license->meta['issued_at'] ?? null,
                'order_id' => $license->order_id,
            ], JSON_PRETTY_PRINT));

            return;
        }

        $this->info('License issued successfully.');
        $this->line(" ID:        {$license->id}");
        $this->line(" Key:       {$license->key_plain}");
        $this->line(' Max Major: '.($license->max_major_version ?? 1));
        $this->line(' Issued at: '.($license->meta['issued_at'] ?? 'unknown'));
        $this->line(' Order ID:  '.$order->getKey());
    }
}
