<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SensorReading;

class CalculateDerivativeGradient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sensor:calculate-derivative-gradient';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hitung derivative dan gradient untuk semua data sensor';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Menghitung derivative dan gradient untuk semua data sensor...");
        
        $readings = SensorReading::orderBy('recorded_at', 'asc')->get();
        $total = $readings->count();
        
        if ($total < 2) {
            $this->warn("Minimal 2 data diperlukan untuk menghitung derivative dan gradient.");
            return 0;
        }
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        $previous = null;
        $index = 0;
        
        foreach ($readings as $current) {
            if ($previous === null) {
                // Data pertama tidak punya derivative/gradient
                $current->update([
                    'derivative_amonia' => null,
                    'derivative_suhu' => null,
                    'derivative_kelembaban' => null,
                    'derivative_cahaya' => null,
                    'gradient_amonia' => null,
                    'gradient_suhu' => null,
                    'gradient_kelembaban' => null,
                    'gradient_cahaya' => null
                ]);
            } else {
                // Hitung waktu selisih dalam jam
                $hoursDiff = $current->recorded_at->diffInHours($previous->recorded_at);
                if ($hoursDiff == 0) {
                    $hoursDiff = 1; // Default 1 jam jika sama
                }
                
                // Hitung derivative (perubahan nilai)
                $derivativeAmonia = $current->amonia_ppm - $previous->amonia_ppm;
                $derivativeSuhu = $current->suhu_c - $previous->suhu_c;
                $derivativeKelembaban = $current->kelembaban_rh - $previous->kelembaban_rh;
                $derivativeCahaya = $current->cahaya_lux - $previous->cahaya_lux;
                
                // Hitung gradient (rate of change per jam)
                $gradientAmonia = $hoursDiff > 0 ? $derivativeAmonia / $hoursDiff : $derivativeAmonia;
                $gradientSuhu = $hoursDiff > 0 ? $derivativeSuhu / $hoursDiff : $derivativeSuhu;
                $gradientKelembaban = $hoursDiff > 0 ? $derivativeKelembaban / $hoursDiff : $derivativeKelembaban;
                $gradientCahaya = $hoursDiff > 0 ? $derivativeCahaya / $hoursDiff : $derivativeCahaya;
                
                $current->update([
                    'derivative_amonia' => round($derivativeAmonia, 2),
                    'derivative_suhu' => round($derivativeSuhu, 2),
                    'derivative_kelembaban' => round($derivativeKelembaban, 2),
                    'derivative_cahaya' => round($derivativeCahaya, 2),
                    'gradient_amonia' => round($gradientAmonia, 2),
                    'gradient_suhu' => round($gradientSuhu, 2),
                    'gradient_kelembaban' => round($gradientKelembaban, 2),
                    'gradient_cahaya' => round($gradientCahaya, 2)
                ]);
            }
            
            $previous = $current;
            $bar->advance();
            $index++;
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Successfully calculated derivative and gradient for {$total} records!");
        
        return 0;
    }
}

