<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::where('stock', '>', 0)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Users or Products not found. Please run UserSeeder and ProductSeeder first.');
            return;
        }

        Order::factory()
            ->count(100)
            ->make()
            ->each(function ($order) use ($users, $products) {
                $order->user_id = $users->random()->user_id;
                $order->save();

                $totalPrice = 0;
                $numItems = rand(1, 5);
                $orderProducts = $products->random($numItems);

                foreach ($orderProducts as $product) {
                    $qty = rand(1, 3);
                    $price = (int) $product->price;

                    OrderDetail::factory()->create([
                        'order_id' => $order->order_id,
                        'product_id' => $product->product_id,
                        'qty' => $qty,
                        'price' => $price,
                    ]);

                    $totalPrice += ($qty * $price);
                }

                $order->total_price = (int) $totalPrice;
                $order->save();
            });
    }
}
