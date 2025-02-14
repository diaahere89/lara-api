<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $products = Product::factory(12)->create();  // Create exactly 12 products

        Order::factory(20)->create()->each(function ($order) use ($products) {
            // Pick a random number of products for each order (between 1 and 12 products)
            $randomProducts = $products->random(rand(1, 12)); 
        
            // Attach each product to the order with a random quantity between 1 and 11
            $randomProducts->each(function ($product) use ($order) {
                $quantity = rand(1, 11);  // Random quantity between 1 and 11
                $order->products()->attach($product->id, ['quantity' => $quantity]);
            });
        });
    }
}
