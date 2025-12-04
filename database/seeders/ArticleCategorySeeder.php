<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tips Beternak Ayam',
                'slug' => 'tips-beternak',
                'description' => 'Tips dan panduan praktis untuk beternak ayam',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Kesehatan Ayam',
                'slug' => 'kesehatan',
                'description' => 'Informasi tentang kesehatan, penyakit, dan pengobatan ayam',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Pakan & Nutrisi',
                'slug' => 'pakan-nutrisi',
                'description' => 'Panduan tentang pakan, nutrisi, dan kebutuhan gizi ayam',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'name' => 'Manajemen Kandang',
                'slug' => 'manajemen-kandang',
                'description' => 'Tips pengelolaan dan manajemen kandang ayam',
                'sort_order' => 4,
                'is_active' => true
            ],
            [
                'name' => 'Monitoring Sensor',
                'slug' => 'monitoring',
                'description' => 'Panduan penggunaan teknologi monitoring dan sensor',
                'sort_order' => 5,
                'is_active' => true
            ],
            [
                'name' => 'Panduan Pemula',
                'slug' => 'panduan-pemula',
                'description' => 'Panduan lengkap untuk pemula yang baru mulai beternak ayam',
                'sort_order' => 6,
                'is_active' => true
            ]
        ];

        foreach ($categories as $cat) {
            ArticleCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}

