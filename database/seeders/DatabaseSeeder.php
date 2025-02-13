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
        $products = Product::factory(10)->create();
        Order::factory(12)->create()->each(function ($order) use ($products) {
            $order->products()->attach(
                $products->random(rand(1, 10))->pluck('id'),
                ['quantity' => rand(1, 5)]
            );
        });
    }
}
