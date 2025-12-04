<?php

namespace Database\Seeders;

use App\Models\ThresholdProfile;
use App\Models\ThresholdValue;
use Illuminate\Database\Seeder;

class ThresholdProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default threshold values from model_metadata.json
        // Mapping: frontend format -> database format
        $defaultThresholds = [
            'amonia_ppm' => [
                'ideal_max' => 20,
                'warn_max' => 35,
                'danger_max' => 35,
            ],
            'suhu_c' => [
                'ideal_min' => 23,
                'ideal_max' => 34,
                'danger_min' => 23, // Maps from frontend danger_low
                'danger_max' => 34, // Maps from frontend danger_high
            ],
            'kelembaban_rh' => [
                'ideal_min' => 50,
                'ideal_max' => 70,
                'warn_max' => 80, // Maps from frontend warn_high
                'danger_max' => 80, // Maps from frontend danger_high
            ],
            'cahaya_lux' => [
                'ideal_min' => 20, // Maps from frontend ideal_low
                'ideal_max' => 40, // Maps from frontend ideal_high
                'warn_min' => 10, // Maps from frontend warn_low
                'warn_max' => 60, // Maps from frontend warn_high
            ],
        ];

        // Create default profile
        $defaultProfile = ThresholdProfile::firstOrCreate(
            ['profile_key' => 'default'],
            [
                'profile_name' => 'Default',
                'age_min_days' => null,
                'age_max_days' => null,
                'is_default' => true,
            ]
        );

        // Create threshold values for default profile
        foreach ($defaultThresholds as $sensorType => $values) {
            ThresholdValue::updateOrCreate(
                [
                    'profile_id' => $defaultProfile->id,
                    'sensor_type' => $sensorType,
                ],
                array_merge(['profile_id' => $defaultProfile->id, 'sensor_type' => $sensorType], $values)
            );
        }

        // Create age-based profiles (using same default values initially)
        $ageProfiles = [
            ['key' => '1-7', 'name' => '1-7 hari', 'min' => 1, 'max' => 7],
            ['key' => '8-14', 'name' => '8-14 hari', 'min' => 8, 'max' => 14],
            ['key' => '15-21', 'name' => '15-21 hari', 'min' => 15, 'max' => 21],
            ['key' => '22-28', 'name' => '22-28 hari', 'min' => 22, 'max' => 28],
            ['key' => '29+', 'name' => '29+ hari', 'min' => 29, 'max' => null],
        ];

        foreach ($ageProfiles as $ageProfile) {
            $profile = ThresholdProfile::firstOrCreate(
                ['profile_key' => $ageProfile['key']],
                [
                    'profile_name' => $ageProfile['name'],
                    'age_min_days' => $ageProfile['min'],
                    'age_max_days' => $ageProfile['max'],
                    'is_default' => false,
                ]
            );

            // Copy default threshold values to age profile
            foreach ($defaultThresholds as $sensorType => $values) {
                ThresholdValue::updateOrCreate(
                    [
                        'profile_id' => $profile->id,
                        'sensor_type' => $sensorType,
                    ],
                    array_merge(['profile_id' => $profile->id, 'sensor_type' => $sensorType], $values)
                );
            }
        }

        $this->command->info('Threshold profiles seeded successfully!');
    }
}
