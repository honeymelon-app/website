<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@honeymelon.app'],
            [
                'name' => 'Admin',
                'email' => 'admin@honeymelon.app',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'changeme')),
                'email_verified_at' => now(),
            ]
        );
    }
}
