<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(): Response
    {
        return Inertia::render('admin/Index', [
            'metrics' => $this->dashboardService->getMetrics(),
            'recent_orders' => $this->dashboardService->getRecentOrders(),
            'recent_licenses' => $this->dashboardService->getRecentLicenses(),
            'charts' => $this->dashboardService->getChartData(),
        ]);
    }
}
