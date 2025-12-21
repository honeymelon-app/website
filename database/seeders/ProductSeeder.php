<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::updateOrCreate(
            ['slug' => 'honeymelon'],
            [
                'name' => 'Honeymelon',
                'description' => 'A powerful, privacy-first video converter for macOS. Convert videos between formats quickly and easily, all offline.',
                'stripe_product_id' => config('services.stripe.honeymelon_product_id'),
                'is_active' => true,
            ]
        );
    }
}
