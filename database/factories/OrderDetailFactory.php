<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_detail_id' => (string) Str::uuid(),
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'qty' => fake()->numberBetween(1, 5),
            'price' => fake()->numberBetween(10000, 1000000), // Sesuai 'integer'
        ];
    }
}
