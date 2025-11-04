<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateLicenseKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:generate-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an Ed25519 keypair for license signing';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (! function_exists('sodium_crypto_sign_keypair')) {
            $this->error('The sodium extension is required to generate license keys.');

            return;
        }

        $keypair = sodium_crypto_sign_keypair();
        $public = base64_encode(sodium_crypto_sign_publickey($keypair));
        $secret = base64_encode($keypair);

        $this->info('Store these values in your environment (never commit them):');
        $this->newLine();
        $this->line("LICENSE_SIGNING_PUBLIC_KEY={$public}");
        $this->line("LICENSE_SIGNING_PRIVATE_KEY={$secret}");
    }
}
