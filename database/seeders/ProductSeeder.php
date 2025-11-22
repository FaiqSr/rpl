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
    }
}
