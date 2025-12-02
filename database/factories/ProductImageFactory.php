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
        // Generate SVG placeholder sebagai data URI (tidak perlu koneksi internet)
        $productName = fake()->words(2, true);
        $svgPlaceholder = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">' .
            '<rect width="400" height="400" fill="#f3f4f6"/>' .
            '<text x="50%" y="50%" font-family="Arial, sans-serif" font-size="18" fill="#6b7280" text-anchor="middle" dy=".3em">' . 
            htmlspecialchars($productName) . 
            '</text>' .
            '</svg>'
        );
        
        return [
            'product_image_id' => (string) \Illuminate\Support\Str::uuid(),
            'product_id' => Product::factory(),
            'name' => $productName . ' Image',
            'url' => $svgPlaceholder,
        ];
    }
}
