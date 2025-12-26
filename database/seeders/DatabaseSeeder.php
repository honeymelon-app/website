<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            AdminUserSeeder::class,
            ProductSeeder::class,
            FaqSeeder::class,
        ];

        if (app()->isLocal()) {
            $seeders[] = DummyDataSeeder::class;
        }

        $this->call($seeders);
    }
}
