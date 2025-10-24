<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_image_id' => (string) Str::uuid(),
            'product_id' => Product::factory(),
            'name' => fake()->sentence(2) . '.jpg',
            'url' => fake()->imageUrl(640, 480, 'products', true),
        ];
    }
}
