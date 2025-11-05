<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GeneratePersonalAccessTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:generate:pat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a personal access token for API authentication';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::findByEmail('tjthavarshan@gmail.com');

        $token = $user->createToken('Personal Access Token')->plainTextToken;

        $this->info('Personal Access Token generated successfully:');
        $this->line($token);
        $this->line('Make sure to save it somewhere safe, as you won\'t be able to see it again.');

        return static::SUCCESS;
    }
}
