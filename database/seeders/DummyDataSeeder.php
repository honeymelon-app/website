<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ReleaseChannel;
use App\Models\Artifact;
use App\Models\Download;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\Release;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Database\Seeder;

/**
 * Seeds realistic dummy data for local development.
 *
 * This seeder creates varied, realistic data to test the UI and
 * application behavior. Safe to run multiple times (additive).
 */
class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCustomers();
        $this->seedReleases();
        $this->seedOrdersAndLicenses();
        $this->seedWebhookEvents();
        $this->seedDownloads();

        $this->command->info('Dummy data seeded successfully.');
    }

    /**
     * Seed customer users.
     */
    private function seedCustomers(): void
    {
        User::factory()
            ->count(10)
            ->create();

        $this->command->info('Created 10 customer users.');
    }

    /**
     * Seed releases with artifacts.
     */
    private function seedReleases(): void
    {
        $product = Product::first();

        if (! $product) {
            $this->command->warn('No product found. Run ProductSeeder first.');

            return;
        }

        // Stable releases (last 3 versions)
        $stableVersions = ['1.3.0', '1.4.0', '1.5.0'];
        foreach ($stableVersions as $index => $version) {
            $release = Release::factory()
                ->forProduct($product)
                ->forVersion($version, ReleaseChannel::STABLE->value)
                ->state([
                    'published_at' => now()->subDays(30 - ($index * 10)),
                    'major' => $index === 0,
                ])
                ->create();

            $this->seedArtifactsForRelease($release);
        }

        // Beta release
        $betaRelease = Release::factory()
            ->forProduct($product)
            ->forVersion('1.6.0-beta.1', ReleaseChannel::BETA->value)
            ->state(['published_at' => now()->subDays(2)])
            ->create();

        $this->seedArtifactsForRelease($betaRelease);

        $this->command->info('Created releases with artifacts.');
    }

    /**
     * Seed artifacts for a release.
     */
    private function seedArtifactsForRelease(Release $release): void
    {
        $platforms = ['darwin-aarch64', 'darwin-x86_64', 'windows-x86_64', 'linux-x86_64'];

        foreach ($platforms as $platform) {
            Artifact::factory()
                ->forRelease($release)
                ->forPlatform($platform)
                ->create();
        }
    }

    /**
     * Seed orders and licenses.
     */
    private function seedOrdersAndLicenses(): void
    {
        $users = User::all();
        $product = Product::first();

        if ($users->isEmpty() || ! $product) {
            return;
        }

        // Create 20 orders with licenses
        foreach ($users->take(5) as $user) {
            // Each customer gets 1-3 orders
            $orderCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $order = Order::factory()
                    ->forUser($user)
                    ->forProduct($product)
                    ->create();

                License::factory()
                    ->state(['order_id' => $order->id])
                    ->create();
            }
        }

        // Create some refunded orders
        Order::factory()
            ->count(2)
            ->refunded()
            ->create();

        $this->command->info('Created orders and licenses.');
    }

    /**
     * Seed webhook events.
     */
    private function seedWebhookEvents(): void
    {
        WebhookEvent::factory()
            ->count(15)
            ->create();

        // Some processed events
        WebhookEvent::factory()
            ->count(5)
            ->processed()
            ->create();

        $this->command->info('Created webhook events.');
    }

    /**
     * Seed download records.
     */
    private function seedDownloads(): void
    {
        $licenses = License::with(['order.user'])->take(5)->get();
        $artifacts = Artifact::all();

        if ($licenses->isEmpty() || $artifacts->isEmpty()) {
            return;
        }

        foreach ($licenses as $license) {
            $downloadCount = fake()->numberBetween(1, 5);

            for ($i = 0; $i < $downloadCount; $i++) {
                Download::factory()
                    ->state([
                        'user_id' => $license->order->user_id,
                        'license_id' => $license->id,
                        'artifact_id' => $artifacts->random()->id,
                    ])
                    ->create();
            }
        }

        $this->command->info('Created download records.');
    }
}
