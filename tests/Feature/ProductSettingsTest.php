<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guests_cannot_access_product_settings(): void
    {
        $response = $this->get(route('product.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_product_settings(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->get(route('product.edit'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('settings/Product')
                ->has('product')
                ->where('product.id', $product->id)
                ->where('product.name', $product->name)
        );
    }

    public function test_product_settings_page_shows_empty_state_when_no_product(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('product.edit'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('settings/Product')
                ->where('product', null)
        );
    }

    public function test_product_can_be_updated(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Old Name',
            'description' => 'Old description',
            'stripe_product_id' => null,
            'price_cents' => 1999,
            'currency' => 'usd',
        ]);

        $response = $this->actingAs($user)->put(route('product.update'), [
            'name' => 'New Name',
            'description' => 'New description',
            'stripe_product_id' => '',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals('New Name', $product->name);
        $this->assertEquals('New description', $product->description);
        // Price should remain unchanged since it's not submitted
        $this->assertEquals(1999, $product->price_cents);
        $this->assertEquals('usd', $product->currency);
    }

    public function test_product_is_created_when_none_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('product.update'), [
            'name' => 'Honeymelon',
            'description' => 'A beautiful video converter',
            'stripe_product_id' => '',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Honeymelon',
            'slug' => 'honeymelon',
        ]);
    }

    public function test_product_update_validates_stripe_product_id_format(): void
    {
        $user = User::factory()->create();
        Product::factory()->create();

        $response = $this->actingAs($user)->put(route('product.update'), [
            'name' => 'Test',
            'stripe_product_id' => 'invalid_format',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('stripe_product_id');
    }

    public function test_sync_requires_stripe_product_id(): void
    {
        $user = User::factory()->create();
        Product::factory()->create(['stripe_product_id' => null]);

        $response = $this->actingAs($user)->post(route('product.sync'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_preview_requires_stripe_product_id(): void
    {
        $user = User::factory()->create();
        Product::factory()->create(['stripe_product_id' => null]);

        $response = $this->actingAs($user)->post(route('product.preview'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
