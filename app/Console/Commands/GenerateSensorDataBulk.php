<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SensorReading;
use Carbon\Carbon;

class GenerateSensorDataBulk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sensor:generate-bulk {count=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate bulk sensor data sampai mencapai jumlah yang ditentukan (default: 500)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetCount = (int) $this->argument('count');
        $currentCount = SensorReading::count();
        
        if ($currentCount >= $targetCount) {
            $this->info("Data sudah mencapai atau melebihi {$targetCount} data. Current: {$currentCount}");
            return 0;
        }
        
        $needed = $targetCount - $currentCount;
        $this->info("Generating {$needed} sensor data...");
        
        $now = Carbon::now()->setTimezone('Asia/Jakarta');
        $bar = $this->output->createProgressBar($needed);
        $bar->start();
        
        // Generate data mulai dari yang paling lama (jika ada) atau dari 500 jam yang lalu
        $latestReading = SensorReading::orderBy('recorded_at', 'desc')->first();
        $startTime = $latestReading 
            ? $latestReading->recorded_at->copy()->subHour() 
            : $now->copy()->subHours($needed);
        
        // Initialize smooth noise untuk setiap sensor
        $noiseTemp = 0.0;
        $noiseHumidity = 0.0;
        $noiseAmmonia = 0.0;
        $noiseLight = 0.0;
        
        // Previous values untuk dependency calculation
        $prevTemp = $latestReading ? (float) $latestReading->suhu_c : 28.0;
        $prevHumidity = $latestReading ? (float) $latestReading->kelembaban_rh : 60.0;
        $prevAmmonia = $latestReading ? (float) $latestReading->amonia_ppm : 15.0;
        $prevLight = $latestReading ? (float) $latestReading->cahaya_lux : 35.0;
        
        for ($i = 0; $i < $needed; $i++) {
            $recordedAt = $startTime->copy()->addHours($i);
            $data = $this->generateSmoothSensorData(
                $recordedAt, 
                $i,
                $noiseTemp,
                $noiseHumidity,
                $noiseAmmonia,
                $noiseLight,
                $prevTemp,
                $prevHumidity,
                $prevAmmonia,
                $prevLight
            );
            
            // Hitung derivative & gradient dari data sebelumnya
            $derivativeGradient = $this->calculateDerivativeGradient(
                $data['amonia'],
                $data['suhu'],
                $data['kelembaban'],
                $data['cahaya'],
                $prevTemp,
                $prevHumidity,
                $prevAmmonia,
                $prevLight,
                $recordedAt,
                $latestReading ? $latestReading->recorded_at : null
            );
            
            // Update noise untuk next iteration (smooth noise)
            $noiseTemp = $data['noise_temp'];
            $noiseHumidity = $data['noise_humidity'];
            $noiseAmmonia = $data['noise_ammonia'];
            $noiseLight = $data['noise_light'];
            
            // Update previous values
            $prevTemp = $data['suhu'];
            $prevHumidity = $data['kelembaban'];
            $prevAmmonia = $data['amonia'];
            $prevLight = $data['cahaya'];
            
            SensorReading::create([
                'amonia_ppm' => $data['amonia'],
                'suhu_c' => $data['suhu'],
                'kelembaban_rh' => $data['kelembaban'],
                'cahaya_lux' => $data['cahaya'],
                'recorded_at' => $recordedAt,
                'derivative_amonia' => $derivativeGradient['derivative_amonia'],
                'derivative_suhu' => $derivativeGradient['derivative_suhu'],
                'derivative_kelembaban' => $derivativeGradient['derivative_kelembaban'],
                'derivative_cahaya' => $derivativeGradient['derivative_cahaya'],
                'gradient_amonia' => $derivativeGradient['gradient_amonia'],
                'gradient_suhu' => $derivativeGradient['gradient_suhu'],
                'gradient_kelembaban' => $derivativeGradient['gradient_kelembaban'],
                'gradient_cahaya' => $derivativeGradient['gradient_cahaya']
            ]);
            
            // Update latest reading untuk next iteration
            $latestReading = SensorReading::orderBy('recorded_at', 'desc')->first();
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Successfully generated {$needed} sensor data. Total: " . SensorReading::count());
        
        return 0;
    }
    
    /**
     * Generate sensor data dengan pola smooth, natural, dan dependency antar fitur
     * Sama seperti RegenerateSensorData untuk konsistensi
     */
    private function generateSmoothSensorData($timestamp, $index, &$noiseTemp, &$noiseHumidity, &$noiseAmmonia, &$noiseLight, $prevTemp, $prevHumidity, $prevAmmonia, $prevLight)
    {
        $hour = (int) $timestamp->format('H');
        
        // Pola harian menggunakan sinusoidal (24 jam cycle)
        $hourRad = ($hour / 24) * 2 * M_PI;
        
        // ========== BASE VALUES ==========
        $baseTemp = 28.0;
        $baseHumidity = 60.0;
        $baseAmmonia = 15.0;
        $baseLight = 35.0;
        
        // ========== SMOOTH NOISE (wajib untuk LSTM) ==========
        // noise_t = noise_(t-1) + random(-0.2, 0.2)
        $noiseTemp += (mt_rand(-20, 20) / 100); // -0.2 to 0.2
        $noiseHumidity += (mt_rand(-20, 20) / 100);
        $noiseAmmonia += (mt_rand(-20, 20) / 100);
        $noiseLight += (mt_rand(-20, 20) / 100);
        
        // Clamp noise agar tidak terlalu besar (max ±5)
        $noiseTemp = max(-5.0, min(5.0, $noiseTemp));
        $noiseHumidity = max(-5.0, min(5.0, $noiseHumidity));
        $noiseAmmonia = max(-5.0, min(5.0, $noiseAmmonia));
        $noiseLight = max(-5.0, min(5.0, $noiseLight));
        
        // ========== POLA HARIAN (SINUSOIDAL) ==========
        
        // Temperature: naik siang (jam 12-14), turun malam
        // phase = -π/2 untuk shift peak ke siang
        $tempDailyPattern = 4.0 * sin($hourRad - M_PI/2);
        
        // Humidity: invers dari suhu, turun saat suhu naik
        $humidityDailyPattern = -10.0 * sin($hourRad - M_PI/2);
        
        // Ammonia: naik sedikit malam, turun siang
        // phase = π untuk shift peak ke malam
        $ammoniaDailyPattern = 3.0 * sin($hourRad + M_PI);
        
        // Light: puncak siang (jam 10-14)
        // phase = -π/2 untuk shift peak ke siang
        $lightDailyPattern = 22.0 * sin($hourRad - M_PI/2);
        
        // ========== CALCULATE INITIAL VALUES ==========
        $temp = $baseTemp + $tempDailyPattern + $noiseTemp;
        $humidity = $baseHumidity + $humidityDailyPattern + $noiseHumidity;
        $ammonia = $baseAmmonia + $ammoniaDailyPattern + $noiseAmmonia;
        $light = $baseLight + $lightDailyPattern + $noiseLight;
        
        // ========== DEPENDENCY ANTAR FITUR (bertahap) ==========
        
        // Step 1: Humidity turun saat temperature naik
        $humidity -= 0.6 * ($temp - 28.0);
        
        // Step 2: Ammonia naik saat humidity tinggi
        $ammonia += 0.1 * ($humidity - 60.0);
        
        // Step 3: Temperature naik saat light naik
        $temp += 0.015 * ($light - 35.0);
        
        // Re-apply humidity dependency setelah temperature berubah
        $humidity -= 0.6 * ($temp - 28.0);
        
        // ========== KONDISI BURUK (10% dari data) ==========
        // Setiap 10 data = 1 BURUK untuk memastikan ada klasifikasi BURUK
        $shouldBeBad = ($index % 10 == 0);
        $badSensor = null;
        
        if ($shouldBeBad) {
            // Pilih sensor mana yang akan BURUK secara random
            $badSensor = mt_rand(0, 3);
        }
        
        // ========== SMOOTHING JIKA KELUAR RANGE (bukan clamp) ==========
        // Skip smoothing untuk sensor yang akan di-set BURUK
        
        // Temperature: 23-34°C (skip jika akan di-set BURUK)
        if ($badSensor !== 1) {
            if ($temp < 23.0) {
                $temp = 23.0 + ($temp - 23.0) * 0.3; // Smooth pull back
            } elseif ($temp > 34.0) {
                $temp = 34.0 + ($temp - 34.0) * 0.3; // Smooth pull back
            }
        }
        
        // Humidity: 50-75% (skip jika akan di-set BURUK)
        if ($badSensor !== 2) {
            if ($humidity < 50.0) {
                $humidity = 50.0 + ($humidity - 50.0) * 0.3;
            } elseif ($humidity > 75.0) {
                $humidity = 75.0 + ($humidity - 75.0) * 0.3;
            }
        }
        
        // Ammonia: 10-30 ppm (skip jika akan di-set BURUK)
        if ($badSensor !== 0) {
            if ($ammonia < 10.0) {
                $ammonia = 10.0 + ($ammonia - 10.0) * 0.3;
            } elseif ($ammonia > 30.0) {
                $ammonia = 30.0 + ($ammonia - 30.0) * 0.3;
            }
        }
        
        // Light: 10-100 lux (skip jika akan di-set BURUK)
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
            'suhu' => $temp,
            'kelembaban' => $humidity,
            'amonia' => $ammonia,
            'cahaya' => $light,
            'noise_temp' => $noiseTemp,
            'noise_humidity' => $noiseHumidity,
            'noise_ammonia' => $noiseAmmonia,
            'noise_light' => $noiseLight
        ];
    }
    
    /**
     * Hitung derivative dan gradient dari data sebelumnya
     */
    private function calculateDerivativeGradient($amonia, $suhu, $kelembaban, $cahaya, $prevAmonia, $prevSuhu, $prevKelembaban, $prevCahaya, $currentTime, $previousTime)
    {
        // Jika tidak ada data sebelumnya, return null
        if ($previousTime === null) {
            return [
                'derivative_amonia' => null,
                'derivative_suhu' => null,
                'derivative_kelembaban' => null,
                'derivative_cahaya' => null,
                'gradient_amonia' => null,
                'gradient_suhu' => null,
                'gradient_kelembaban' => null,
                'gradient_cahaya' => null
            ];
        }
        
        // Hitung waktu selisih dalam jam
        $hoursDiff = $currentTime->diffInHours($previousTime);
        if ($hoursDiff == 0) {
            $hoursDiff = 1; // Default 1 jam jika sama
        }
        
        // Hitung derivative (perubahan nilai)
        $derivativeAmonia = $amonia - $prevAmonia;
        $derivativeSuhu = $suhu - $prevSuhu;
        $derivativeKelembaban = $kelembaban - $prevKelembaban;
        $derivativeCahaya = $cahaya - $prevCahaya;
        
        // Hitung gradient (rate of change per jam)
        $gradientAmonia = $hoursDiff > 0 ? $derivativeAmonia / $hoursDiff : $derivativeAmonia;
        $gradientSuhu = $hoursDiff > 0 ? $derivativeSuhu / $hoursDiff : $derivativeSuhu;
        $gradientKelembaban = $hoursDiff > 0 ? $derivativeKelembaban / $hoursDiff : $derivativeKelembaban;
        $gradientCahaya = $hoursDiff > 0 ? $derivativeCahaya / $hoursDiff : $derivativeCahaya;
        
        return [
            'derivative_amonia' => round($derivativeAmonia, 2),
            'derivative_suhu' => round($derivativeSuhu, 2),
            'derivative_kelembaban' => round($derivativeKelembaban, 2),
            'derivative_cahaya' => round($derivativeCahaya, 2),
            'gradient_amonia' => round($gradientAmonia, 2),
            'gradient_suhu' => round($gradientSuhu, 2),
            'gradient_kelembaban' => round($gradientKelembaban, 2),
            'gradient_cahaya' => round($gradientCahaya, 2)
        ];
    }
}
