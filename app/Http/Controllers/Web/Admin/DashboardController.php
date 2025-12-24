<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index(): Response
    {
        $metrics = $this->dashboardService->getMetrics();
        $recentOrders = $this->dashboardService->getRecentOrders();
        $recentLicenses = $this->dashboardService->getRecentLicenses();
        $chartData = $this->dashboardService->getChartData();
        $visitorAnalytics = $this->dashboardService->getVisitorAnalytics();

        return Inertia::render('admin/Index', [
            'metrics' => $metrics,
            'recent_orders' => $recentOrders,
            'recent_licenses' => $recentLicenses,
            'charts' => [
                'orders_over_time' => $chartData['orders_over_time'],
                'licenses_by_status' => $chartData['licenses_by_status'],
                'artifacts_by_platform' => $chartData['artifacts_by_platform'],
            ],
            'visitor_analytics' => $visitorAnalytics,
        ]);
    }
}
