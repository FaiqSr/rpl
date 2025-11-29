<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    protected $botToken;
    protected $chatId;

    public function __construct()
    {
        // Read directly from .env file to ensure latest value (bypass config cache)
        $this->botToken = $this->getEnvValue('TELEGRAM_BOT_TOKEN');
        $this->chatId = $this->getEnvValue('TELEGRAM_CHAT_ID');
    }

    /**
     * Helper function untuk membaca nilai dari .env file langsung (bypass config cache)
     */
    protected function getEnvValue($key, $default = null)
    {
        $envFile = base_path('.env');
        if (!file_exists($envFile)) {
            return $default;
        }
        $envContent = file_get_contents($envFile);
        if ($envContent === false) {
            return $default;
        }
        if (preg_match('/^' . preg_quote($key, '/') . '\s*=\s*(.*)$/m', $envContent, $matches)) {
            $value = trim($matches[1]);
            $value = trim($value, '"\'');
            return $value;
        }
        return $default;
    }

    /**
     * Kirim notifikasi kondisi kandang ke Telegram
     * 
     * @param array $latest Nilai sensor terkini
     * @param array $status Status kandang dari ML
     * @param array $prediction_6h Prediksi 6 jam ke depan
     * @param array $anomalies List anomali yang terdeteksi
     * @param array $forecast_summary_6h Ringkasan prediksi 6 jam
     * @param array $thresholds Threshold dari database (optional)
     * @param array $history_24h History 24 jam terakhir untuk trend analysis (optional)
     * @return bool
     */
    public function sendMonitoringNotification($latest, $status, $prediction_6h, $anomalies, $forecast_summary_6h, $thresholds = [], $history_24h = [])
    {
        // Check if Telegram notifications are enabled
        // Read directly from .env file to ensure latest value (bypass config cache)
        $telegramEnabled = $this->getEnvValue('TELEGRAM_NOTIFICATIONS_ENABLED', 'true');
        
        // Handle string 'true'/'false' and boolean true/false
        if ($telegramEnabled === 'false' || $telegramEnabled === false || $telegramEnabled === '0' || $telegramEnabled === 0) {
            Log::info('Telegram notifications disabled - skipping notification', ['enabled' => $telegramEnabled]);
            return false;
        }

        if (!$this->botToken || !$this->chatId) {
            Log::warning('Telegram credentials not configured');
            return false;
        }

        try {
            // Format pesan dengan template lengkap - pass history_24h juga
            $message = $this->buildTelegramReport($latest, $status, $prediction_6h, $anomalies, $forecast_summary_6h, $thresholds, $history_24h);
            
            // Check message length (Telegram limit is 4096 characters)
            if (mb_strlen($message) > 4096) {
                Log::warning('Telegram message too long, truncating', [
                    'original_length' => mb_strlen($message),
                    'max_length' => 4096
                ]);
                // Truncate message and add note
                $message = mb_substr($message, 0, 4000) . "\n\n[Pesan dipotong karena terlalu panjang]";
            }
            
            // Escape karakter khusus untuk Markdown
            // Escape karakter yang bisa diinterpretasikan sebagai Markdown syntax
            // Tapi kita sudah menggunakan * untuk bold, jadi tidak perlu escape
            // Hanya perlu escape karakter yang bisa menyebabkan masalah
            // Escape underscore dan backtick yang tidak kita gunakan
            // Tapi karena kita tidak menggunakan underscore untuk italic, kita biarkan saja
            
            // Log message untuk debugging (hanya panjang, bukan isi)
            Log::info('Sending Telegram notification', [
                'message_length' => mb_strlen($message),
                'has_latest' => !empty($latest),
                'has_status' => !empty($status),
                'status_label' => $status['label'] ?? 'unknown'
            ]);
            
            // Kirim ke Telegram dengan retry mechanism untuk handle connection errors
            $maxRetries = 3;
            $retryDelay = 2; // seconds
            $response = null;
            $lastError = null;
            
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $response = Http::timeout(15)
                        ->retry(1, 1000) // Retry once with 1 second delay
                        ->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                            'chat_id' => $this->chatId,
                            'text' => $message,
                            'parse_mode' => 'Markdown',
                            'disable_web_page_preview' => true
                        ]);
                    
                    // If successful, break out of retry loop
                    if ($response->successful()) {
                        break;
                    }
                    
                    $lastError = [
                        'status' => $response->status(),
                        'body' => $response->json()
                    ];
                    
                    // If it's a client error (4xx), don't retry
                    if ($response->status() >= 400 && $response->status() < 500) {
                        break;
                    }
                    
                    // For server errors or connection errors, retry
                    if ($attempt < $maxRetries) {
                        Log::warning("Telegram API error, retrying (attempt {$attempt}/{$maxRetries})", $lastError);
                        sleep($retryDelay);
                    }
                } catch (\Exception $e) {
                    $lastError = [
                        'error' => $e->getMessage(),
                        'attempt' => $attempt
                    ];
                    
                    // If it's a connection error, retry
                    if (strpos($e->getMessage(), 'Connection') !== false || 
                        strpos($e->getMessage(), 'timeout') !== false ||
                        strpos($e->getMessage(), 'cURL error') !== false) {
                        if ($attempt < $maxRetries) {
                            Log::warning("Telegram connection error, retrying (attempt {$attempt}/{$maxRetries}): " . $e->getMessage());
                            sleep($retryDelay);
                            continue;
                        }
                    }
                    
                    // For other errors, don't retry
                    throw $e;
                }
            }
            
            // Check response after retries
            if ($response && $response->successful()) {
                $data = $response->json();
                if (isset($data['ok']) && $data['ok'] === true) {
                    Log::info('Telegram monitoring notification sent successfully');
                    return true;
                } else {
                    // Telegram API returned success but ok=false
                    Log::error('Telegram API returned ok=false', [
                        'response' => $data
                    ]);
                }
            } else {
                // HTTP error or all retries failed
                if ($lastError) {
                    Log::error('Failed to send Telegram notification after retries', $lastError);
                } else {
                    Log::error('Failed to send Telegram notification - HTTP Error', [
                        'status' => $response ? $response->status() : 'unknown',
                        'body' => $response ? $response->json() : 'no response'
                    ]);
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error sending Telegram notification', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);
            return false;
        }
    }

    /**
     * Build laporan Telegram sesuai format baru yang lebih sederhana
     */
    protected function buildTelegramReport($latest, $status, $prediction_6h, $anomalies, $forecast_summary_6h, $thresholds = [], $history_24h = [])
    {
        // Use thresholds from database if provided, otherwise use defaults
        $defaultThresholds = [
            'temperature' => ['ideal_min' => 23, 'ideal_max' => 34],
            'humidity' => ['ideal_min' => 50, 'ideal_max' => 70],
            'ammonia' => ['ideal_max' => 20],
            'light' => ['ideal_low' => 20, 'ideal_high' => 40, 'warn_low' => 10, 'warn_high' => 60]
        ];
        
        $effectiveThresholds = !empty($thresholds) ? $thresholds : $defaultThresholds;
        
        // Helper: Get status text untuk parameter (SAMA DENGAN WEB MONITORING)
        $getParameterStatus = function($value, $type) use ($effectiveThresholds) {
            $t = $effectiveThresholds[$type] ?? [];
            
            if ($type === 'temperature') {
                $idealMin = $t['ideal_min'] ?? 23;
                $idealMax = $t['ideal_max'] ?? 34;
                $dangerLow = $t['danger_low'] ?? 20;
                $dangerHigh = $t['danger_high'] ?? 37;
                
                if ($value >= $idealMin && $value <= $idealMax) {
                    return '🟢 Aman';
                } elseif ($value < $dangerLow || $value > $dangerHigh) {
                    return '🔴 Di Luar Batas Aman';
                } else {
                    return '⚠ Perlu Perhatian';
                }
            } elseif ($type === 'humidity') {
                $idealMin = $t['ideal_min'] ?? 50;
                $idealMax = $t['ideal_max'] ?? 70;
                $warnLow = $t['warn_low'] ?? 50;
                $warnHigh = $t['warn_high'] ?? 80;
                $dangerHigh = $t['danger_high'] ?? 80;
                
                if ($value >= $idealMin && $value <= $idealMax) {
                    return '🟢 Aman';
                } elseif ($value > $dangerHigh) {
                    return '🔴 Di Luar Batas Aman';
                } elseif ($value < $warnLow || ($value > $idealMax && $value <= $warnHigh)) {
                    return '⚠ Perlu Perhatian';
                }
            } elseif ($type === 'ammonia') {
                $idealMax = $t['ideal_max'] ?? 20;
                $warnMax = $t['warn_max'] ?? 35;
                $dangerMax = $t['danger_max'] ?? 35;
                
                if ($value <= $idealMax) {
                    return '🟢 Aman';
                } elseif ($value > $dangerMax) {
                    return '🔴 Di Luar Batas Aman';
                } elseif ($value > $warnMax) {
                    return '⚠ Perlu Perhatian';
                }
            } elseif ($type === 'light') {
                // Light: SAMA PERSIS DENGAN FRONTEND - TIDAK membagi 10 untuk status check
                // Frontend menggunakan nilai langsung (51.3, bukan 5.13) untuk status check
                $lightValue = $value; // Nilai langsung dari database (51.3, bukan 5.13)
                $idealLow = $t['ideal_low'] ?? 20;
                $idealHigh = $t['ideal_high'] ?? 40;
                $warnLow = $t['warn_low'] ?? 10;
                $warnHigh = $t['warn_high'] ?? 60;
                
                // Logika SAMA PERSIS dengan frontend getSensorStatus untuk light
                if ($lightValue >= $idealLow && $lightValue <= $idealHigh) {
                    return '🟢 Aman';
                } elseif ($lightValue < $warnLow || $lightValue > $warnHigh) {
                    return '🔴 Di Luar Batas Aman';
                } else {
                    return '⚠ Perlu Perhatian';
                }
            }
            
            return '🟢 Aman';
        };
        
        // Helper: Get trend indicator
        $getTrendIndicator = function($values) {
            if (!is_array($values) || count($values) < 2) return 'stabil';
            $first = $values[0];
            $last = $values[count($values) - 1];
            $diff = $last - $first;
            
            if ($diff > 1) return 'meningkat';
            if ($diff < -1) return 'menurun';
            return 'stabil';
        };
        
        // Helper: Check if prediction is within safe range
        $checkPredictionRisk = function($values, $type) use ($effectiveThresholds) {
            if (empty($values) || !is_array($values)) return '🟢 masih aman';
            
            $min = min($values);
            $max = max($values);
            $t = $effectiveThresholds[$type] ?? [];
            
            if ($type === 'temperature') {
                $idealMin = $t['ideal_min'] ?? 23;
                $idealMax = $t['ideal_max'] ?? 34;
                if ($min < $idealMin || $max > $idealMax) return '⚠ potensi di luar batas';
                return '🟢 masih aman';
            } elseif ($type === 'humidity') {
                $idealMin = $t['ideal_min'] ?? 50;
                $idealMax = $t['ideal_max'] ?? 70;
                if ($min < $idealMin || $max > $idealMax) return '⚠ potensi di luar batas';
                return '🟢 masih aman';
            } elseif ($type === 'ammonia') {
                $idealMax = $t['ideal_max'] ?? 20;
                if ($max > $idealMax) return '⚠ potensi di luar batas';
                return '🟢 masih aman';
            } elseif ($type === 'light') {
                $warnMin = $t['warn_low'] ?? 10;
                $warnMax = $t['warn_high'] ?? 60;
                if ($min < $warnMin || $max > $warnMax) return '⚠ potensi di luar batas';
                return '🟢 masih aman';
            }
            
            return '🟢 masih aman';
        };
        
        // Status Label
        $statusLabel = strtolower($status['label'] ?? 'tidak diketahui');
        $statusDisplay = [
            'baik' => '✅ BAIK',
            'perhatian' => '⚠ PERHATIAN',
            'buruk' => '🚨 BURUK',
            'tidak diketahui' => '❓ TIDAK DIKETAHUI'
        ];
        $statusText = $statusDisplay[$statusLabel] ?? '❓ TIDAK DIKETAHUI';
        
        // Status description
        $statusDesc = '';
        if ($statusLabel === 'baik') {
            $statusDesc = 'Semua parameter dalam kondisi optimal.';
        } elseif ($statusLabel === 'perhatian') {
            $statusDesc = 'Beberapa parameter mendekati batas aman.';
        } elseif ($statusLabel === 'buruk') {
            $statusDesc = 'Kondisi memerlukan tindakan segera.';
        } else {
            $statusDesc = 'Status tidak dapat ditentukan.';
        }
        
        // Probabilitas
        $prob = $status['probability'] ?? ['BAIK' => 0, 'PERHATIAN' => 0, 'BURUK' => 0];
        $baikProb = round(($prob['BAIK'] ?? 0) * 100, 1);
        $perhatianProb = round(($prob['PERHATIAN'] ?? 0) * 100, 1);
        $burukProb = round(($prob['BURUK'] ?? 0) * 100, 1);
        
        // Parameter Lingkungan
        // Untuk cahaya, SAMA PERSIS DENGAN FRONTEND - TIDAK membagi 10 untuk status check
        // Frontend menggunakan nilai langsung (51.3) untuk status check, bukan 5.13
        $lightValueForStatus = isset($latest['light']) ? (float)$latest['light'] : 0;
        
        $tempStatus = $getParameterStatus($latest['temperature'], 'temperature');
        $humStatus = $getParameterStatus($latest['humidity'], 'humidity');
        $ammStatus = $getParameterStatus($latest['ammonia'], 'ammonia');
        $lightStatus = $getParameterStatus($lightValueForStatus, 'light');
        
        // Prediksi 6 Jam - Gunakan forecast_summary_6h yang sudah di-generate di command (SAMA PERSIS DENGAN WEB MONITORING)
        $pred6Items = [];
        if (!empty($forecast_summary_6h) && is_array($forecast_summary_6h)) {
            // Gunakan forecast_summary yang sudah di-generate di command (SAMA PERSIS dengan web monitoring)
            foreach ($forecast_summary_6h as $forecast) {
                if (isset($forecast['metric']) && isset($forecast['summary'])) {
                    $pred6Items[] = $forecast['summary'];
                }
            }
        } else {
            $pred6Items = ['Data prediksi tidak tersedia'];
        }
        
        // History 24 Jam Terakhir - hitung trend dari data history yang sebenarnya (SAMA DENGAN WEB MONITORING)
        $history24Items = [];
        if (!empty($history_24h) && is_array($history_24h) && count($history_24h) > 0) {
            // Generate forecast summary untuk history 24 jam (SAMA DENGAN WEB MONITORING)
            $generateHistorySummary = function($series, $metric, $unit, $safeLow, $safeHigh) {
                if (empty($series) || !is_array($series)) {
                    return "{$metric}: Data tidak tersedia";
                }
                
                $numericSeries = array_values(array_filter($series, function($v) {
                    return is_numeric($v);
                }));
                
                if (empty($numericSeries)) {
                    return "{$metric}: Data tidak valid";
                }
                
                $min = min($numericSeries);
                $max = max($numericSeries);
                $firstValue = $numericSeries[0];
                $lastValue = end($numericSeries);
                $trend = $lastValue - $firstValue;
                $dir = $trend > 0.5 ? 'meningkat' : ($trend < -0.5 ? 'menurun' : 'stabil');
                $risk = ($min < $safeLow || $max > $safeHigh) ? 'potensi keluar batas aman' : 'dalam kisaran aman';
                
                return "{$metric} {$dir} → " . ($risk === 'dalam kisaran aman' ? '🟢 masih aman' : '⚠ potensi di luar batas');
            };
            
            // Extract values untuk setiap sensor
            $tempHistory = array_column($history_24h, 'temperature');
            $humHistory = array_column($history_24h, 'humidity');
            $ammHistory = array_column($history_24h, 'ammonia');
            $lightHistoryRaw = array_column($history_24h, 'light');
            // Konversi cahaya dari ratusan ke puluhan (sama seperti web monitoring)
            $lightHistory = [];
            foreach ($lightHistoryRaw as $val) {
                if (is_numeric($val)) {
                    $lightHistory[] = (float)$val / 10;
                }
            }
            
            $tempMin = $effectiveThresholds['temperature']['ideal_min'] ?? 23;
            $tempMax = $effectiveThresholds['temperature']['ideal_max'] ?? 34;
            $humMin = $effectiveThresholds['humidity']['ideal_min'] ?? 50;
            $humMax = $effectiveThresholds['humidity']['ideal_max'] ?? 70;
            $ammMax = $effectiveThresholds['ammonia']['ideal_max'] ?? 20;
            $lightMin = $effectiveThresholds['light']['ideal_low'] ?? 20;
            $lightMax = $effectiveThresholds['light']['ideal_high'] ?? 40;
            
            $history24Items = [
                $generateHistorySummary($tempHistory, 'Suhu', '°C', $tempMin, $tempMax),
                $generateHistorySummary($humHistory, 'Kelembaban', '%', $humMin, $humMax),
                $generateHistorySummary($ammHistory, 'Amoniak', 'ppm', 0, $ammMax),
                $generateHistorySummary($lightHistory, 'Cahaya', 'lux', $lightMin, $lightMax)
            ];
        } else {
            $history24Items = ['Data history tidak tersedia'];
        }
        
        // Deteksi Anomali
        $anomalyList = [];
        if (!empty($anomalies) && is_array($anomalies)) {
            foreach (array_slice($anomalies, 0, 5) as $anomaly) {
                $sensorEmoji = [
                    'temperature' => '🌡',
                    'humidity' => '💧',
                    'ammonia' => '💨',
                    'light' => '💡'
                ];
                $sensorName = [
                    'temperature' => 'Temperature',
                    'humidity' => 'Humidity',
                    'light' => 'Light'
                ];
                
                $type = $anomaly['type'] ?? 'unknown';
                $emoji = $sensorEmoji[$type] ?? '•';
                $name = $sensorName[$type] ?? ucfirst($type);
                
                $value = isset($anomaly['value']) ? (float)$anomaly['value'] : (float)($latest[$type] ?? 0);
                
                // Get unit
                $unit = '';
                if ($type === 'temperature') {
                    $unit = '°C';
                } elseif ($type === 'humidity') {
                    $unit = '%';
                } elseif ($type === 'ammonia') {
                    $unit = 'ppm';
                } elseif ($type === 'light') {
                    $unit = 'lux';
                }
                
                // Extract z-score if available
                $zScore = '';
                $message = $anomaly['message'] ?? '';
                if (preg_match('/z[=\s:]+([\d.]+)/i', $message, $zMatches)) {
                    $zScore = ' — z-score: ' . round((float)$zMatches[1], 2);
                } elseif ($type === 'humidity') {
                    // For humidity, show threshold (use ideal_min as safe threshold)
                    $threshold = $effectiveThresholds['humidity']['ideal_min'] ?? 50;
                    $zScore = ' — *batas aman: ' . $threshold . '%*';
                }
                
                // Format sesuai contoh: "• 💧 Humidity (49.8%) — *batas aman: 40%*"
                $anomalyList[] = "• {$emoji} {$name} (" . round($value, 1) . " {$unit}){$zScore}";
            }
        }
        
        // Rekomendasi
        $recommendations = [];
        if ($statusLabel === 'buruk') {
            $recommendations = [
                'SEGERA cek kondisi ayam secara langsung',
                'Tingkatkan ventilasi kandang',
                'Cek kipas dan exhaust',
                'Pastikan pakan & air minum tersedia'
            ];
        } elseif ($statusLabel === 'perhatian') {
            $recommendations = [
                'Optimalkan ventilasi kandang',
                'Cek kipas dan exhaust',
                'Pastikan pakan & air minum tersedia',
                'Pantau parameter setiap 30-60 menit'
            ];
        } else {
            $recommendations = [
                'Pertahankan kondisi lingkungan saat ini',
                'Lanjutkan jadwal pemeliharaan rutin',
                'Pastikan semua sensor berfungsi dengan baik'
            ];
        }
        
        // Build Message
        $message = "🐔 *Dashboard Monitoring Kandang Ayam*\n\n";
        $message .= "📊 *Status:* *{$statusText}*\n\n";
        $message .= "{$statusDesc}\n\n";
        $message .= "*Probabilitas Kondisi*\n\n";
        $message .= "• 🟢 Baik: *{$baikProb}%*\n";
        $message .= "• 🟡 Perhatian: *{$perhatianProb}%*\n";
        $message .= "• 🔴 Buruk: *{$burukProb}%*\n\n";
        $message .= "---\n\n";
        
        // Parameter Lingkungan
        // Untuk cahaya, SAMA PERSIS DENGAN FRONTEND - TIDAK membagi 10 untuk display
        // Frontend menampilkan nilai langsung (51.3 lux), bukan 5.13 lux
        $lightValue = isset($latest['light']) ? (float)$latest['light'] : 0;
        
        $message .= "*Parameter Lingkungan*\n\n";
        $message .= "🌡 Suhu: *" . round($latest['temperature'], 1) . "°C* — {$tempStatus}\n\n";
        $message .= "💧 Kelembaban: *" . round($latest['humidity'], 1) . "%* — {$humStatus}\n\n";
        $message .= "💨 Amoniak: *" . round($latest['ammonia'], 1) . " ppm* — {$ammStatus}\n\n";
        $message .= "💡 Cahaya: *" . round($lightValue, 1) . " lux* — {$lightStatus}\n\n";
        $message .= "---\n\n";
        
        // Prediksi Kondisi
        $message .= "📈 *Prediksi Kondisi*\n\n";
        $message .= "⏳ *6 Jam ke Depan*\n\n";
        // Sinkronisasi ringkasan prediksi 6 jam dengan dashboard
        if (!empty($forecast_summary_6h) && is_array($forecast_summary_6h)) {
            foreach ($forecast_summary_6h as $item) {
                if (isset($item['summary'])) {
                    $message .= "• {$item['summary']}\n";
                }
            }
        } else {
            foreach ($pred6Items as $item) {
                $message .= "• {$item}\n";
            }
        }
        $message .= "\n";

        // Ganti label menjadi 24 Jam ke Depan
        $message .= "🕒 *24 Jam ke Depan*\n\n";
        foreach ($history24Items as $item) {
            $message .= "• {$item}\n";
        }
        $message .= "\n";
        $message .= "---\n\n";
        
        // Deteksi Anomali
        if (!empty($anomalyList)) {
            $message .= "🚨 *Deteksi Anomali Sensor*\n\n";
            foreach ($anomalyList as $anom) {
                $message .= "{$anom}\n";
            }
            $message .= "\n---\n\n";
        }
        
        // Rekomendasi
        $message .= "📝 *Rekomendasi*\n\n";
        foreach ($recommendations as $rec) {
            $message .= "• {$rec}\n";
        }
        $message .= "\n---\n\n";
        
        // Footer
        $wibTime = now()->setTimezone('Asia/Jakarta');
        $dayNames = [
            'Monday' => 'Monday',
            'Tuesday' => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday' => 'Thursday',
            'Friday' => 'Friday',
            'Saturday' => 'Saturday',
            'Sunday' => 'Sunday'
        ];
        $dayName = $dayNames[$wibTime->format('l')] ?? $wibTime->format('l');
        
        $message .= "🕐 *Waktu Laporan:* " . $wibTime->format('d/m/Y H:i:s') . " WIB\n\n";
        $message .= "📅 *Hari:* {$dayName}\n\n";
        $message .= "📤 *Dikirim otomatis oleh ChickPatrol Monitoring System*\n\n";
        $message .= "🤖 *Powered by Machine Learning & IoT Sensors*";
        
        return $message;
    }
}
