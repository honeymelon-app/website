<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Main database seeder for the application.
 *
 * By default, only seeds essential data (DefaultUserSeeder, ProductSeeder).
 * Use --class=DummyDataSeeder for development data.
 * Use --class=TestSetupSeeder for test fixtures.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DefaultUserSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
