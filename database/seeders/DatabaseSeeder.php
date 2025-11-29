<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Jerome Thayananthajothy',
            'email' => 'tjthavarshan@gmail.com',
            'password' => Hash::make('Matrix09!'),
        ]);

        $this->call([
            ProductSeeder::class,
        ]);
    }
}
