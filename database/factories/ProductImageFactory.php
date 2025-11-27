<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_image_id' => (string) \Illuminate\Support\Str::uuid(),
            'product_id' => Product::factory(),
            'name' => fake()->words(2, true) . ' Image',
            'url' => 'https://via.placeholder.com/400x400?text=' . urlencode(fake()->words(2, true)),
        ];
    }
}
