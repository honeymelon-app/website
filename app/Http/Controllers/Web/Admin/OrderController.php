<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Filters\OrderFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    private const DEFAULT_PAGE_SIZE = 15;

    private const ALLOWED_PAGE_SIZES = [10, 15, 25, 50, 100];

    private const SORTABLE_COLUMNS = [
        'email',
        'provider',
        'amount_cents',
        'currency',
        'created_at',
    ];

    /**
     * Display a listing of orders with filtering, sorting, and pagination.
     */
    public function index(Request $request, OrderFilter $filter): Response
    {
        $pageSize = $this->getValidatedPageSize($request);
        $sortColumn = $this->getValidatedSortColumn($request);
        $sortDirection = $this->getValidatedSortDirection($request);

        $query = Order::query()
            ->with('license')
            ->filter($filter);

        if ($sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->latest('created_at');
        }

        $orders = $query->paginate($pageSize)->withQueryString();

        return Inertia::render('admin/orders/Index', [
            'orders' => new OrderCollection($orders),
            'filters' => $request->only([
                'provider',
                'email',
                'email_search',
                'min_amount',
                'max_amount',
                'currency',
                'has_license',
                'license_status',
                'created_after',
                'created_before',
            ]),
            'sorting' => [
                'column' => $sortColumn,
                'direction' => $sortDirection,
            ],
            'pagination' => [
                'pageSize' => $pageSize,
                'allowedPageSizes' => self::ALLOWED_PAGE_SIZES,
            ],
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): Response
    {
        return Inertia::render('admin/orders/Show', [
            'order' => (new OrderResource($order->load('license')))->resolve(),
        ]);
    }

    /**
     * Process a refund for the specified order.
     */
    public function refund(Request $request, Order $order, RefundService $refundService): RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $refundService->refund($order, $request->input('reason'));

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('success', 'Order has been refunded successfully. The associated license has been revoked.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('error', 'Failed to process refund: '.$e->getMessage());
        }
    }

    /**
     * Get validated page size from request.
     */
    private function getValidatedPageSize(Request $request): int
    {
        $pageSize = (int) $request->input('per_page', self::DEFAULT_PAGE_SIZE);

        return in_array($pageSize, self::ALLOWED_PAGE_SIZES, true)
            ? $pageSize
            : self::DEFAULT_PAGE_SIZE;
    }

    /**
     * Get validated sort column from request.
     */
    private function getValidatedSortColumn(Request $request): ?string
    {
        $column = $request->input('sort');

        return in_array($column, self::SORTABLE_COLUMNS, true) ? $column : null;
    }

    /**
     * Get validated sort direction from request.
     */
    private function getValidatedSortDirection(Request $request): string
    {
        $direction = strtolower($request->input('direction', 'desc'));

        return in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';
    }
}
