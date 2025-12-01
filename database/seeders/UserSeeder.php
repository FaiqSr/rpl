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
        // 1. Buat satu pengguna admin khusus (gunakan firstOrCreate untuk menghindari duplicate)
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('Admin@123'),
                'role' => 'admin',
                'phone' => '081234567890',
                'address' => 'Jl. Admin No.1, Kota Contoh'
            ]
        );

        // 2. Tambahkan 3 admin baru
        User::firstOrCreate(
            ['email' => 'peternak@gmail.com'],
            [
                'name' => 'Peternak ChickPatrol',
                'password' => bcrypt('Admin@123'),
                'role' => 'admin',
                'phone' => '081234567891',
                'address' => 'Jl. Peternak No.1'
            ]
        );

        User::firstOrCreate(
            ['email' => 'chickpblog@gmail.com'],
            [
                'name' => 'ChickPBlog',
                'password' => bcrypt('Admin@123'),
                'role' => 'admin',
                'phone' => '081234567892',
                'address' => 'Jl. Blog No.1'
            ]
        );

        User::firstOrCreate(
            ['email' => 'chickseller@gmail.com'],
            [
                'name' => 'ChickPSeller',
                'password' => bcrypt('Admin@123'),
                'role' => 'admin',
                'phone' => '081234567893',
                'address' => 'Jl. Seller No.1'
            ]
        );

        // User dummy tidak dibuat lagi - user pelanggan hanya dibuat melalui registrasi
    }
}
