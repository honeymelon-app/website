<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\DateRanges;
use App\Models\Artifact;
use App\Models\License;
use App\Models\Order;
use App\Models\Release;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DashboardService
{
    /**
     * Get all dashboard metrics.
     *
     * @return array{
     *     total_orders: int,
     *     total_revenue_cents: int,
     *     active_licenses: int,
     *     total_releases: int,
     *     orders_change: float,
     *     revenue_change: float,
     *     licenses_change: float
     * }
     */
    public function getMetrics(): array
    {
        return [
            'total_orders' => Order::count(),
            'total_revenue_cents' => (int) Order::sum('amount_cents'),
            'active_licenses' => License::active()->count(),
            'total_releases' => Release::whereNotNull('published_at')->count(),
            'orders_change' => $this->calculateOrdersChange(),
            'revenue_change' => $this->calculateRevenueChange(),
            'licenses_change' => $this->calculateLicensesChange(),
        ];
    }

    /**
     * Get recent orders for the dashboard.
     *
     * @return Collection<int, array{
     *     id: string,
     *     email: string,
     *     amount_cents: int,
     *     formatted_amount: string,
     *     currency: string,
     *     created_at: string,
     *     license_status: string|null
     * }>
     */
    public function getRecentOrders(int $limit = 5): Collection
    {
        return Order::with('license')
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'email' => $order->email,
                'amount_cents' => $order->amount_cents,
                'formatted_amount' => $order->formatted_amount,
                'currency' => $order->currency,
                'created_at' => $order->created_at->toIso8601String(),
                'license_status' => $order->license?->status?->value,
            ]);
    }

    /**
     * Get recent licenses for the dashboard.
     *
     * @return Collection<int, array{
     *     id: string,
     *     key_plain: string,
     *     status: string,
     *     max_major_version: int,
     *     created_at: string
     * }>
     */
    public function getRecentLicenses(int $limit = 5): Collection
    {
        return License::latest()
            ->take($limit)
            ->get()
            ->map(fn (License $license): array => [
                'id' => $license->id,
                'key_plain' => $license->key_plain,
                'status' => $license->status->value,
                'max_major_version' => $license->max_major_version,
                'created_at' => $license->created_at->toIso8601String(),
            ]);
    }

    /**
     * Get chart data for the dashboard.
     *
     * @return array{
     *     orders_over_time: Collection,
     *     licenses_by_status: Collection,
     *     artifacts_by_platform: Collection
     * }
     */
    public function getChartData(): array
    {
        return [
            'orders_over_time' => $this->getOrdersOverTime(),
            'licenses_by_status' => $this->getLicensesByStatus(),
            'artifacts_by_platform' => $this->getArtifactsByPlatform(),
        ];
    }

    private function calculateOrdersChange(): float
    {
        $current = Order::withinDays(DateRanges::DASHBOARD_COMPARISON_DAYS)->count();
        $previous = Order::betweenDates(
            now()->subDays(DateRanges::DASHBOARD_COMPARISON_DAYS * 2),
            now()->subDays(DateRanges::DASHBOARD_COMPARISON_DAYS)
        )->count();

        return $this->calculatePercentageChange($current, $previous);
    }

    private function calculateRevenueChange(): float
    {
        $current = Order::withinDays(DateRanges::DASHBOARD_COMPARISON_DAYS)->sum('amount_cents') / 100;
        $previous = Order::betweenDates(
            now()->subDays(DateRanges::DASHBOARD_COMPARISON_DAYS * 2),
            now()->subDays(DateRanges::DASHBOARD_COMPARISON_DAYS)
        )->sum('amount_cents') / 100;

        return $this->calculatePercentageChange($current, $previous);
    }

    private function calculateLicensesChange(): float
    {
        $current = License::withinDays(DateRanges::DASHBOARD_COMPARISON_DAYS)->active()->count();
        $previous = License::betweenDates(
            now()->subDays(DateRanges::DASHBOARD_COMPARISON_DAYS * 2),
            now()->subDays(DateRanges::DASHBOARD_COMPARISON_DAYS)
        )->active()->count();

        return $this->calculatePercentageChange($current, $previous);
    }

    private function calculatePercentageChange(int|float $current, int|float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getOrdersOverTime(): Collection
    {
        return Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount_cents) / 100 as revenue')
        )
            ->withinDays(DateRanges::DASHBOARD_COMPARISON_DAYS)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item): array => [
                'date' => $item->date,
                'orders' => $item->count,
                'revenue' => (float) $item->revenue,
            ]);
    }

    private function getLicensesByStatus(): Collection
    {
        return License::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn ($item): array => [
                'status' => $item->status->value,
                'count' => $item->count,
            ]);
    }

    private function getArtifactsByPlatform(): Collection
    {
        return Artifact::select('platform', DB::raw('COUNT(*) as count'))
            ->groupBy('platform')
            ->get()
            ->map(fn ($item): array => [
                'platform' => $item->platform,
                'count' => $item->count,
            ]);
    }
}
