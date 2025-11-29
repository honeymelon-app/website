<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

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
    /**
     * Display a listing of orders.
     */
    public function index(): Response
    {
        $orders = Order::query()
            ->with('license')
            ->latest('created_at')
            ->paginate(20);

        return Inertia::render('admin/orders/Index', [
            'orders' => new OrderCollection($orders),
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
}
