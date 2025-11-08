<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LicenseStatus;
use App\Http\Controllers\Controller;
use App\Models\Artifact;
use App\Models\License;
use App\Models\Order;
use App\Models\Release;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Overview metrics
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('amount') / 100; // Convert cents to dollars
        $activeLicenses = License::where('status', LicenseStatus::ACTIVE)->count();
        $totalReleases = Release::whereNotNull('published_at')->count();

        // Calculate percentage changes (last 30 days vs previous 30 days)
        $ordersChange = $this->calculatePercentageChange(
            Order::where('created_at', '>=', now()->subDays(30))->count(),
            Order::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count()
        );

        $revenueThisMonth = Order::where('created_at', '>=', now()->subDays(30))->sum('amount') / 100;
        $revenueLastMonth = Order::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->sum('amount') / 100;
        $revenueChange = $this->calculatePercentageChange($revenueThisMonth, $revenueLastMonth);

        $licensesChange = $this->calculatePercentageChange(
            License::where('created_at', '>=', now()->subDays(30))->where('status', LicenseStatus::ACTIVE)->count(),
            License::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->where('status', LicenseStatus::ACTIVE)->count()
        );

        // Recent activity
        $recentOrders = Order::with('license')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'email' => $order->email,
                'amount' => $order->amount / 100,
                'currency' => $order->currency,
                'created_at' => $order->created_at->toIso8601String(),
                'license_status' => $order->license?->status?->value,
            ]);

        $recentLicenses = License::latest()
            ->take(5)
            ->get()
            ->map(fn ($license) => [
                'id' => $license->id,
                'key_plain' => $license->key_plain,
                'status' => $license->status->value,
                'max_major_version' => $license->max_major_version,
                'created_at' => $license->created_at->toIso8601String(),
            ]);

        // Chart data - Orders over time (last 30 days)
        $ordersChartData = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount) / 100 as revenue')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'orders' => $item->count,
                'revenue' => (float) $item->revenue,
            ]);

        // License status distribution
        $licensesByStatus = License::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn ($item) => [
                'status' => $item->status->value,
                'count' => $item->count,
            ]);

        // Platform distribution from artifacts
        $artifactsByPlatform = Artifact::select('platform', DB::raw('COUNT(*) as count'))
            ->groupBy('platform')
            ->get()
            ->map(fn ($item) => [
                'platform' => $item->platform,
                'count' => $item->count,
            ]);

        return Inertia::render('admin/Index', [
            'metrics' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'active_licenses' => $activeLicenses,
                'total_releases' => $totalReleases,
                'orders_change' => $ordersChange,
                'revenue_change' => $revenueChange,
                'licenses_change' => $licensesChange,
            ],
            'recent_orders' => $recentOrders,
            'recent_licenses' => $recentLicenses,
            'charts' => [
                'orders_over_time' => $ordersChartData,
                'licenses_by_status' => $licensesByStatus,
                'artifacts_by_platform' => $artifactsByPlatform,
            ],
        ]);
    }

    private function calculatePercentageChange(int|float $current, int|float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
