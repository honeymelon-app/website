<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Artifact;
use App\Models\License;
use App\Models\Order;
use App\Models\Release;
use App\Models\WebhookEvent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PlatformDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create one stable release with full artifacts and updates
        $stableRelease = Release::factory()
            ->forVersion('1.5.0', 'stable')
            ->state([
                'published_at' => Carbon::now()->subDays(7),
                'major' => false,
                'notes' => "# What's New\n\n- Performance improvements\n- Bug fixes\n- UI enhancements",
            ])
            ->create();

        // Create artifact for darwin-aarch64
        $stableArtifact = Artifact::factory()
            ->forRelease($stableRelease)
            ->forPlatform('darwin-aarch64')
            ->state([
                'filename' => "honeymelon-{$stableRelease->version}.dmg",
                'url' => "https://github.com/honeymelon-app/honeymelon/releases/download/v{$stableRelease->version}/honeymelon-{$stableRelease->version}.dmg",
            ])
            ->create();

        // Create one beta release with full artifacts and updates
        $betaRelease = Release::factory()
            ->forVersion('1.6.0', 'beta')
            ->state([
                'published_at' => Carbon::now()->subDays(2),
                'major' => false,
                'notes' => "# Beta Release\n\n- Experimental features\n- Testing new functionality",
            ])
            ->create();

        // Create artifact for darwin-aarch64
        $betaArtifact = Artifact::factory()
            ->forRelease($betaRelease)
            ->forPlatform('darwin-aarch64')
            ->state([
                'filename' => "honeymelon-{$betaRelease->version}.dmg",
                'url' => "https://github.com/honeymelon-app/honeymelon/releases/download/v{$betaRelease->version}/honeymelon-{$betaRelease->version}.dmg",
            ])
            ->create();

        // Create 5 licenses with orders
        $licenses = collect();
        for ($i = 0; $i < 5; $i++) {
            $order = Order::factory()->create();
            $license = License::factory()
                ->state(['order_id' => $order->id])
                ->create();
            $licenses->push($license);
        }

        // Create 3 webhook events
        WebhookEvent::factory()->count(3)->create();
    }
}
