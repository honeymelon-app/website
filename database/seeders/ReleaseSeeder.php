<?php

namespace Database\Seeders;

use App\Models\Release;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReleaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate a sequence like:
        // 1.2.0-beta.1, 1.2.0 (stable), 1.2.1 (stable), 1.3.0-beta.1, 1.3.0 (stable)
        $start = Carbon::now()->subMonths(3);

        $series = [
            ['1.2.0', 'beta',  $start->copy()->addDays(0)],
            ['1.2.0', 'stable', $start->copy()->addDays(7)],
            ['1.2.1', 'stable', $start->copy()->addDays(21)],
            ['1.3.0', 'beta',  $start->copy()->addDays(35)],
            ['1.3.0', 'stable', $start->copy()->addDays(49)],
            ['1.3.1', 'stable', $start->copy()->addDays(63)],
        ];

        foreach ($series as [$version, $channel, $date]) {
            Release::factory()
                ->forVersion($version, $channel)
                ->state([
                    'published_at' => $date,
                    'major' => preg_match('/^\d+\.0\.0$/', $version) === 1,
                ])
                ->create();
        }

        // A few extra random betas and stables for variety
        Release::factory()->count(3)->beta()->create();
        Release::factory()->count(5)->stable()->create();
    }
}
