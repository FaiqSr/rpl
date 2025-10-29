<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat satu pengguna admin khusus
        User::factory()->create([
            'username' => 'adminUser',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 2. Buat 10 pengguna biasa,
        // dan untuk setiap pengguna, buat 1 atau 2 alamat
        // (Mengasumsikan ada relasi 'addresses' di model User)
        User::factory()
            ->count(10)
            ->hasAddresses(rand(1, 2))
            ->create();
    }
}
