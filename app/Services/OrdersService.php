<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class OrdersService
{    
    public function searchOrders(Request $request): JsonResponse
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

    public function validateOrderInputs(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'products' => 'required|array', // Ensure products array is present
            'products.*.product_id' => 'required|exists:products,id', // Validate product IDs
            'products.*.quantity' => 'required|integer|min:1', // Validate quantities
        ]);

        if ( ! $validatedData ) {
            return response()->json(['error' => 'Invalid data'], 400);
        }

        return $validatedData;
    }

    public function createOrder(Request $request)
    {
        // Validate the request
        $validatedData = $this->validateOrderInputs($request);


        // Create the order
        $order = Order::create($request->only(['name', 'description']));
    
        // Attach products to the order
        foreach ($request->products as $product) {
            $order->products()->attach($product['product_id'], [
                'quantity' => $product['quantity'],
            ]);
    
            // Optional: Decrement product stock
            $productModel = Product::find($product['product_id']);
            $productModel->decrement('stock', $product['quantity']);
        }
    
        // Return the order with products
        return response()->json($order->load('products'), 201);        
    }


    public function updateOrder(Request $request, Order $order)
    {
        // Validate the request
        $validatedData = $this->validateOrderInputs($request);

        // Update the order
        $order->update($request->only(['name', 'description']));

        // Sync products with quantities
        $order->products()->sync([]);
        foreach ($request->products as $product) {
            $order->products()->attach($product['product_id'], [
                'quantity' => $product['quantity'],
            ]);
        }

        // Return the updated order with products
        return response()->json($order->load('products'));
    }


    public function deleteOrder(Order $order)
    {
        $order->delete();
        return response()->noContent();
    }
}