<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SensorReading;
use Carbon\Carbon;

class SensorReadingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generate 30 data terakhir dengan variasi random yang realistic
     * Distribusi status: 33% BAIK, 33% PERHATIAN, 33% BURUK
     */
    public function run(): void
    {
        $now = Carbon::now()->setTimezone('Asia/Jakarta');
        
        // Hapus data lama jika ada
        SensorReading::truncate();
        
        // Generate 30 data terakhir (setiap jam)
        for ($i = 29; $i >= 0; $i--) {
            $recordedAt = $now->copy()->subHours($i);
            $hour = (int) $recordedAt->format('H');
            
            // Tentukan status yang ingin dihasilkan (distribusi merata)
            $statusType = $i % 3; // 0=BAIK, 1=PERHATIAN, 2=BURUK
            
            // Generate data berdasarkan status type
            $data = $this->generateSensorData($statusType, $hour);
            
            SensorReading::create([
                'amonia_ppm' => $data['amonia'],
                'suhu_c' => $data['suhu'],
                'kelembaban_rh' => $data['kelembaban'],
                'cahaya_lux' => $data['cahaya'],
                'recorded_at' => $recordedAt
            ]);
        }
        
        $this->command->info('Generated 30 sensor readings with balanced status distribution (33% BAIK, 33% PERHATIAN, 33% BURUK)');
    }
    
    /**
     * Generate sensor data berdasarkan status type dan jam
     * Threshold sesuai boiler standards:
     * - Amonia: ideal_max: 20, warn_max: 35, danger_max: 35
     * - Suhu: ideal_min: 23, ideal_max: 34, danger_low: 23, danger_high: 34
     * - Kelembaban: ideal_min: 50, ideal_max: 70, warn_high: 80, danger_high: 80
     * - Cahaya: ideal_low: 20, ideal_high: 40, warn_low: 10, warn_high: 60 (nilai aktual ratusan)
     */
    private function generateSensorData($statusType, $hour)
    {
        // Base values untuk siang/malam
        $isDaytime = ($hour >= 6 && $hour <= 18);
        $baseLight = $isDaytime ? 300 : 200;
        $baseTemp = $isDaytime ? 28 : 26;
        
        // Random factor untuk variasi
        $random = mt_rand(0, 100) / 100;
        
        switch ($statusType) {
            case 0: // BAIK - semua sensor dalam range ideal
                return [
                    'amonia' => round(8 + ($random * 12), 1), // 8-20 ppm (ideal)
                    'suhu' => round(25 + ($random * 9), 1), // 25-34°C (ideal)
                    'kelembaban' => round(52 + ($random * 18), 1), // 52-70% (ideal)
                    'cahaya' => round(10 + ($random * 50), 1) // 10-60 lux (threshold range)
                ];
                
            case 1: // PERHATIAN - 1-2 sensor di luar range ideal tapi belum danger
                $sensorToWarn = mt_rand(0, 3); // Pilih sensor mana yang warning
                
                $data = [
                    'amonia' => round(8 + ($random * 12), 1), // Default ideal
                    'suhu' => round(25 + ($random * 9), 1), // Default ideal
                    'kelembaban' => round(52 + ($random * 18), 1), // Default ideal
                    'cahaya' => round(10 + ($random * 50), 1) // Default ideal (10-60 lux)
                ];
                
                // Set satu sensor ke warning range
                switch ($sensorToWarn) {
                    case 0: // Amonia warning
                        $data['amonia'] = round(21 + ($random * 14), 1); // 21-35 ppm (warning)
                        break;
                    case 1: // Suhu warning
                        $data['suhu'] = mt_rand(0, 1) ? round(20 + ($random * 3), 1) : round(35 + ($random * 2), 1); // 20-23°C atau 35-37°C
                        break;
                    case 2: // Kelembaban warning
                        $data['kelembaban'] = mt_rand(0, 1) ? round(40 + ($random * 10), 1) : round(71 + ($random * 9), 1); // 40-50% atau 71-80%
                        break;
                    case 3: // Cahaya warning - sedikit keluar threshold (tidak ekstrem)
                        $data['cahaya'] = mt_rand(0, 1) ? round(5 + ($random * 5), 1) : round(61 + ($random * 10), 1); // 5-10 lux atau 61-70 lux
                        break;
                }
                
                return $data;
                
            case 2: // BURUK - ada sensor di range danger
                $sensorToDanger = mt_rand(0, 3); // Pilih sensor mana yang danger
                
                $data = [
                    'amonia' => round(8 + ($random * 12), 1), // Default ideal
                    'suhu' => round(25 + ($random * 9), 1), // Default ideal
                    'kelembaban' => round(52 + ($random * 18), 1), // Default ideal
                    'cahaya' => round(10 + ($random * 50), 1) // Default ideal (10-60 lux)
                ];
                
                // Set satu sensor ke danger range
                switch ($sensorToDanger) {
                    case 0: // Amonia danger
                        $data['amonia'] = round(36 + ($random * 10), 1); // 36-46 ppm (danger)
                        break;
                    case 1: // Suhu danger
                        $data['suhu'] = mt_rand(0, 1) ? round(18 + ($random * 2), 1) : round(37 + ($random * 3), 1); // 18-20°C atau 37-40°C
                        break;
                    case 2: // Kelembaban danger
                        $data['kelembaban'] = mt_rand(0, 1) ? round(30 + ($random * 10), 1) : round(81 + ($random * 10), 1); // 30-40% atau 81-91%
                        break;
                    case 3: // Cahaya danger - keluar threshold tapi tidak ekstrem
                        $data['cahaya'] = mt_rand(0, 1) ? round(1 + ($random * 4), 1) : round(71 + ($random * 15), 1); // 1-5 lux atau 71-85 lux
                        break;
                }
                
                return $data;
        }
    }
}
