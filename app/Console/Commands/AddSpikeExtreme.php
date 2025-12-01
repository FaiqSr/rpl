<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SensorReading;
use Carbon\Carbon;

class AddSpikeExtreme extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sensor:add-spike-extreme {count=12}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tambahkan data dengan spike ekstrem (nilai sangat ekstrem di luar range normal)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');
        
        $this->info("Menambahkan {$count} data dengan spike ekstrem...");
        
        $now = Carbon::now()->setTimezone('Asia/Jakarta');
        $latestReading = SensorReading::orderBy('recorded_at', 'desc')->first();
        $startTime = $latestReading 
            ? $latestReading->recorded_at->copy()->addHour() 
            : $now->copy()->subHours($count);
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        for ($i = 0; $i < $count; $i++) {
            $recordedAt = $startTime->copy()->addHours($i);
            
            // Generate spike ekstrem - nilai sangat ekstrem
            $spikeType = mt_rand(0, 3); // Pilih sensor mana yang spike
            
            $data = $this->generateExtremeSpike($spikeType);
            
            // Hitung derivative & gradient dari data sebelumnya
            $prevReading = SensorReading::orderBy('recorded_at', 'desc')->first();
            $derivativeGradient = $this->calculateDerivativeGradient(
                $data['amonia'],
                $data['suhu'],
                $data['kelembaban'],
                $data['cahaya'],
                $prevReading ? (float) $prevReading->amonia_ppm : null,
                $prevReading ? (float) $prevReading->suhu_c : null,
                $prevReading ? (float) $prevReading->kelembaban_rh : null,
                $prevReading ? (float) $prevReading->cahaya_lux : null,
                $recordedAt,
                $prevReading ? $prevReading->recorded_at : null
            );
            
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
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Successfully added {$count} extreme spike data. Total: " . SensorReading::count());
        
        return 0;
    }
    
    /**
     * Generate data dengan spike ekstrem untuk satu sensor
     */
    private function generateExtremeSpike($spikeType)
    {
        // Base values normal untuk sensor lain
        $baseAmonia = 15.0;
        $baseSuhu = 28.0;
        $baseKelembaban = 60.0;
        $baseCahaya = 35.0;
        
        // Random variasi kecil untuk sensor normal
        $random = mt_rand(0, 100) / 100;
        
        $data = [
            'amonia' => $baseAmonia + ($random * 5 - 2.5), // 12.5-17.5 ppm (normal)
            'suhu' => $baseSuhu + ($random * 4 - 2), // 26-30°C (normal)
            'kelembaban' => $baseKelembaban + ($random * 10 - 5), // 55-65% (normal)
            'cahaya' => $baseCahaya + ($random * 20 - 10) // 25-45 lux (normal)
        ];
        
        // Set spike ekstrem untuk satu sensor
        switch ($spikeType) {
            case 0: // Amonia spike ekstrem
                $data['amonia'] = 50.0 + (mt_rand(0, 20) / 1.0); // 50-70 ppm (sangat ekstrem!)
                break;
            case 1: // Suhu spike ekstrem
                $data['suhu'] = mt_rand(0, 1) 
                    ? (15.0 + (mt_rand(0, 3) / 1.0))  // 15-18°C (sangat dingin!)
                    : (42.0 + (mt_rand(0, 5) / 1.0)); // 42-47°C (sangat panas!)
                break;
            case 2: // Kelembaban spike ekstrem
                $data['kelembaban'] = mt_rand(0, 1)
                    ? (20.0 + (mt_rand(0, 10) / 1.0))  // 20-30% (sangat kering!)
                    : (92.0 + (mt_rand(0, 8) / 1.0));  // 92-100% (sangat lembab!)
                break;
            case 3: // Cahaya spike ekstrem
                $data['cahaya'] = mt_rand(0, 1)
                    ? (0.5 + (mt_rand(0, 2) / 1.0))    // 0.5-2.5 lux (sangat gelap!)
                    : (90.0 + (mt_rand(0, 30) / 1.0)); // 90-120 lux (sangat terang!)
                break;
        }
        
        // Round values
        $data['amonia'] = round($data['amonia'], 1);
        $data['suhu'] = round($data['suhu'], 1);
        $data['kelembaban'] = round($data['kelembaban'], 1);
        $data['cahaya'] = round($data['cahaya'], 1);
        
        return $data;
    }
    
    /**
     * Hitung derivative dan gradient dari data sebelumnya
     */
    private function calculateDerivativeGradient($amonia, $suhu, $kelembaban, $cahaya, $prevAmonia, $prevSuhu, $prevKelembaban, $prevCahaya, $currentTime, $previousTime)
    {
        // Jika tidak ada data sebelumnya, return null
        if ($previousTime === null || $prevAmonia === null) {
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

