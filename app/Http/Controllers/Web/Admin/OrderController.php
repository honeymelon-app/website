<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
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

        return Inertia::render('Admin/Orders/Index', [
            'orders' => new OrderCollection($orders),
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): Response
    {
        return Inertia::render('Admin/Orders/Show', [
            'order' => new OrderResource($order->load('license')),
        ]);
    }
}
