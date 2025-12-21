<?php

declare(strict_types=1);

namespace Tests\Feature\AttributeRefactor;

use App\Enums\LicenseStatus;
use App\Enums\ReleaseChannel;
use App\Models\License;
use App\Models\Product;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test that #[Scope] attributes work correctly.
 */
class ScopeAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_release_stable_scope_works(): void
    {
        $product = Product::factory()->create();

        Release::factory()->create([
            'product_id' => $product->id,
            'channel' => ReleaseChannel::STABLE,
        ]);

        Release::factory()->create([
            'product_id' => $product->id,
            'channel' => ReleaseChannel::BETA,
        ]);

        Release::factory()->create([
            'product_id' => $product->id,
            'channel' => ReleaseChannel::ALPHA,
        ]);

        $stableReleases = Release::stable()->get();

        $this->assertCount(1, $stableReleases);
        $this->assertTrue($stableReleases->first()->isStable());
    }

    public function test_release_published_scope_works(): void
    {
        $product = Product::factory()->create();

        Release::factory()->create([
            'product_id' => $product->id,
            'published_at' => now(),
        ]);

        Release::factory()->create([
            'product_id' => $product->id,
            'published_at' => null,
        ]);

        $publishedReleases = Release::published()->get();

        $this->assertCount(1, $publishedReleases);
        $this->assertTrue($publishedReleases->first()->isPublished());
    }

    public function test_release_downloadable_scope_works(): void
    {
        $product = Product::factory()->create();

        Release::factory()->create([
            'product_id' => $product->id,
            'is_downloadable' => true,
        ]);

        Release::factory()->create([
            'product_id' => $product->id,
            'is_downloadable' => false,
        ]);

        $downloadableReleases = Release::downloadable()->get();

        $this->assertCount(1, $downloadableReleases);
    }

    public function test_license_active_scope_works(): void
    {
        License::factory()->create(['status' => LicenseStatus::ACTIVE]);
        License::factory()->create(['status' => LicenseStatus::REFUNDED]);
        License::factory()->create(['status' => LicenseStatus::REVOKED]);

        $activeLicenses = License::active()->get();

        $this->assertCount(1, $activeLicenses);
        $this->assertTrue($activeLicenses->first()->isActive());
    }

    public function test_license_refunded_scope_works(): void
    {
        License::factory()->create(['status' => LicenseStatus::ACTIVE]);
        License::factory()->create(['status' => LicenseStatus::REFUNDED]);
        License::factory()->create(['status' => LicenseStatus::REVOKED]);

        $refundedLicenses = License::refunded()->get();

        $this->assertCount(1, $refundedLicenses);
        $this->assertTrue($refundedLicenses->first()->isRefunded());
    }

    public function test_license_revoked_scope_works(): void
    {
        License::factory()->create(['status' => LicenseStatus::ACTIVE]);
        License::factory()->create(['status' => LicenseStatus::REFUNDED]);
        License::factory()->create(['status' => LicenseStatus::REVOKED]);

        $revokedLicenses = License::revoked()->get();

        $this->assertCount(1, $revokedLicenses);
        $this->assertTrue($revokedLicenses->first()->isRevoked());
    }

    public function test_scopes_can_be_chained(): void
    {
        $product = Product::factory()->create();

        Release::factory()->create([
            'product_id' => $product->id,
            'channel' => ReleaseChannel::STABLE,
            'published_at' => now(),
            'is_downloadable' => true,
        ]);

        Release::factory()->create([
            'product_id' => $product->id,
            'channel' => ReleaseChannel::STABLE,
            'published_at' => null,
            'is_downloadable' => true,
        ]);

        Release::factory()->create([
            'product_id' => $product->id,
            'channel' => ReleaseChannel::BETA,
            'published_at' => now(),
            'is_downloadable' => true,
        ]);

        // Chain multiple scopes
        $releases = Release::stable()->published()->downloadable()->get();

        $this->assertCount(1, $releases);
    }
}
