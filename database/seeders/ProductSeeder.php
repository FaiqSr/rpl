<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus semua produk lama
        Product::query()->delete();
        
        // Daftar produk baru dengan harga dan stok yang wajar
        $products = [
            // Ayam Potong Segar
            ['name' => 'Ayam Broiler Utuh', 'price' => 35000, 'stock' => 50, 'unit' => 'ekor', 'description' => 'Ayam broiler utuh segar, siap masak'],
            ['name' => 'Ayam Broiler Utuh Tanpa Kepala & Ceker', 'price' => 33000, 'stock' => 45, 'unit' => 'ekor', 'description' => 'Ayam broiler utuh tanpa kepala dan ceker'],
            ['name' => 'Ayam Potong 1 kg', 'price' => 32000, 'stock' => 60, 'unit' => 'kg', 'description' => 'Ayam potong segar 1 kg'],
            ['name' => 'Ayam Potong 1.2 kg', 'price' => 38000, 'stock' => 55, 'unit' => 'kg', 'description' => 'Ayam potong segar 1.2 kg'],
            ['name' => 'Ayam Potong 1.5 kg', 'price' => 45000, 'stock' => 50, 'unit' => 'kg', 'description' => 'Ayam potong segar 1.5 kg'],
            ['name' => 'Paha Atas (Thigh)', 'price' => 42000, 'stock' => 40, 'unit' => 'kg', 'description' => 'Paha atas ayam segar'],
            ['name' => 'Paha Bawah (Drumstick)', 'price' => 40000, 'stock' => 40, 'unit' => 'kg', 'description' => 'Paha bawah ayam segar'],
            ['name' => 'Sayap Ayam', 'price' => 35000, 'stock' => 50, 'unit' => 'kg', 'description' => 'Sayap ayam segar'],
            ['name' => 'Kulit Ayam', 'price' => 18000, 'stock' => 30, 'unit' => 'kg', 'description' => 'Kulit ayam segar'],
            ['name' => 'Kepala Ayam', 'price' => 12000, 'stock' => 25, 'unit' => 'kg', 'description' => 'Kepala ayam segar'],
            ['name' => 'Ceker Ayam', 'price' => 22000, 'stock' => 35, 'unit' => 'kg', 'description' => 'Ceker ayam segar'],
            
            // Dada Ayam
            ['name' => 'Dada Ayam Utuh', 'price' => 45000, 'stock' => 40, 'unit' => 'kg', 'description' => 'Dada ayam utuh segar'],
            ['name' => 'Dada Ayam Fillet', 'price' => 55000, 'stock' => 35, 'unit' => 'kg', 'description' => 'Dada ayam fillet premium'],
            ['name' => 'Dada Ayam Fillet Premium (tanpa lemak)', 'price' => 60000, 'stock' => 30, 'unit' => 'kg', 'description' => 'Dada ayam fillet premium tanpa lemak'],
            ['name' => 'Dada Ayam Slice (iris tipis)', 'price' => 58000, 'stock' => 25, 'unit' => 'kg', 'description' => 'Dada ayam slice iris tipis'],
            ['name' => 'Dada Ayam Cube (dadu)', 'price' => 56000, 'stock' => 25, 'unit' => 'kg', 'description' => 'Dada ayam cube potongan dadu'],
            ['name' => 'Tenderloin Ayam (daging dalam)', 'price' => 65000, 'stock' => 20, 'unit' => 'kg', 'description' => 'Tenderloin ayam daging dalam'],
            ['name' => 'Dada Ayam Skinless (tanpa kulit)', 'price' => 52000, 'stock' => 30, 'unit' => 'kg', 'description' => 'Dada ayam tanpa kulit'],
            ['name' => 'Dada Ayam Boneless (tanpa tulang)', 'price' => 54000, 'stock' => 30, 'unit' => 'kg', 'description' => 'Dada ayam tanpa tulang'],
            
            // Ayam Karkas
            ['name' => 'Ayam Karkas Utuh', 'price' => 30000, 'stock' => 50, 'unit' => 'kg', 'description' => 'Ayam karkas utuh segar'],
            ['name' => 'Karkas Fresh Grade A', 'price' => 30000, 'stock' => 50, 'unit' => 'kg', 'description' => 'Karkas ayam fresh grade A'],
            ['name' => 'Karkas Fresh Grade B', 'price' => 28000, 'stock' => 45, 'unit' => 'kg', 'description' => 'Karkas ayam fresh grade B'],
            ['name' => 'Karkas Beku', 'price' => 26000, 'stock' => 60, 'unit' => 'kg', 'description' => 'Karkas ayam beku'],
            ['name' => 'Karkas 0.9 – 1.1 kg', 'price' => 29000, 'stock' => 40, 'unit' => 'kg', 'description' => 'Karkas ayam 0.9-1.1 kg'],
            ['name' => 'Karkas 1.1 – 1.3 kg', 'price' => 32000, 'stock' => 45, 'unit' => 'kg', 'description' => 'Karkas ayam 1.1-1.3 kg'],
            ['name' => 'Karkas 1.3 – 1.5 kg', 'price' => 35000, 'stock' => 40, 'unit' => 'kg', 'description' => 'Karkas ayam 1.3-1.5 kg'],
            ['name' => 'Ayam Karkas Tanpa Kepala', 'price' => 31000, 'stock' => 35, 'unit' => 'kg', 'description' => 'Karkas ayam tanpa kepala'],
            ['name' => 'Ayam Karkas Tanpa Kepala & Ceker', 'price' => 30000, 'stock' => 35, 'unit' => 'kg', 'description' => 'Karkas ayam tanpa kepala dan ceker'],
            
            // Jeroan Ayam
            ['name' => 'Hati Ayam', 'price' => 28000, 'stock' => 30, 'unit' => 'kg', 'description' => 'Hati ayam segar'],
            ['name' => 'Ampela Ayam', 'price' => 25000, 'stock' => 30, 'unit' => 'kg', 'description' => 'Ampela ayam segar'],
            ['name' => 'Jantung Ayam', 'price' => 30000, 'stock' => 25, 'unit' => 'kg', 'description' => 'Jantung ayam segar'],
            ['name' => 'Usus Ayam', 'price' => 20000, 'stock' => 20, 'unit' => 'kg', 'description' => 'Usus ayam segar'],
            ['name' => 'Paru Ayam', 'price' => 18000, 'stock' => 20, 'unit' => 'kg', 'description' => 'Paru ayam segar'],
            ['name' => 'Paket Jeroan', 'price' => 26000, 'stock' => 25, 'unit' => 'kg', 'description' => 'Paket jeroan ayam'],
            ['name' => 'Paket Jeroan Lengkap', 'price' => 30000, 'stock' => 25, 'unit' => 'kg', 'description' => 'Paket jeroan ayam lengkap'],
            
            // Ayam Beku
            ['name' => 'Ayam Utuh Beku', 'price' => 32000, 'stock' => 45, 'unit' => 'ekor', 'description' => 'Ayam utuh beku'],
            ['name' => 'Ayam Potong Beku (1 kg – 1.5 kg)', 'price' => 30000, 'stock' => 50, 'unit' => 'kg', 'description' => 'Ayam potong beku 1-1.5 kg'],
            ['name' => 'Sayap Ayam Beku', 'price' => 32000, 'stock' => 40, 'unit' => 'kg', 'description' => 'Sayap ayam beku'],
            ['name' => 'Paha Bawah Beku', 'price' => 38000, 'stock' => 35, 'unit' => 'kg', 'description' => 'Paha bawah ayam beku'],
            ['name' => 'Paha Atas Beku', 'price' => 40000, 'stock' => 35, 'unit' => 'kg', 'description' => 'Paha atas ayam beku'],
            ['name' => 'Dada Ayam Beku', 'price' => 42000, 'stock' => 30, 'unit' => 'kg', 'description' => 'Dada ayam beku'],
            ['name' => 'Fillet Ayam Beku', 'price' => 50000, 'stock' => 25, 'unit' => 'kg', 'description' => 'Fillet ayam beku'],
            ['name' => 'Kulit Ayam Beku', 'price' => 16000, 'stock' => 30, 'unit' => 'kg', 'description' => 'Kulit ayam beku'],
            ['name' => 'Kepala & Ceker Ayam Beku', 'price' => 15000, 'stock' => 25, 'unit' => 'kg', 'description' => 'Kepala dan ceker ayam beku'],
            ['name' => 'Jeroan Ayam Beku', 'price' => 24000, 'stock' => 20, 'unit' => 'kg', 'description' => 'Jeroan ayam beku'],
            
            // Produk Olahan Ayam
            ['name' => 'Nugget Ayam', 'price' => 45000, 'stock' => 40, 'unit' => 'kg', 'description' => 'Nugget ayam siap masak'],
            ['name' => 'Sosis Ayam', 'price' => 42000, 'stock' => 35, 'unit' => 'kg', 'description' => 'Sosis ayam siap masak'],
            ['name' => 'Karage / Ayam Popcorn Frozen', 'price' => 48000, 'stock' => 30, 'unit' => 'kg', 'description' => 'Karage ayam popcorn frozen'],
            ['name' => 'Chicken Wings Frozen', 'price' => 40000, 'stock' => 35, 'unit' => 'kg', 'description' => 'Chicken wings frozen'],
            
            // Obat & Vitamin Ayam
            ['name' => 'Vitamin Ayam (Vitachick, Vitamix, dll.)', 'price' => 35000, 'stock' => 50, 'unit' => 'botol', 'description' => 'Vitamin ayam untuk kesehatan'],
            ['name' => 'Antibiotik Poultry', 'price' => 45000, 'stock' => 40, 'unit' => 'botol', 'description' => 'Antibiotik untuk unggas'],
            ['name' => 'Obat Diare Ayam', 'price' => 38000, 'stock' => 45, 'unit' => 'botol', 'description' => 'Obat diare untuk ayam'],
            ['name' => 'Obat Flu Ayam', 'price' => 40000, 'stock' => 40, 'unit' => 'botol', 'description' => 'Obat flu untuk ayam'],
            ['name' => 'Probiotik Ayam', 'price' => 42000, 'stock' => 35, 'unit' => 'botol', 'description' => 'Probiotik untuk kesehatan ayam'],
            ['name' => 'Multivitamin Cair', 'price' => 36000, 'stock' => 50, 'unit' => 'botol', 'description' => 'Multivitamin cair untuk ayam'],
            ['name' => 'Disinfectant Kandang', 'price' => 55000, 'stock' => 30, 'unit' => 'liter', 'description' => 'Disinfectant untuk kandang ayam'],
            ['name' => 'Electrolyte Ayam', 'price' => 32000, 'stock' => 40, 'unit' => 'botol', 'description' => 'Electrolyte untuk ayam'],
            ['name' => 'Suplemen Penambah Nafsu Makan', 'price' => 38000, 'stock' => 35, 'unit' => 'botol', 'description' => 'Suplemen penambah nafsu makan ayam'],
            ['name' => 'Obat Antistress Ayam', 'price' => 40000, 'stock' => 30, 'unit' => 'botol', 'description' => 'Obat antistress untuk ayam'],
            
            // Pakan Ayam
            ['name' => 'Pakan Ayam Starter', 'price' => 12000, 'stock' => 100, 'unit' => 'kg', 'description' => 'Pakan ayam starter'],
            ['name' => 'Pakan Ayam Finisher', 'price' => 11000, 'stock' => 100, 'unit' => 'kg', 'description' => 'Pakan ayam finisher'],
            ['name' => 'Vaksin ND/IB', 'price' => 50000, 'stock' => 25, 'unit' => 'dosis', 'description' => 'Vaksin ND/IB untuk ayam'],
            ['name' => 'Desinfektan Air Minum', 'price' => 45000, 'stock' => 30, 'unit' => 'liter', 'description' => 'Desinfektan untuk air minum ayam'],
            ['name' => 'Mineral Feed Supplement', 'price' => 28000, 'stock' => 40, 'unit' => 'kg', 'description' => 'Suplemen mineral untuk pakan ayam'],
            
            // Peralatan Kandang
            ['name' => 'Tempat Minum Ayam (Nipple / Manual)', 'price' => 85000, 'stock' => 20, 'unit' => 'unit', 'description' => 'Tempat minum ayam nipple atau manual'],
            ['name' => 'Tempat Pakan Ayam (Feeder)', 'price' => 75000, 'stock' => 25, 'unit' => 'unit', 'description' => 'Tempat pakan ayam feeder'],
            ['name' => 'Nipple Drinker', 'price' => 5000, 'stock' => 100, 'unit' => 'buah', 'description' => 'Nipple drinker untuk ayam'],
            ['name' => 'Selang Kandang Ayam', 'price' => 15000, 'stock' => 50, 'unit' => 'meter', 'description' => 'Selang untuk kandang ayam'],
            ['name' => 'Lampu Penghangat (Brooder)', 'price' => 120000, 'stock' => 15, 'unit' => 'unit', 'description' => 'Lampu penghangat brooder untuk DOC'],
            ['name' => 'Pemanas Kandang (Gasolec / Infrared)', 'price' => 2500000, 'stock' => 5, 'unit' => 'unit', 'description' => 'Pemanas kandang gasolec atau infrared'],
            ['name' => 'Timbangan Digital Ayam', 'price' => 350000, 'stock' => 10, 'unit' => 'unit', 'description' => 'Timbangan digital untuk ayam'],
            ['name' => 'Sensor Suhu & Kelembaban Kandang', 'price' => 450000, 'stock' => 8, 'unit' => 'unit', 'description' => 'Sensor suhu dan kelembaban kandang'],
            ['name' => 'Tirai Kandang / Plastik UV', 'price' => 25000, 'stock' => 30, 'unit' => 'meter', 'description' => 'Tirai kandang atau plastik UV'],
            ['name' => 'Keranjang Ayam', 'price' => 150000, 'stock' => 12, 'unit' => 'unit', 'description' => 'Keranjang untuk transportasi ayam'],
            ['name' => 'Kandang DOC', 'price' => 500000, 'stock' => 8, 'unit' => 'unit', 'description' => 'Kandang untuk DOC (Day Old Chicken)'],
            ['name' => 'Sprayer Disinfektan', 'price' => 180000, 'stock' => 10, 'unit' => 'unit', 'description' => 'Sprayer untuk disinfektan kandang'],
            ['name' => 'Mesin Pencabut Bulu Ayam', 'price' => 1500000, 'stock' => 3, 'unit' => 'unit', 'description' => 'Mesin pencabut bulu ayam'],
            ['name' => 'Knapsack Sprayer', 'price' => 200000, 'stock' => 8, 'unit' => 'unit', 'description' => 'Knapsack sprayer untuk kandang'],
            ['name' => 'Termometer Kandang', 'price' => 85000, 'stock' => 20, 'unit' => 'unit', 'description' => 'Termometer untuk kandang ayam'],
            ['name' => 'Exhaust Fan / Blower Kandang', 'price' => 1200000, 'stock' => 5, 'unit' => 'unit', 'description' => 'Exhaust fan atau blower untuk kandang'],
            ['name' => 'Timbangan Pakan', 'price' => 280000, 'stock' => 10, 'unit' => 'unit', 'description' => 'Timbangan untuk pakan ayam'],
            
            // Robot ChickPatrol
            ['name' => 'Robot ChickPatrol F.2', 'price' => 15000000, 'stock' => 3, 'unit' => 'unit', 'description' => 'Robot monitoring kandang ayam ChickPatrol F.2'],
        ];
        
        // Buat produk baru
        foreach ($products as $productData) {
            $product = Product::factory()->create([
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'description' => $productData['description'] ?? $productData['name'],
                'price' => $productData['price'],
                'stock' => $productData['stock'],
                'unit' => $productData['unit'],
            ]);
            
            // Tambahkan gambar default untuk setiap produk
            $product->images()->create([
                'product_image_id' => (string) Str::uuid(),
                'name' => $productData['name'],
                'url' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIiB2aWV3Qm94PSIwIDAgNDAwIDQwMCI+PHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjI0IiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UHJvZHVjdDwvdGV4dD48L3N2Zz4=',
            ]);
        }
        
        $this->command->info('Berhasil membuat ' . count($products) . ' produk baru!');
    }
}
