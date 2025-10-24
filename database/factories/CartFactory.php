<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'qty' => fake()->numberBetween(1, 3), // Default di migrasi 0, tapi di cart biasanya > 0
        ];
    }
}
