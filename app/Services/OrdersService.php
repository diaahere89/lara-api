<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Get the total count of orders (for pagination)
        $totalCount = Order::count();

        $orders = $query->get()->map(function ($order) {
            $order->products = DB::table('order_products')
            ->where('order_id', $order->id)
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->select('products.id as product_id', 'products.name as product_name', 'order_products.quantity')
            ->get();
            return $order;
        });

        return response()->json($orders)->header('X-Total-Count', $totalCount); // Add the X-Total-Count header;
    }

    public function validateOrder(Request $request, ?bool $updated=false)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'products' => 'required|array', // Ensure products array is present
            'products.*.product_id' => 'required|exists:products,id', // Validate product IDs
            'products.*.quantity' => 'required|integer|min:1', // Validate quantities
        ];
        if ($updated) {
            $rules['name'] = 'sometimes|string|max:255';
            $rules['products.*.quantity'] = 'required|integer'; // Validate quantities
        }

        $validatedData = $request->validate($rules);
        
        if ( ! $validatedData ) {
            return response()->json(['error' => 'Invalid data'], 400);
        }

        return $validatedData;
    }

    public function checkStockAvailability(Request $request)
    {
        // Check stock availability
        foreach ($request->products as $product) {
            $productModel = Product::find($product['product_id']);
            if ($productModel->stock < $product['quantity']) {
                return response()->json([
                    'message' => 'Insufficient stock for product: ' . $productModel->name,
                    'product_id' => $productModel->id,
                    'available_stock' => $productModel->stock,
                    'requested_quantity' => $product['quantity'],
                ], 422); // 422 Unprocessable Entity
            }
        }
    }


    public function createOrder(Request $request)
    {
        // Validate the request
        $validatedData = $this->validateOrder($request);

        // Check stock availability
        $unavailable = $this->checkStockAvailability($request);
        if ($unavailable) return $unavailable;

        $order = Order::find(0);

        // Use a database transaction to ensure atomicity
        DB::transaction(function () use ($request, &$order) {
            // Create the order
            $order = Order::create($request->only(['name', 'description']));
    
            // Attach products to the order and update stock
            foreach ($request->products as $product) {
                $order->products()->attach($product['product_id'], [
                    'quantity' => $product['quantity'],
                ]);
    
                // Decrement product stock
                $productModel = Product::find($product['product_id']);
                $productModel->decrement('stock', $product['quantity']);
            }
        });

        // Return the order with products
        return response()->json($order->load('products'), 201);
    }


    public function updateOrder(Request $request, Order $order): JsonResponse
    {
        // Validate the request
        $validatedData = $this->validateOrder($request, updated: true);

        $productModel = null;
        $product = null;
    
        try {
            // Use a database transaction to ensure atomicity
            DB::transaction(function () use ($request, $order, &$productModel, &$product) {
                // Update the order fields (if provided)
                if ($request->has('name')) {
                    $order->name = $request->input('name');
                }
                if ($request->has('description')) {
                    $order->description = $request->input('description');
                }
                $order->save();
        
                // Update products (if provided)
                if ($request->has('products')) {
                    $productsData = [];
                    foreach ($request->products as $product) {
                        $productId = $product['product_id'];
                        $newQuantity = $product['quantity'];
                        $productModel = Product::find($productId);
    
                        // Get the current quantity of the product in the order (if it exists)
                        $currentQuantity = $order->products->find($productId)->pivot->quantity ?? 0;
    
                        // Case 1: New Quantity = 0 (Remove product from order)
                        if ($newQuantity == 0) {
                            // Increase stock by the current quantity
                            $productModel->increment('stock', $currentQuantity);
                            continue; // Skip adding to $productsData
                        }
    
                        // Case 2: New Quantity = Current Quantity (Do nothing)
                        if ($newQuantity == $currentQuantity) {
                            $productsData[$productId] = ['quantity' => $newQuantity];
                            continue;
                        }
    
                        // Case 3: New Quantity > Current Quantity
                        if ($newQuantity > $currentQuantity) {
                            $quantityDifference = $newQuantity - $currentQuantity;
    
                            // Check if the difference exceeds available stock
                            if ($productModel->stock < $quantityDifference) {
                                throw new \Exception("Insufficient stock for product: " . $productModel->name);
                            }
    
                            // Decrement stock by the difference
                            $productModel->decrement('stock', $quantityDifference);
                        }
    
                        // Case 4: New Quantity < Current Quantity
                        if ($newQuantity < $currentQuantity) {
                            $quantityDifference = $currentQuantity - $newQuantity;
    
                            // Increment stock by the difference
                            $productModel->increment('stock', $quantityDifference);
                        }
    
                        // Add the product to the sync data
                        $productsData[$productId] = ['quantity' => $newQuantity];
                    }
        
                    // Sync products with the order
                    $order->products()->sync($productsData);
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Insufficient stock for product: ' . $productModel->name,
                'product_id' => $productModel->id,
                'available_stock' => $productModel->stock,
                'requested_quantity' => $product['quantity'],
            ], 422); // 422 Unprocessable Entity
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