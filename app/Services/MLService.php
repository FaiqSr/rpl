<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MLService
{
    private $apiUrl;

    public function __construct()
    {
        // URL Python API (sesuaikan dengan port Anda)
        $this->apiUrl = env('ML_API_URL', env('ML_SERVICE_URL', 'http://127.0.0.1:5000'));
    }

    /**
     * Klasifikasi status kandang
     */
    public function classifyStatus($amonia, $suhu, $kelembaban, $cahaya)
    {
        try {
            $response = Http::timeout(10)->post("{$this->apiUrl}/api/classify", [
                'amonia_ppm' => $amonia,
                'suhu_c' => $suhu,
                'kelembaban_rh' => $kelembaban,
                'cahaya_lux' => $cahaya
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('ML API Error: ' . $response->body());
            return ['success' => false, 'error' => 'ML API tidak merespon'];
        } catch (\Exception $e) {
            Log::error('ML Service Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Deteksi anomali
     */
    public function detectAnomaly($amonia, $suhu, $kelembaban, $cahaya)
    {
        try {
            $response = Http::timeout(10)->post("{$this->apiUrl}/api/detect-anomaly", [
                'amonia_ppm' => $amonia,
                'suhu_c' => $suhu,
                'kelembaban_rh' => $kelembaban,
                'cahaya_lux' => $cahaya
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            return ['success' => false, 'error' => 'ML API tidak merespon'];
        } catch (\Exception $e) {
            Log::error('ML Service Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Prediksi nilai sensor berikutnya
     */
    public function predictSensor($history)
    {
        try {
            $response = Http::timeout(30)->post("{$this->apiUrl}/api/predict", [
                'history' => $history
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            return ['success' => false, 'error' => 'ML API tidak merespon'];
        } catch (\Exception $e) {
            Log::error('ML Service Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Analisis lengkap (semua model)
     */
    public function analyzeAll($current, $history = [])
    {
        try {
            $response = Http::timeout(30)->post("{$this->apiUrl}/api/analyze", [
                'current' => $current,
                'history' => $history
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            return ['success' => false, 'error' => 'ML API tidak merespon'];
        } catch (\Exception $e) {
            Log::error('ML Service Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if ML API is running
     */
    public function healthCheck()
    {
        try {
            $response = Http::timeout(5)->get("{$this->apiUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}

