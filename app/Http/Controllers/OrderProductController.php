<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderProductController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock < $validated['quantity']) {
            return response()->json(['error' => 'Not enough stock available'], Response::HTTP_BAD_REQUEST);
        }

        // Deduct stock
        $product->decrement('stock', $validated['quantity']);

        // Create order-product relationship
        $orderProduct = OrderProduct::create($validated);

        return response()->json($orderProduct, Response::HTTP_CREATED);
    }

    public function update(Request $request, OrderProduct $orderProduct)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0' // Allow 0 to indicate removal
        ]);
    
        $product = Product::findOrFail($orderProduct->product_id);
        $stockDifference = $validated['quantity'] - $orderProduct->quantity;
    
        if ($stockDifference > 0 && $product->stock < $stockDifference) {
            return response()->json(['error' => 'Not enough stock available'], Response::HTTP_BAD_REQUEST);
        }
    
        if ($validated['quantity'] == 0) {
            // Restore stock and remove order-product relation
            $product->increment('stock', $orderProduct->quantity);
            $orderProduct->delete();
    
            return response()->json(['message' => 'Product removed from order'], Response::HTTP_NO_CONTENT);
        }
    
        // Adjust stock accordingly
        $product->decrement('stock', $stockDifference);
    
        // Update order-product relationship
        $orderProduct->update(['quantity' => $validated['quantity']]);
    
        return response()->json($orderProduct);
    }
    
    public function destroy(OrderProduct $orderProduct)
    {
        $product = Product::findOrFail($orderProduct->product_id);

        // Restore stock before deleting the order-product relationship
        $product->increment('stock', $orderProduct->quantity);

        $orderProduct->delete();

        return response()->noContent();
    }
}
