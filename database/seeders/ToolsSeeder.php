<?php

namespace Database\Seeders;

use App\Models\Tools;
use Illuminate\Database\Seeder;

class ToolsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat 20 tipe alat (Tools)
        // Untuk setiap tipe alat, buat 5 hingga 15 item detail
        // (Mengasumsikan ada relasi 'details' di model Tool)
        Tools::factory()
            ->count(20)
            ->hasDetails(rand(5, 15))
            ->create();
    }
}
