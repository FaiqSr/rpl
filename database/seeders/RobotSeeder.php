<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Robot;

class RobotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default robot (ChickPatrol Kamura)
        Robot::updateOrCreate(
            ['robot_id' => 'CHICKPATROL-001'],
            [
                'name' => 'Kandang Ayam',
                'model' => 'ChickPatrol Kamura',
                'location' => 'Kandang A',
                'operational_status' => 'operating',
                'battery_level' => 75,
                'last_activity_at' => now()->subMinutes(5),
                'current_position' => 'Kandang A - Area 3',
                'total_distance_today' => 2.5,
                'operating_hours_today' => 390, // 6 jam 30 menit dalam menit
                'uptime_percentage' => 99.2,
                'health_status' => [
                    'motors' => 'normal',
                    'sensors' => 'normal',
                    'battery' => 'good',
                    'navigation' => 'normal'
                ],
                'patrol_count_today' => 12
            ]
        );
    }
}
