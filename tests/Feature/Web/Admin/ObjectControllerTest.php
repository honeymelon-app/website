<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ObjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('s3');
    }

    public function test_objects_page_loads(): void
    {
        Storage::disk('s3')->put('releases/1.0.0/app.dmg', 'fake content');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.objects.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('admin/objects/Index')
                ->has('objects', 1)
                ->where('objects.0.name', 'app.dmg')
        );
    }

    public function test_objects_page_requires_authentication(): void
    {
        $response = $this->get(route('admin.objects.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_object_can_be_deleted(): void
    {
        Storage::disk('s3')->put('releases/1.0.0/app.dmg', 'fake content');

        $user = User::factory()->create();

        $this->assertTrue(Storage::disk('s3')->exists('releases/1.0.0/app.dmg'));

        $response = $this->actingAs($user)->delete(route('admin.objects.destroy', ['path' => 'releases/1.0.0/app.dmg']));

        $response->assertRedirect(route('admin.objects.index'));
        $response->assertSessionHas('success');
        $this->assertFalse(Storage::disk('s3')->exists('releases/1.0.0/app.dmg'));
    }

    public function test_deleting_nonexistent_object_returns_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.objects.destroy', ['path' => 'nonexistent/file.dmg']));

        $response->assertRedirect(route('admin.objects.index'));
        $response->assertSessionHas('error');
    }
}
