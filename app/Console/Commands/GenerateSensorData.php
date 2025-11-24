<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SensorReading;
use Carbon\Carbon;

class GenerateSensorData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sensor:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate real-time sensor data dengan variasi random yang realistic sesuai threshold boiler';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now()->setTimezone('Asia/Jakarta');
        $hour = (int) $now->format('H');
        
        // Cek apakah data untuk jam ini sudah ada
        $existing = SensorReading::where('recorded_at', '>=', $now->copy()->startOfHour())
            ->where('recorded_at', '<=', $now->copy()->endOfHour())
            ->first();
            
        if ($existing) {
            $this->info('Data untuk jam ini sudah ada. Skipping...');
            return 0;
        }
        
        // Generate data dengan pola smooth dan natural (sama seperti RegenerateSensorData)
        $data = $this->generateSensorData($hour);
        
        SensorReading::create([
            'amonia_ppm' => $data['amonia'],
            'suhu_c' => $data['suhu'],
            'kelembaban_rh' => $data['kelembaban'],
            'cahaya_lux' => $data['cahaya'],
            'recorded_at' => $now->copy()->startOfHour()
        ]);
        
        $this->info("Generated sensor data for {$now->format('Y-m-d H:00')}");
        $this->info("  Amonia: {$data['amonia']} ppm | Suhu: {$data['suhu']}°C | Kelembaban: {$data['kelembaban']}% | Cahaya: {$data['cahaya']} lux");
        
        return 0;
    }
    
    /**
     * Generate sensor data dengan pola smooth dan natural (sama seperti RegenerateSensorData)
     * Menggunakan base values + sinusoidal pattern + smooth noise + dependency
     */
    private function generateSensorData($hour)
    {
        // Get previous values untuk smooth noise dan dependency
        $latest = SensorReading::orderBy('recorded_at', 'desc')->first();
        
        // Initialize noise dari previous atau 0
        $noiseTemp = 0.0;
        $noiseHumidity = 0.0;
        $noiseAmmonia = 0.0;
        $noiseLight = 0.0;
        
        $prevTemp = $latest ? (float) $latest->suhu_c : 28.0;
        $prevHumidity = $latest ? (float) $latest->kelembaban_rh : 60.0;
        $prevAmmonia = $latest ? (float) $latest->amonia_ppm : 15.0;
        $prevLight = $latest ? (float) $latest->cahaya_lux : 35.0;
        
        // Gunakan logika yang sama dengan RegenerateSensorData
        $hourRad = ($hour / 24) * 2 * M_PI;
        
        // ========== SMOOTH NOISE ==========
        $noiseTemp += (mt_rand(-20, 20) / 100);
        $noiseHumidity += (mt_rand(-20, 20) / 100);
        $noiseAmmonia += (mt_rand(-20, 20) / 100);
        $noiseLight += (mt_rand(-20, 20) / 100);
        
        $noiseTemp = max(-5.0, min(5.0, $noiseTemp));
        $noiseHumidity = max(-5.0, min(5.0, $noiseHumidity));
        $noiseAmmonia = max(-5.0, min(5.0, $noiseAmmonia));
        $noiseLight = max(-5.0, min(5.0, $noiseLight));
        
        // ========== BASE VALUES ==========
        $baseTemp = 28.0;
        $baseHumidity = 60.0;
        $baseAmmonia = 15.0;
        $baseLight = 35.0;
        
        // ========== POLA HARIAN ==========
        $tempDailyPattern = 4.0 * sin($hourRad - M_PI/2);
        $humidityDailyPattern = -10.0 * sin($hourRad - M_PI/2);
        $ammoniaDailyPattern = 3.0 * sin($hourRad + M_PI);
        $lightDailyPattern = 22.0 * sin($hourRad - M_PI/2);
        
        // ========== CALCULATE INITIAL VALUES ==========
        $temp = $baseTemp + $tempDailyPattern + $noiseTemp;
        $humidity = $baseHumidity + $humidityDailyPattern + $noiseHumidity;
        $ammonia = $baseAmmonia + $ammoniaDailyPattern + $noiseAmmonia;
        $light = $baseLight + $lightDailyPattern + $noiseLight;
        
        // ========== DEPENDENCY ANTAR FITUR ==========
        $humidity -= 0.6 * ($temp - 28.0);
        $ammonia += 0.1 * ($humidity - 60.0);
        $temp += 0.015 * ($light - 35.0);
        $humidity -= 0.6 * ($temp - 28.0);
        
        // ========== KONDISI BURUK (5% chance untuk real-time) ==========
        // Untuk real-time, hanya 5% chance menghasilkan BURUK agar tidak terlalu sering
        $shouldBeBad = (mt_rand(1, 100) <= 5);
        $badSensor = null;
        
        if ($shouldBeBad) {
            // Pilih sensor mana yang akan BURUK secara random
            $badSensor = mt_rand(0, 3);
        }
        
        // ========== SMOOTHING JIKA KELUAR RANGE ==========
        // Skip smoothing untuk sensor yang akan di-set BURUK
        
        if ($badSensor !== 1) {
            if ($temp < 23.0) {
                $temp = 23.0 + ($temp - 23.0) * 0.3;
            } elseif ($temp > 34.0) {
                $temp = 34.0 + ($temp - 34.0) * 0.3;
            }
        }
        
        if ($badSensor !== 2) {
            if ($humidity < 50.0) {
                $humidity = 50.0 + ($humidity - 50.0) * 0.3;
            } elseif ($humidity > 75.0) {
                $humidity = 75.0 + ($humidity - 75.0) * 0.3;
            }
        }
        
        if ($badSensor !== 0) {
            if ($ammonia < 10.0) {
                $ammonia = 10.0 + ($ammonia - 10.0) * 0.3;
            } elseif ($ammonia > 30.0) {
                $ammonia = 30.0 + ($ammonia - 30.0) * 0.3;
            }
        }
        
        if ($badSensor !== 3) {
            if ($light < 10.0) {
                $light = 10.0 + ($light - 10.0) * 0.3;
            } elseif ($light > 100.0) {
                $light = 100.0 + ($light - 100.0) * 0.3;
            }
        }
        
        // ========== SET KONDISI BURUK SETELAH SMOOTHING ==========
        if ($shouldBeBad && $badSensor !== null) {
            switch ($badSensor) {
                case 0: // Amonia BURUK (>35 ppm)
                    $ammonia = 36.0 + (mt_rand(0, 10) / 1.0); // 36-46 ppm
                    break;
                case 1: // Suhu BURUK (<23°C atau >34°C)
                    $temp = mt_rand(0, 1) 
                        ? (18.0 + (mt_rand(0, 2) / 1.0))  // 18-20°C (terlalu dingin)
                        : (37.0 + (mt_rand(0, 3) / 1.0)); // 37-40°C (terlalu panas)
                    break;
                case 2: // Kelembaban BURUK (<40% atau >80%)
                    $humidity = mt_rand(0, 1)
                        ? (30.0 + (mt_rand(0, 10) / 1.0))  // 30-40% (terlalu kering)
                        : (81.0 + (mt_rand(0, 10) / 1.0)); // 81-91% (terlalu lembab)
                    break;
                case 3: // Cahaya BURUK (<10 lux atau >60 lux, ekstrem)
                    $light = mt_rand(0, 1)
                        ? (1.0 + (mt_rand(0, 4) / 1.0))   // 1-5 lux (terlalu gelap)
                        : (71.0 + (mt_rand(0, 15) / 1.0)); // 71-85 lux (terlalu terang)
                    break;
            }
        }
        
        // Final clamp - izinkan nilai BURUK tapi tetap dalam batas realistis
        $temp = max(18.0, min(40.0, round($temp, 1)));        // Izinkan 18-40°C (termasuk BURUK)
        $humidity = max(30.0, min(91.0, round($humidity, 1))); // Izinkan 30-91% (termasuk BURUK)
        $ammonia = max(10.0, min(46.0, round($ammonia, 1)));   // Izinkan 10-46 ppm (termasuk BURUK)
        $light = max(1.0, min(85.0, round($light, 1)));        // Izinkan 1-85 lux (termasuk BURUK)
        
        return [
            'amonia' => $ammonia,
            'suhu' => $temp,
            'kelembaban' => $humidity,
            'cahaya' => $light
        ];
    }
}
