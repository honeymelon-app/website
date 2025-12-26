<?php

declare(strict_types=1);

namespace Tests\Feature\RateLimiting;

use App\Models\Artifact;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class DownloadRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear rate limiter between tests
        RateLimiter::clear('downloads');
    }

    public function test_download_endpoint_has_rate_limiting(): void
    {
        $product = Product::factory()->create();
        $release = Release::factory()->create([
            'product_id' => $product->id,
            'version' => '1.0.0',
            'is_downloadable' => true,
        ]);
        $artifact = Artifact::factory()->create([
            'release_id' => $release->id,
            'platform' => 'darwin-aarch64',
            'source' => 'github',
        ]);
        $order = Order::factory()->create(['product_id' => $product->id]);
        $license = License::factory()->create([
            'product_id' => $product->id,
            'order_id' => $order->id,
        ]);

        // Make 10 requests (the limit per minute)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get("/api/download?license={$license->key_plain}&version=1.0.0&platform=darwin-aarch64");
            $this->assertTrue(
                in_array($response->status(), [302, 400, 403, 404]),
                sprintf(
                    'Request %d should succeed; got status %d (content-type: %s, location: %s)',
                    $i,
                    $response->status(),
                    (string) $response->headers->get('content-type'),
                    (string) $response->headers->get('location')
                )
            );
        }

        // 11th request should be rate limited
        $response = $this->get("/api/download?license={$license->key_plain}&version=1.0.0&platform=darwin-aarch64");
        $response->assertStatus(429);
        $response->assertJson(['message' => 'Too many download attempts. Please try again later.']);
    }

    public function test_rate_limit_is_per_ip_and_license_combination(): void
    {
        $product = Product::factory()->create();
        $release = Release::factory()->create([
            'product_id' => $product->id,
            'version' => '1.0.0',
            'is_downloadable' => true,
        ]);
        $artifact = Artifact::factory()->create([
            'release_id' => $release->id,
            'platform' => 'darwin-aarch64',
            'source' => 'github',
        ]);
        $order1 = Order::factory()->create(['product_id' => $product->id]);
        $license1 = License::factory()->create([
            'product_id' => $product->id,
            'order_id' => $order1->id,
        ]);
        $order2 = Order::factory()->create(['product_id' => $product->id]);
        $license2 = License::factory()->create([
            'product_id' => $product->id,
            'order_id' => $order2->id,
        ]);

        // Make 10 requests with license1
        for ($i = 0; $i < 10; $i++) {
            $this->get("/api/download?license={$license1->key_plain}&version=1.0.0&platform=darwin-aarch64");
        }

        // 11th request with license1 should be rate limited
        $response = $this->get("/api/download?license={$license1->key_plain}&version=1.0.0&platform=darwin-aarch64");
        $response->assertStatus(429);

        // But first request with license2 from same IP should succeed (different license key)
        $response = $this->get("/api/download?license={$license2->key_plain}&version=1.0.0&platform=darwin-aarch64");
        $this->assertTrue(in_array($response->status(), [302, 400, 403, 404]), 'Different license should have separate limit');
    }

    public function test_rate_limit_headers_are_present(): void
    {
        $product = Product::factory()->create();
        $release = Release::factory()->create([
            'product_id' => $product->id,
            'version' => '1.0.0',
            'is_downloadable' => true,
        ]);
        $artifact = Artifact::factory()->create([
            'release_id' => $release->id,
            'platform' => 'darwin-aarch64',
            'source' => 'github',
        ]);
        $order = Order::factory()->create(['product_id' => $product->id]);
        $license = License::factory()->create([
            'product_id' => $product->id,
            'order_id' => $order->id,
        ]);

        $response = $this->get("/api/download?license={$license->key_plain}&version=1.0.0&platform=darwin-aarch64");

        // Rate limit headers should be present
        $this->assertNotNull($response->headers->get('X-RateLimit-Limit'));
        $this->assertNotNull($response->headers->get('X-RateLimit-Remaining'));
    }
}
