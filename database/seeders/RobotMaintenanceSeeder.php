<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RobotMaintenance;
use Carbon\Carbon;

class RobotMaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create maintenance schedule for CHICKPATROL-001
        RobotMaintenance::updateOrCreate(
            [
                'robot_id' => 'CHICKPATROL-001',
                'title' => 'Service Rutin Bulanan'
            ],
            [
                'maintenance_type' => 'routine',
                'description' => 'Service rutin untuk membersihkan sensor, cek motor, dan ganti filter',
                'scheduled_date' => Carbon::now()->addDays(5),
                'status' => 'scheduled',
                'next_service_days' => 30
            ]
        );
        
        // Create completed maintenance (history)
        RobotMaintenance::updateOrCreate(
            [
                'robot_id' => 'CHICKPATROL-001',
                'title' => 'Service Rutin Bulanan (Lalu)'
            ],
            [
                'maintenance_type' => 'routine',
                'description' => 'Service rutin bulan lalu',
                'scheduled_date' => Carbon::now()->subDays(25),
                'completed_date' => Carbon::now()->subDays(25),
                'status' => 'completed',
                'cost' => 150000,
                'technician_name' => 'Teknisi A',
                'notes' => 'Semua komponen dalam kondisi baik'
            ]
        );
    }
}
