<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds deterministic data for test environments.
 *
 * This seeder creates predictable, known data that tests can rely on.
 * All IDs and values are fixed to ensure test reproducibility.
 */
class TestSetupSeeder extends Seeder
{
    public const TEST_USER_EMAIL = 'test@honeymelon.app';

    public const TEST_USER_PASSWORD = 'password';

    public const TEST_PRODUCT_NAME = 'Honeymelon Test';

    public const TEST_PRODUCT_SLUG = 'honeymelon-test';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedTestUser();
        $this->seedTestProduct();
    }

    /**
     * Create a deterministic test user.
     */
    private function seedTestUser(): User
    {
        return User::firstOrCreate(
            ['email' => self::TEST_USER_EMAIL],
            [
                'name' => 'Test User',
                'password' => Hash::make(self::TEST_USER_PASSWORD),
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * Create a deterministic test product.
     */
    private function seedTestProduct(): Product
    {
        return Product::firstOrCreate(
            ['slug' => self::TEST_PRODUCT_SLUG],
            [
                'name' => self::TEST_PRODUCT_NAME,
                'description' => 'Test product for automated testing',
                'price_cents' => 9900, // $99.00 in cents
                'currency' => 'usd',
                'is_active' => true,
            ]
        );
    }
}
