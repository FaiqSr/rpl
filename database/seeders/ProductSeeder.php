<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed a small, predictable set to match dashboard demo
        Product::factory()
            ->hasImages(1)
            ->create([
                'name' => 'Daging segar',
                'slug' => 'daging-segar',
                'description' => 'Daging ayam segar berkualitas',
                'price' => 40000,
                'stock' => 15,
                'unit' => 'kg',
            ]);

        Product::factory()
            ->hasImages(1)
            ->create([
                'name' => 'Daging segar',
                'slug' => 'daging-segar-2',
                'description' => 'Daging ayam segar',
                'price' => 40000,
                'stock' => 15,
                'unit' => 'kg',
            ]);

        Product::factory()
            ->hasImages(1)
            ->create([
                'name' => 'Dada Fillet',
                'slug' => 'dada-fillet',
                'description' => 'Dada fillet premium',
                'price' => 50000,
                'stock' => 10,
                'unit' => 'kg',
            ]);

        // Dummy products without images
        $dummyProducts = [
            ['name' => 'Sayap Ayam', 'slug' => 'sayap-ayam', 'price' => 35000, 'stock' => 20, 'unit' => 'kg', 'description' => 'Sayap ayam segar'],
            ['name' => 'Paha Ayam', 'slug' => 'paha-ayam', 'price' => 38000, 'stock' => 18, 'unit' => 'kg', 'description' => 'Paha ayam berkualitas'],
            ['name' => 'Dada Ayam', 'slug' => 'dada-ayam', 'price' => 45000, 'stock' => 12, 'unit' => 'kg', 'description' => 'Dada ayam tanpa tulang'],
            ['name' => 'Ayam Utuh', 'slug' => 'ayam-utuh', 'price' => 75000, 'stock' => 8, 'unit' => 'ekor', 'description' => 'Ayam utuh segar'],
            ['name' => 'Telur Ayam Kampung', 'slug' => 'telur-kampung', 'price' => 35000, 'stock' => 50, 'unit' => 'kg', 'description' => 'Telur ayam kampung organik'],
            ['name' => 'Telur Ayam Negeri', 'slug' => 'telur-negeri', 'price' => 28000, 'stock' => 100, 'unit' => 'kg', 'description' => 'Telur ayam negeri segar'],
            ['name' => 'Hati Ampela', 'slug' => 'hati-ampela', 'price' => 32000, 'stock' => 15, 'unit' => 'kg', 'description' => 'Hati dan ampela ayam'],
            ['name' => 'Ceker Ayam', 'slug' => 'ceker-ayam', 'price' => 25000, 'stock' => 25, 'unit' => 'kg', 'description' => 'Ceker ayam bersih'],
            ['name' => 'Tulang Ayam', 'slug' => 'tulang-ayam', 'price' => 15000, 'stock' => 30, 'unit' => 'kg', 'description' => 'Tulang ayam untuk kaldu'],
            ['name' => 'Kulit Ayam', 'slug' => 'kulit-ayam', 'price' => 20000, 'stock' => 10, 'unit' => 'kg', 'description' => 'Kulit ayam crispy'],
        ];

        foreach ($dummyProducts as $prod) {
            Product::factory()->create($prod);
        }
    }
}