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
     * @return bool
     */
    public function sendMonitoringNotification($latest, $status, $prediction_6h, $anomalies, $forecast_summary_6h)
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
            // Format pesan dengan template lengkap
            $message = $this->buildTelegramReport($latest, $status, $prediction_6h, $anomalies, $forecast_summary_6h);
            
            // Ganti karakter khusus yang tidak didukung Telegram HTML
            // Telegram HTML tidak mendukung degree symbol (°), jadi ganti dengan &deg;
            $message = str_replace('°C', '&deg;C', $message);
            $message = str_replace('°', '&deg;', $message);
            
            // Karena format baru tidak menggunakan HTML tags, kita tidak perlu escape
            // Tapi tetap perlu escape karakter HTML yang berbahaya
            // Escape & terlebih dahulu (sebelum &amp;)
            $message = str_replace('&', '&amp;', $message);
            // Kembalikan &deg; yang sudah benar
            $message = str_replace('&amp;deg;', '&deg;', $message);
            $message = str_replace('&amp;amp;', '&amp;', $message);
            
            // Escape < dan > yang bukan bagian dari HTML tag yang valid
            // Karena format baru tidak menggunakan HTML tags, kita bisa escape semua
            $message = str_replace('<', '&lt;', $message);
            $message = str_replace('>', '&gt;', $message);
            
            // Kirim ke Telegram
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['ok']) && $data['ok'] === true) {
                    Log::info('Telegram monitoring notification sent successfully');
                    return true;
                }
            }

            Log::error('Failed to send Telegram notification', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Error sending Telegram notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Build laporan Telegram lengkap sesuai template baru
     */
    protected function buildTelegramReport($latest, $status, $prediction_6h, $anomalies, $forecast_summary_6h)
    {
        // Helper functions
        $getGaugeBar = function($percentage) {
            $filled = round($percentage / 10);
            $empty = 10 - $filled;
            return str_repeat('▰', $filled) . str_repeat('▱', $empty);
        };
        
        $getStatusText = function($value, $type) {
            $thresholds = [
                'temperature' => ['min' => 23, 'max' => 34],
                'humidity' => ['min' => 50, 'max' => 70],
                'ammonia' => ['max' => 20],
                'light' => ['min' => 10, 'max' => 60]
            ];
            
            $t = $thresholds[$type] ?? [];
            
            if ($type === 'temperature') {
                if ($value < $t['min'] || $value > $t['max']) return 'Tidak aman';
                return 'Aman';
            } elseif ($type === 'humidity') {
                if ($value < $t['min'] || $value > $t['max']) return 'Tidak aman';
                return 'Aman';
            } elseif ($type === 'ammonia') {
                if ($value > $t['max']) return 'Tidak aman';
                return 'Aman';
            } elseif ($type === 'light') {
                if ($value < $t['min'] || $value > $t['max']) return 'Tidak aman';
                return 'Aman';
            }
            
            return 'Aman';
        };
        
        $getTrendIndicator = function($values) {
            if (!is_array($values) || count($values) < 2) return 'stabil';
            $first = $values[0];
            $last = $values[count($values) - 1];
            $diff = $last - $first;
            
            if ($diff > 1) return 'meningkat';
            if ($diff < -1) return 'menurun';
            return 'stabil';
        };
        
        // Status Kandang
        $statusLabel = $status['label'] ?? 'tidak diketahui';
        $statusEmoji = [
            'baik' => '✅',
            'perhatian' => '⚠️',
            'buruk' => '🚨',
            'tidak diketahui' => '❓'
        ];
        $statusIcon = $statusEmoji[$statusLabel] ?? '❓';
        $confidence = isset($status['confidence']) ? round($status['confidence'] * 100, 1) : 0;
        $gaugeBar = $getGaugeBar($confidence);
        
        // Penjelasan Prediksi (Reason-Based Explanation) - Format sesuai contoh
        $reasons = [];
        
        // Analisis berdasarkan probabilitas klasifikasi
        if (isset($status['probability'])) {
            $prob = $status['probability'];
            $baikProb = isset($prob['BAIK']) ? round($prob['BAIK'] * 100, 1) : 0;
            $perhatianProb = isset($prob['PERHATIAN']) ? round($prob['PERHATIAN'] * 100, 1) : 0;
            $burukProb = isset($prob['BURUK']) ? round($prob['BURUK'] * 100, 1) : 0;
            
            if ($burukProb > 0) {
                $reasons[] = "Probabilitas kondisi BURUK mencapai {$burukProb}%";
            }
            if ($perhatianProb > 0) {
                $reasons[] = "Probabilitas kondisi PERHATIAN mencapai {$perhatianProb}%";
            }
        }
        
        // Analisis detail setiap sensor dengan nilai spesifik
        $tempStatus = $getStatusText($latest['temperature'], 'temperature');
        $humStatus = $getStatusText($latest['humidity'], 'humidity');
        $ammStatus = $getStatusText($latest['ammonia'], 'ammonia');
        $lightStatus = $getStatusText($latest['light'], 'light');
        
        if ($tempStatus !== 'Aman') {
            if ($latest['temperature'] < 23) {
                $reasons[] = "Suhu terlalu rendah ({$latest['temperature']} &deg;C), di bawah batas optimal";
            } elseif ($latest['temperature'] > 34) {
                $reasons[] = "Suhu terlalu tinggi ({$latest['temperature']} &deg;C), di atas batas optimal";
            }
        }
        
        if ($humStatus !== 'Aman') {
            if ($latest['humidity'] < 50) {
                $reasons[] = "Kelembaban terlalu rendah ({$latest['humidity']}%), di bawah batas optimal";
            } elseif ($latest['humidity'] > 70) {
                $reasons[] = "Kelembaban terlalu tinggi ({$latest['humidity']}%), di atas batas optimal";
            }
        }
        
        if ($ammStatus !== 'Aman') {
            $reasons[] = "Kadar amoniak berbahaya ({$latest['ammonia']} ppm), melebihi batas aman";
        }
        
        if ($lightStatus !== 'Aman') {
            if ($latest['light'] < 10) {
                $reasons[] = "Intensitas cahaya terlalu rendah ({$latest['light']} lux), di bawah batas optimal";
            } elseif ($latest['light'] > 60) {
                $reasons[] = "Intensitas cahaya terlalu tinggi ({$latest['light']} lux), melebihi batas optimal";
            }
        }
        
        if (empty($reasons)) {
            $reasons[] = "Semua parameter sensor berada dalam rentang optimal";
        }
        
        // Prediksi 6 Jam
        $pred6Text = [];
        if (!empty($prediction_6h) && is_array($prediction_6h)) {
            $tempPred = isset($prediction_6h['temperature']) && is_array($prediction_6h['temperature']) ? $prediction_6h['temperature'] : [];
            $humPred = isset($prediction_6h['humidity']) && is_array($prediction_6h['humidity']) ? $prediction_6h['humidity'] : [];
            $ammPred = isset($prediction_6h['ammonia']) && is_array($prediction_6h['ammonia']) ? $prediction_6h['ammonia'] : [];
            $lightPred = isset($prediction_6h['light']) && is_array($prediction_6h['light']) ? $prediction_6h['light'] : [];
            
            if (!empty($tempPred)) {
                $pred6Text[] = "Suhu: " . round(min($tempPred), 1) . "–" . round(max($tempPred), 1) . " &deg;C";
            }
            if (!empty($humPred)) {
                $pred6Text[] = "Kelembaban: " . round(min($humPred), 1) . "–" . round(max($humPred), 1) . "%";
            }
            if (!empty($ammPred)) {
                $pred6Text[] = "Amoniak: " . round(min($ammPred), 1) . "–" . round(max($ammPred), 1) . " ppm";
            }
            if (!empty($lightPred)) {
                $pred6Text[] = "Cahaya: " . round(min($lightPred), 1) . "–" . round(max($lightPred), 1) . " lux";
            }
            
            if (empty($pred6Text)) {
                $pred6Text[] = "Data prediksi tidak tersedia";
            }
        } else {
            $pred6Text[] = "Data prediksi tidak tersedia";
        }
        
        // Indikator Risiko Tren
        $trends = [];
        if (!empty($prediction_6h) && is_array($prediction_6h)) {
            $tempPred = isset($prediction_6h['temperature']) && is_array($prediction_6h['temperature']) ? $prediction_6h['temperature'] : [];
            $humPred = isset($prediction_6h['humidity']) && is_array($prediction_6h['humidity']) ? $prediction_6h['humidity'] : [];
            $ammPred = isset($prediction_6h['ammonia']) && is_array($prediction_6h['ammonia']) ? $prediction_6h['ammonia'] : [];
            $lightPred = isset($prediction_6h['light']) && is_array($prediction_6h['light']) ? $prediction_6h['light'] : [];
            
            $trends['temperature'] = !empty($tempPred) ? $getTrendIndicator($tempPred) : 'stabil';
            $trends['humidity'] = !empty($humPred) ? $getTrendIndicator($humPred) : 'stabil';
            $trends['ammonia'] = !empty($ammPred) ? $getTrendIndicator($ammPred) : 'stabil';
            $trends['light'] = !empty($lightPred) ? $getTrendIndicator($lightPred) : 'stabil';
        } else {
            $trends = ['temperature' => 'stabil', 'humidity' => 'stabil', 'ammonia' => 'stabil', 'light' => 'stabil'];
        }
        
        // Deteksi Anomali - Format sesuai contoh
        $anomalyStatus = empty($anomalies) ? 'NORMAL' : 'ANOMALI';
        $anomalyCount = count($anomalies);
        $anomalyList = [];
        if (!empty($anomalies)) {
            foreach (array_slice($anomalies, 0, 3) as $anomaly) {
                $sensorName = [
                    'temperature' => 'Suhu',
                    'humidity' => 'Kelembaban',
                    'ammonia' => 'Amoniak',
                    'light' => 'Cahaya'
                ][$anomaly['type'] ?? 'unknown'] ?? ucfirst($anomaly['type'] ?? 'Unknown');
                
                // Gunakan value dari anomali, jika tidak ada gunakan dari latest
                $anomalyValue = isset($anomaly['value']) ? (float)$anomaly['value'] : (float)($latest[$anomaly['type'] ?? 'temperature'] ?? 0);
                $anomalyMessage = $anomaly['message'] ?? 'Anomali terdeteksi';
                
                // Tentukan unit berdasarkan type
                $unit = '';
                if (($anomaly['type'] ?? '') === 'temperature') {
                    $unit = ' &deg;C';
                } elseif (($anomaly['type'] ?? '') === 'humidity') {
                    $unit = '%';
                } elseif (($anomaly['type'] ?? '') === 'ammonia') {
                    $unit = ' ppm';
                } elseif (($anomaly['type'] ?? '') === 'light') {
                    $unit = ' lux';
                }
                
                // Format sesuai contoh: "Kelembaban terlalu rendah (47.7%)"
                // atau "Suhu menyimpang (33.5°C, z=1.83)"
                if (strpos($anomalyMessage, 'terlalu rendah') !== false) {
                    $anomalyList[] = "• {$sensorName} terlalu rendah (" . round($anomalyValue, 1) . "{$unit})";
                } elseif (strpos($anomalyMessage, 'terlalu tinggi') !== false) {
                    $anomalyList[] = "• {$sensorName} terlalu tinggi (" . round($anomalyValue, 1) . "{$unit})";
                } elseif (strpos($anomalyMessage, 'menyimpang') !== false || strpos($anomalyMessage, 'menyimpang') !== false) {
                    // Jika ada z-score di message, extract dan tampilkan
                    $zScore = '';
                    if (preg_match('/z[=\s:]+([\d.]+)/i', $anomalyMessage, $zMatches)) {
                        $zScore = ', z=' . round((float)$zMatches[1], 2);
                    }
                    $anomalyList[] = "• {$sensorName} menyimpang (" . round($anomalyValue, 1) . "{$unit}{$zScore})";
                } else {
                    // Fallback: format sederhana
                    $anomalyList[] = "• {$sensorName}: " . round($anomalyValue, 1) . "{$unit} - " . $anomalyMessage;
                }
            }
        }
        
        // Rekomendasi Tindak Lanjut - Format sesuai contoh
        $recommendations = [];
        if ($statusLabel === 'buruk') {
            $recommendations[] = "Cek kondisi ayam secara langsung";
            $recommendations[] = "Naikkan ventilasi";
            $recommendations[] = "Sesuaikan suhu (pemanas/kipas)";
            $recommendations[] = "Konsultasi dokter hewan jika tidak membaik";
            $recommendations[] = "Monitoring setiap 30 menit";
        } elseif ($statusLabel === 'perhatian') {
            $recommendations[] = "Pantau parameter sensor secara berkala";
            $recommendations[] = "Lakukan penyesuaian kecil pada ventilasi atau suhu";
            $recommendations[] = "Periksa kondisi pakan dan air minum";
            $recommendations[] = "Siapkan tindakan pencegahan jika kondisi memburuk";
        } else {
            $recommendations[] = "Pertahankan kondisi lingkungan saat ini";
            $recommendations[] = "Lanjutkan monitoring rutin";
            $recommendations[] = "Pastikan sistem sensor berfungsi dengan baik";
            $recommendations[] = "Lakukan pengecekan berkala sesuai jadwal";
        }
        
        // Build Message - Format sesuai contoh yang diberikan
        $message = "🐔 LAPORAN MONITORING KANDANG AYAM\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Status Kandang
        $message .= "📊 STATUS KANDANG\n\n";
        $message .= "{$statusIcon} " . strtoupper($statusLabel) . "\n\n";
        $message .= "📈 Tingkat Keyakinan: {$confidence}%\n\n";
        $message .= "{$gaugeBar} (Confidence " . ($confidence >= 80 ? 'tinggi' : ($confidence >= 60 ? 'sedang' : 'rendah')) . ")\n\n";
        
        // Interpretasi confidence
        if ($confidence >= 80) {
            $message .= "Sistem sangat yakin bahwa kondisi kandang berada dalam status " . ucfirst($statusLabel) . " dan " . ($statusLabel === 'buruk' ? 'memerlukan tindakan cepat' : ($statusLabel === 'perhatian' ? 'perlu perhatian' : 'dalam kondisi baik')) . ".\n";
        } elseif ($confidence >= 60) {
            $message .= "Sistem cukup yakin dengan prediksi, namun perlu monitoring lanjutan.\n";
        } else {
            $message .= "Sistem memerlukan verifikasi manual untuk memastikan kondisi kandang.\n";
        }
        
        $message .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Penjelasan Prediksi
        $message .= "🧠 Penjelasan Prediksi (Reason-Based Explanation)\n\n";
        foreach ($reasons as $reason) {
            $message .= "• {$reason}\n";
        }
        
        $message .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Data Sensor Saat Ini - Format sesuai contoh
        $message .= "⚙️ DATA SENSOR SAAT INI\n\n";
        
        $tempStatusText = $tempStatus === 'Aman' ? 'Aman' : 'Tidak aman';
        $tempRange = "23–34 &deg;C";
        $message .= "• Suhu: {$latest['temperature']} &deg;C ({$tempStatusText} — {$tempRange})\n";
        
        $humStatusText = $humStatus === 'Aman' ? 'Aman' : 'Tidak aman';
        $humRange = "50–70%";
        $message .= "• Kelembaban: {$latest['humidity']}% ({$humStatusText} — {$humRange})\n";
        
        $ammStatusText = $ammStatus === 'Aman' ? 'Aman' : 'Tidak aman';
        $ammRange = "max 20 ppm";
        $message .= "• Amoniak: {$latest['ammonia']} ppm ({$ammStatusText} — {$ammRange})\n";
        
        $lightStatusText = $lightStatus === 'Aman' ? 'Aman' : 'Tidak aman';
        $lightRange = "10–60 lux";
        $message .= "• Cahaya: {$latest['light']} lux ({$lightStatusText} — {$lightRange})\n";
        
        $message .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Prediksi 6 Jam
        $message .= "📈 PREDIKSI 6 JAM KE DEPAN (Ensemble LSTM)\n\n";
        foreach ($pred6Text as $pred) {
            $message .= "• {$pred}\n";
        }
        
        $message .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Indikator Tren Risiko
        $message .= "🔥 INDIKATOR TREN RISIKO\n\n";
        $message .= "• Suhu: {$trends['temperature']}\n";
        $message .= "• Kelembaban: {$trends['humidity']}\n";
        $message .= "• Cahaya: {$trends['light']}\n";
        $message .= "• Amoniak: {$trends['ammonia']}\n";
        
        $message .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Deteksi Anomali
        $message .= "🚨 DETEKSI ANOMALI\n\n";
        $message .= "Status: {$anomalyStatus}\n\n";
        $message .= "Total Anomali: {$anomalyCount}\n\n";
        
        if (!empty($anomalyList)) {
            $message .= "Contoh:\n\n";
            foreach ($anomalyList as $anom) {
                $message .= "{$anom}\n";
            }
            $message .= "\nMetode deteksi: Isolation Forest + Threshold Analysis\n";
        } else {
            $message .= "Tidak ada anomali terdeteksi.\n";
        }
        
        $message .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Rekomendasi
        $message .= "🧭 REKOMENDASI\n\n";
        foreach ($recommendations as $index => $rec) {
            $message .= ($index + 1) . ". {$rec}\n";
        }
        
        $message .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Timestamp dan Footer
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
        
        $message .= "🕐 Waktu Laporan: {$wibTime->format('d/m/Y H:i:s')} WIB\n\n";
        $message .= "📅 Hari: {$dayName}\n\n";
        $message .= "📤 Dikirim otomatis oleh ChickPatrol Monitoring System\n\n";
        $message .= "🤖 Powered by Machine Learning &amp; IoT Sensors";
        
        return $message;
    }
}

