<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the default admin user from environment variables.
 *
 * This seeder is idempotent and safe to run multiple times.
 * Uses config/env values for email and password.
 */
class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('app.admin_email', 'admin@honeymelon.app');
        $password = config('app.admin_password', 'changeme');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Default admin user created/verified: {$email}");
    }
}
