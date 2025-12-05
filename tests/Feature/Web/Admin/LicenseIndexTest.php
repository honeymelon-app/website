<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Admin;

use App\Models\Release;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LicenseIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_available_major_versions_from_releases(): void
    {
        $user = User::factory()->create();

        Release::factory()
            ->forVersion('1.2.0')
            ->stable()
            ->create([
                'published_at' => now()->subDays(10),
                'is_downloadable' => true,
            ]);

        Release::factory()
            ->forVersion('2.0.1')
            ->stable()
            ->create([
                'published_at' => now()->subDays(2),
                'is_downloadable' => true,
            ]);

        // Beta release should be ignored for available versions
        Release::factory()
            ->forVersion('2.1.0', 'beta')
            ->create([
                'published_at' => now()->subDay(),
                'is_downloadable' => true,
            ]);

        $this
            ->actingAs($user)
            ->get(route('admin.licenses.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/licenses/Index')
                ->where('available_versions', [2, 1])
                ->etc());
    }

    public function test_includes_alpha_major_zero_versions(): void
    {
        $user = User::factory()->create();

        Release::factory()
            ->forVersion('0.0.4', 'alpha')
            ->create([
                'published_at' => now()->subDays(5),
                'is_downloadable' => true,
            ]);

        Release::factory()
            ->forVersion('1.0.0')
            ->stable()
            ->create([
                'published_at' => now()->subDay(),
                'is_downloadable' => true,
            ]);

        $this
            ->actingAs($user)
            ->get(route('admin.licenses.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/licenses/Index')
                ->where('available_versions', [1, 0])
                ->etc());
    }
}
