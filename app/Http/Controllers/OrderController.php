<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrdersService;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private OrdersService $ordersService
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->ordersService->searchOrders($request);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->ordersService->createOrder($request);
    }

    public function show(Order $order)
    {
        return $order->load('products');
    }

    public function update(Request $request, Order $order)
    {
        return $this->ordersService->updateOrder($request, $order);
    }

    public function destroy(Order $order)
    {
        return $this->ordersService->deleteOrder($order);
    }
    
}
