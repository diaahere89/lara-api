<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $order = Order::create($request->only(['name', 'description', 'date']));
        return response()->json($order, 201);
    }

    public function show(Order $order)
    {
        return $order->load('products');
    }

    public function update(Request $request, Order $order)
    {
        $order->update($request->only(['name', 'description', 'date']));
        return response()->json($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->noContent();
    }
}
