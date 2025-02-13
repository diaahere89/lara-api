<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderCreateTest extends TestCase
{
    use RefreshDatabase;

    private function createAuthUserToken()
    {
        // Create a user
        $user = \App\Models\User::factory()->create();

        // Generate a Sanctum token for the user
        return $user->createToken('auth_token')->plainTextToken;
    }

    public function test_create_order_successfully()
    {
        $token = $this->createAuthUserToken();

        // Create a product with sufficient stock
        $product = Product::factory()->create(['stock' => 10]);

        // Data for the new order
        $orderData = [
            'name' => 'New Order',
            'description' => 'This is a test order',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ];

        // Send a POST request to create the order with the token in the headers
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/orders', $orderData);

        // Assert the response status is 201 (Created)
        $response->assertStatus(201);

        // Assert the order was created in the database
        $this->assertDatabaseHas('orders', [
            'name' => 'New Order',
            'description' => 'This is a test order',
        ]);

        // Assert the product stock was decremented
        $this->assertEquals(5, $product->fresh()->stock);
    }

    public function test_create_order_fails_due_to_insufficient_stock()
    {
        $token = $this->createAuthUserToken();

        // Create a product with low stock
        $product = Product::factory()->create(['stock' => 2]);

        // Data for the new order (quantity exceeds stock)
        $orderData = [
            'name' => 'New Order',
            'description' => 'This is a test order',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ];

        // Send a POST request to create the order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/orders', $orderData);

        // Assert the response status is 422 (Unprocessable Entity)
        $response->assertStatus(422);

        // Assert the error message is returned
        $response->assertJson([
            'message' => 'Insufficient stock for product: ' . $product->name,
        ]);

        // Assert the order was not created in the database
        $this->assertDatabaseMissing('orders', [
            'name' => 'New Order',
        ]);

        // Assert the product stock remains unchanged
        $this->assertEquals(2, $product->fresh()->stock);
    }

    public function test_update_order_successfully()
    {
        $token = $this->createAuthUserToken();

        // Create a product with sufficient stock
        $product = Product::factory()->create(['stock' => 10]);

        // Create an order with the product
        $order = Order::factory()->create();
        $order->products()->attach($product->id, ['quantity' => 3]);

        // Data to update the order
        $updateData = [
            'name' => 'Updated Order Name',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5, // Increase quantity
                ],
            ],
        ];

        // Send a PUT request to update the order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/orders/{$order->id}", $updateData);

        // Assert the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert the order was updated in the database
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'name' => 'Updated Order Name',
        ]);

        // Assert the product stock was updated correctly
        $this->assertEquals(8, $product->fresh()->stock); // 10 - (5 - 3) = 8
    }
    
    public function test_update_order_fails_due_to_insufficient_stock()
    {
        $token = $this->createAuthUserToken();

        // Create a product with low stock
        $product = Product::factory()->create(['stock' => 2]);

        // Create an order with the product
        $order = Order::factory()->create();
        $order->products()->attach($product->id, ['quantity' => 3]);

        // Data to update the order (new quantity exceeds stock)
        $updateData = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 6, // New quantity exceeds stock
                ],
            ],
        ];

        // Send a PUT request to update the order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/orders/{$order->id}", $updateData);

        // Assert the response status is 422 (Unprocessable Entity)
        $response->assertStatus(422);

        // Assert the error message is returned
        $response->assertJson([
            'message' => 'Insufficient stock for product: ' . $product->name,
        ]);

        // Assert the product stock remains unchanged
        $this->assertEquals(2, $product->fresh()->stock);
    }
        
    public function test_update_order_with_multiple_products()
    {
        $token = $this->createAuthUserToken();

        // Create products with sufficient stock
        $product1 = Product::factory()->create(['stock' => 10]);
        $product2 = Product::factory()->create(['stock' => 15]);

        // Create an order with the products
        $order = Order::factory()->create();
        $order->products()->attach($product1->id, ['quantity' => 3]);
        $order->products()->attach($product2->id, ['quantity' => 5]);

        // Data to update the order
        $updateData = [
            'products' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 4, // Increase quantity
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 2, // Decrease quantity
                ],
            ],
        ];

        // Send a PUT request to update the order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/orders/{$order->id}", $updateData);

        // Assert the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert the product stocks were updated correctly
        $this->assertEquals(9, $product1->fresh()->stock); // 10 - (4 - 3) = 9
        $this->assertEquals(18, $product2->fresh()->stock); // 15 + (5 - 2) = 18
    }
    
    public function test_update_order_with_invalid_product_id()
    {
        $token = $this->createAuthUserToken();

        // Create an order
        $order = Order::factory()->create();

        // Data to update the order with an invalid product ID
        $updateData = [
            'products' => [
                [
                    'product_id' => 999, // Invalid product ID
                    'quantity' => 5,
                ],
            ],
        ];

        // Send a PUT request to update the order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/orders/{$order->id}", $updateData);

        // Assert the response status is 422 (Unprocessable Entity)
        $response->assertStatus(422);

        // Assert the error message is returned
        $response->assertJsonValidationErrors(['products.0.product_id']);
    }
    
    public function test_create_order_with_multiple_products()
    {
        $token = $this->createAuthUserToken();

        // Create products with sufficient stock
        $product1 = Product::factory()->create(['stock' => 10]);
        $product2 = Product::factory()->create(['stock' => 15]);

        // Data for the new order
        $orderData = [
            'name' => 'New Order',
            'description' => 'This is a test order',
            'products' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 4,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 5,
                ],
            ],
        ];

        // Send a POST request to create the order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/orders', $orderData);

        // Assert the response status is 201 (Created)
        $response->assertStatus(201);

        // Assert the product stocks were updated correctly
        $this->assertEquals(6, $product1->fresh()->stock); // 10 - 4 = 6
        $this->assertEquals(10, $product2->fresh()->stock); // 15 - 5 = 10
    }

    public function test_create_order_with_zero_quantity()
    {
        $token = $this->createAuthUserToken();

        // Create a product
        $product = Product::factory()->create(['stock' => 10]);

        // Data for the new order with zero quantity
        $orderData = [
            'name' => 'New Order',
            'description' => 'This is a test order',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0, // Zero quantity
                ],
            ],
        ];

        // Send a POST request to create the order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/orders', $orderData);

        // Assert the response status is 422 (Unprocessable Entity)
        $response->assertStatus(422);

        // Assert the error message is returned
        $response->assertJsonValidationErrors(['products.0.quantity']);
    }

    public function test_remove_product_from_order()
    {
        $token = $this->createAuthUserToken();

        // Create a product
        $product = Product::factory()->create(['stock' => 10]);

        // Create an order with the product
        $order = Order::factory()->create();
        $order->products()->attach($product->id, ['quantity' => 3]);

        // Data to update the order (remove the product)
        $updateData = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0, // Remove the product
                ],
            ],
        ];

        // Send a PUT request to update the order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/orders/{$order->id}", $updateData);

        // Assert the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert the product was removed from the order
        $this->assertDatabaseMissing('order_products', [
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        // Assert the product stock was incremented
        $this->assertEquals(13, $product->fresh()->stock); // 10 + 3 = 13
    }

    public function test_concurrent_order_updates_affect_stock_correctly()
    {
        $token = $this->createAuthUserToken();
        
        // Create a product with sufficient stock
        $product = Product::factory()->create(['stock' => 10]);

        // Create an order with the product
        $order = Order::factory()->create();
        $order->products()->attach($product->id, ['quantity' => 3]);

        // Simulate concurrent updates using multiple requests
        $requests = [];
        for ($i = 0; $i < 5; $i++) {
            $requests[] = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->putJson("/api/orders/{$order->id}", [
                'products' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => $i + 1, // Vary the quantity
                    ],
                ],
            ]);
        }

        // Wait for all requests to complete
        foreach ($requests as $request) {
            $request->assertStatus(200);
        }

        // Assert the product stock was updated correctly
        $this->assertEquals(8, $product->fresh()->stock); // 10 - (5 - 3) = 8
    }

    public function test_update_order_to_remove_all_products()
    {
        $token = $this->createAuthUserToken();

        // Create products
        $product1 = Product::factory()->create(['stock' => 10]);
        $product2 = Product::factory()->create(['stock' => 15]);
    
        // Create an order with the products
        $order = Order::factory()->create();
        $order->products()->attach($product1->id, ['quantity' => 3]);
        $order->products()->attach($product2->id, ['quantity' => 5]);
    
        // Data to update the order (remove all products)
        $updateData = [
            'products' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 0, // Remove product 1
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 0, // Remove product 2
                ],
            ],
        ];
    
        // Send a PUT request to update the order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/orders/{$order->id}", $updateData);
    
        // Assert the response status is 200 (OK)
        $response->assertStatus(200);
    
        // Assert the products were removed from the order
        $this->assertDatabaseMissing('order_products', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
        ]);
        $this->assertDatabaseMissing('order_products', [
            'order_id' => $order->id,
            'product_id' => $product2->id,
        ]);
    
        // Assert the product stocks were updated correctly
        $this->assertEquals(13, $product1->fresh()->stock); // 10 + 3 = 13
        $this->assertEquals(20, $product2->fresh()->stock); // 15 + 5 = 20
    }

}