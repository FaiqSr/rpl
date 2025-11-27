<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MachineLearningService
{
    /**
     * URL untuk ML service eksternal (Python Flask/FastAPI)
     * Set di .env: ML_SERVICE_URL=http://localhost:5000
     */
    protected $mlServiceUrl;

    /**
     * Timeout untuk request ke ML service (detik)
     * Ensemble LSTM membutuhkan waktu lebih lama, jadi timeout dinaikkan
     */
    protected $timeout = 30;

    public function __construct()
    {
        $this->mlServiceUrl = env('ML_SERVICE_URL', null);
    }

    /**
     * Mendapatkan prediksi dari ML model
     * 
     * @param array $history Data sensor history (24 jam terakhir)
     * @return array Hasil prediksi ML
     */
    public function getPredictions(array $history): array
    {
        // Jika ML service URL tidak diset, gunakan prediksi sederhana
        if (!$this->mlServiceUrl) {
            return $this->getSimplePredictions($history);
        }

        try {
            // Panggil ML service eksternal
            $response = Http::timeout($this->timeout)
                ->post($this->mlServiceUrl . '/predict', [
                    'history' => $history,
                    'features' => ['temperature', 'humidity', 'ammonia', 'light']
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Log untuk debugging
                Log::info('ML Service response received', [
                    'has_prediction_6h' => isset($data['prediction_6h']),
                    'has_prediction_24h' => isset($data['prediction_24h']),
                    'has_status' => isset($data['status']),
                    'has_anomalies' => isset($data['anomalies']),
                    'status_code' => $response->status(),
                    'has_model_name' => isset($data['model_name']),
                    'has_ml_metadata' => isset($data['ml_metadata']),
                    'model_name' => $data['model_name'] ?? $data['ml_metadata']['model_name'] ?? 'not set'
                ]);
                
                // Pastikan prediction_6h dan prediction_24h memiliki struktur yang benar
                $pred6h = $data['prediction_6h'] ?? [];
                $pred24h = $data['prediction_24h'] ?? [];
                
                // Validasi struktur: harus memiliki keys temperature, humidity, ammonia, light
                if (!is_array($pred6h) || !isset($pred6h['temperature'])) {
                    $pred6h = ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
                }
                if (!is_array($pred24h) || !isset($pred24h['temperature'])) {
                    $pred24h = ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
                }
                
                // Pastikan semua keys ada
                $requiredKeys = ['temperature', 'humidity', 'ammonia', 'light'];
                foreach ($requiredKeys as $key) {
                    if (!isset($pred6h[$key])) $pred6h[$key] = [];
                    if (!isset($pred24h[$key])) $pred24h[$key] = [];
                }
                
                return [
                    'prediction_6h' => $pred6h,
                    'prediction_24h' => $pred24h,
                    'anomalies' => $data['anomalies'] ?? [],
                    'status' => $data['status'] ?? [],
                    'forecast_summary_6h' => $data['forecast_summary_6h'] ?? [],
                    'forecast_summary_24h' => $data['forecast_summary_24h'] ?? [],
                    'ml_metadata' => [
                        'source' => 'ml_service',
                        'model_name' => $data['model_name'] ?? $data['ml_metadata']['model_name'] ?? 'Unknown',
                        'model_version' => $data['model_version'] ?? $data['ml_metadata']['model_version'] ?? '1.0',
                        'accuracy' => $data['accuracy'] ?? $data['ml_metadata']['accuracy'] ?? null,
                        'prediction_time' => $data['prediction_time'] ?? $data['ml_metadata']['prediction_time'] ?? null,
                        'confidence' => $data['confidence'] ?? $data['ml_metadata']['confidence'] ?? null,
                    ],
                    'source' => 'ml_service'
                ];
            } else {
                Log::warning('ML Service returned non-success status', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $this->mlServiceUrl . '/predict'
                ]);
                throw new \Exception('ML Service returned status ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::warning('ML Service tidak tersedia, menggunakan prediksi sederhana: ' . $e->getMessage());
            Log::warning('Exception details: ' . $e->getTraceAsString());
            // Fallback ke prediksi sederhana jika ML service gagal
            return $this->getSimplePredictions($history);
        }
    }

    /**
     * Prediksi sederhana sebagai fallback
     */
    protected function getSimplePredictions(array $history): array
    {
        $temps = array_column($history, 'temperature');
        $humids = array_column($history, 'humidity');
        $ammonias = array_column($history, 'ammonia');
        $lights = array_column($history, 'light');

        $predict = function ($arr) {
            $recent = array_slice($arr, -6);
            $deltas = [];
            for ($i = 1; $i < count($recent); $i++) {
                $deltas[] = $recent[$i] - $recent[$i-1];
            }
            $avgDelta = count($deltas) ? array_sum($deltas) / count($deltas) : 0;
            $base = end($arr);
            $out = [];
            for ($h = 1; $h <= 6; $h++) {
                $out[] = round($base + $avgDelta * $h, 2);
            }
            return $out;
        };

        $predict24 = function ($arr) {
            $recent = array_slice($arr, -6);
            $deltas = [];
            for ($i = 1; $i < count($recent); $i++) {
                $deltas[] = $recent[$i] - $recent[$i-1];
            }
            $avgDelta = count($deltas) ? array_sum($deltas) / count($deltas) : 0;
            $base = end($arr);
            $out = [];
            for ($h = 1; $h <= 24; $h++) {
                $factor = $h <= 12 ? 1 : 0.5;
                $out[] = round($base + $avgDelta * $h * $factor, 2);
            }
            return $out;
        };

        return [
            'prediction_6h' => [
                'temperature' => $predict($temps),
                'humidity' => $predict($humids),
                'ammonia' => $predict($ammonias),
                'light' => $predict($lights)
            ],
            'prediction_24h' => [
                'temperature' => $predict24($temps),
                'humidity' => $predict24($humids),
                'ammonia' => $predict24($ammonias),
                'light' => $predict24($lights)
            ],
            'anomalies' => $this->detectAnomalies($history),
            'status' => $this->classifyStatus(end($history), []), // Threshold akan diupdate di routes/web.php
            'ml_metadata' => [
                'model_name' => 'Simple Linear Extrapolation',
                'model_version' => '1.0',
                'accuracy' => null,
                'prediction_time' => null,
                'confidence' => 'low',
            ],
            'source' => 'fallback'
        ];
    }

    /**
     * Deteksi anomali dari data sensor
     */
    protected function detectAnomalies(array $history): array
    {
        $anomalies = [];
        foreach ($history as $point) {
            if ($point['temperature'] > 30 || $point['temperature'] < 20) {
                $anomalies[] = [
                    'type' => 'temperature',
                    'value' => $point['temperature'],
                    'time' => $point['time'],
                    'message' => 'Suhu di luar rentang optimal (20-30°C)',
                    'severity' => $point['temperature'] > 32 || $point['temperature'] < 18 ? 'critical' : 'warning'
                ];
            }
            if ($point['humidity'] < 55 || $point['humidity'] > 75) {
                $anomalies[] = [
                    'type' => 'humidity',
                    'value' => $point['humidity'],
                    'time' => $point['time'],
                    'message' => 'Kelembaban di luar rentang optimal (55-75%)',
                    'severity' => 'warning'
                ];
            }
            if ($point['ammonia'] > 25) {
                $anomalies[] = [
                    'type' => 'ammonia',
                    'value' => $point['ammonia'],
                    'time' => $point['time'],
                    'message' => 'Kadar amoniak tinggi, cek ventilasi',
                    'severity' => $point['ammonia'] > 30 ? 'critical' : 'warning'
                ];
            }
            if ($point['light'] < 200 && (int)date('G', strtotime($point['time'])) >= 8 && (int)date('G', strtotime($point['time'])) <= 17) {
                $anomalies[] = [
                    'type' => 'light',
                    'value' => $point['light'],
                    'time' => $point['time'],
                    'message' => 'Cahaya kurang optimal pada siang hari',
                    'severity' => 'warning'
                ];
            }
        }
        return $anomalies;
    }

    /**
     * Klasifikasi status lingkungan
     */
    protected function classifyStatus(array $latest): array
    {
        $issues = 0;
        if ($latest['temperature'] < 20 || $latest['temperature'] > 30) $issues++;
        if ($latest['humidity'] < 55 || $latest['humidity'] > 75) $issues++;
        if ($latest['ammonia'] > 25) $issues++;
        if ($latest['light'] < 200 && (int)date('G') >= 8 && (int)date('G') <= 17) $issues++;

        // Probabilitas dummy untuk fallback (karena bukan dari model ML sebenarnya)
        // Confidence rendah untuk fallback
        $confidence = 0.5; // 50% confidence untuk fallback
        
        if ($issues === 0) {
            return [
                'label' => 'baik',
                'severity' => 'normal',
                'message' => 'Semua parameter lingkungan dalam kondisi optimal. Kandang siap untuk pertumbuhan ayam yang sehat.',
                'confidence' => $confidence,
                'probability' => [
                    'BAIK' => 0.7,
                    'PERHATIAN' => 0.25,
                    'BURUK' => 0.05
                ]
            ];
        }
        // Hanya 3 status: baik, perhatian, buruk
        if ($issues === 1 || $issues === 2) {
            return [
                'label' => 'perhatian',
                'severity' => 'warning',
                'message' => 'Beberapa parameter lingkungan perlu diperhatikan. Lakukan pengecekan ventilasi, suhu, dan kelembaban. Periksa juga ketersediaan pakan dan air minum.',
                'confidence' => $confidence,
                'probability' => [
                    'BAIK' => 0.1,
                    'PERHATIAN' => 0.75,
                    'BURUK' => 0.15
                ]
            ];
        }
        return [
            'label' => 'buruk',
            'severity' => 'critical',
            'message' => 'Kondisi lingkungan tidak optimal dan berpotensi membahayakan kesehatan ayam. Segera lakukan penyesuaian suhu, kelembaban, ventilasi, atau pencahayaan. Jika perlu, hubungi dokter hewan.',
            'confidence' => $confidence,
            'probability' => [
                'BAIK' => 0.05,
                'PERHATIAN' => 0.3,
                'BURUK' => 0.65
            ]
        ];
    }

    /**
     * Test koneksi ke ML service
     */
    public function testConnection(): bool
    {
        if (!$this->mlServiceUrl) {
            Log::info('ML Service URL not configured');
            return false;
        }

        try {
            $response = Http::timeout(5)->get($this->mlServiceUrl . '/health');
            if ($response->successful()) {
                $data = $response->json();
                return isset($data['status']) && $data['status'] === 'ok' && 
                       isset($data['models_loaded']) && $data['models_loaded'] === true;
            }
            return false;
        } catch (\Exception $e) {
            Log::warning('ML Service connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}

