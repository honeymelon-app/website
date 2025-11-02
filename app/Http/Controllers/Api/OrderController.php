<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Filters\OrderFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request, OrderFilter $filter): OrderCollection
    {
        $orders = Order::query()
            ->filter($filter)
            ->paginate($request->input('per_page', 20));

        return new OrderCollection($orders);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load('license'));
    }
}
