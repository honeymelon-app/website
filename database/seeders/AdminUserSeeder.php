<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the admin user from configuration values.
 *
 * This seeder is idempotent and safe to run multiple times.
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user.
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
    }
}
