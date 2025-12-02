<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cart::truncate();

        $users = User::all();
        $products = Product::where('stock', '>', 0)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Users or Products not found. Please run seeders first.');
            return;
        }

        $userProductPairs = [];

        for ($i = 0; $i < 30; $i++) {
            $user = $users->random();
            $product = $products->random();
            $pair = $user->user_id . '-' . $product->product_id;

            if (in_array($pair, $userProductPairs)) {
                continue;
            }
            $userProductPairs[] = $pair;

            Cart::factory()->create([
                'user_id' => $user->user_id,
                'product_id' => $product->product_id,
                'qty' => rand(1, 3),
            ]);
        }
    }
}
