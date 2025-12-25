<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Filters\OrderFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRefundRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\RefundService;
use App\Support\IndexQueryParams;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
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
        $params = IndexQueryParams::fromRequest(
            request: $request,
            sortableColumns: self::SORTABLE_COLUMNS,
        );

        $query = Order::query()
            ->with('license')
            ->filter($filter);

        if ($params->sortColumn !== null) {
            $query->orderBy($params->sortColumn, $params->sortDirection);
        } else {
            $query->latest('created_at');
        }

        $orders = $query->paginate($params->pageSize)->withQueryString();

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
                'column' => $params->sortColumn,
                'direction' => $params->sortDirection,
            ],
            'pagination' => [
                'pageSize' => $params->pageSize,
                'allowedPageSizes' => IndexQueryParams::ALLOWED_PAGE_SIZES,
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
    public function refund(OrderRefundRequest $request, Order $order, RefundService $refundService): RedirectResponse
    {
        try {
            $refundService->refund($order, $request->validated('reason'));

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('success', 'Order has been refunded successfully. The associated license has been revoked.');
        } catch (\Exception $e) {
            return $this->handleWebException(
                $e,
                'admin.orders.show',
                'Failed to process refund',
                ['order_id' => $order->id],
                [$order]
            );
        }
    }
}
