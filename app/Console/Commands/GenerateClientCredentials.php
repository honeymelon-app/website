<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GenerateClientCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:generate:client {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate client credentials for API access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->option('name') ?? 'Default Client';

        $key = bin2hex(random_bytes(16));
        $secret = bin2hex(random_bytes(32));

        Client::create([
            'name' => $name,
            'key' => $key,
            'secret' => Hash::make($secret),
        ]);

        $this->info('Client credentials generated successfully:');
        $this->line("Key: {$key}");
        $this->line("Secret: {$secret}");
    }
}
