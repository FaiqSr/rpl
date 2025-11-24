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
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
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
     * Build laporan Telegram lengkap sesuai template
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
                if ($value < $t['min']) return 'Terlalu Rendah';
                if ($value > $t['max']) return 'Terlalu Tinggi';
                return 'Aman';
            } elseif ($type === 'humidity') {
                if ($value < $t['min']) return 'Terlalu Rendah';
                if ($value > $t['max']) return 'Terlalu Tinggi';
                return 'Aman';
            } elseif ($type === 'ammonia') {
                if ($value > $t['max']) return 'Berbahaya';
                return 'Aman';
            } elseif ($type === 'light') {
                if ($value < $t['min'] || $value > $t['max']) return 'Di Luar Batas';
                return 'Aman';
            }
            
            return 'Normal';
        };
        
        $getTrendIndicator = function($values) {
            if (!is_array($values) || count($values) < 2) return '➡️ Stabil';
            $first = $values[0];
            $last = $values[count($values) - 1];
            $diff = $last - $first;
            
            if ($diff > 1) return '📈 Meningkat';
            if ($diff < -1) return '📉 Menurun';
            return '➡️ Stabil';
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
        
        // Penjelasan Prediksi (Reason-Based Explanation)
        $reasons = [];
        if (isset($status['probability'])) {
            $prob = $status['probability'];
            if (isset($prob['BURUK']) && $prob['BURUK'] > 0.3) {
                $reasons[] = "Probabilitas BURUK tinggi (" . round($prob['BURUK'] * 100, 1) . "%)";
            }
            if (isset($prob['PERHATIAN']) && $prob['PERHATIAN'] > 0.5) {
                $reasons[] = "Probabilitas PERHATIAN dominan (" . round($prob['PERHATIAN'] * 100, 1) . "%)";
            }
        }
        
        // Check sensor values untuk alasan
        if ($latest['temperature'] < 23 || $latest['temperature'] > 34) {
            $reasons[] = "Suhu di luar rentang optimal (23-34°C)";
        }
        if ($latest['humidity'] < 50 || $latest['humidity'] > 70) {
            $reasons[] = "Kelembaban di luar rentang optimal (50-70%)";
        }
        if ($latest['ammonia'] > 20) {
            $reasons[] = "Kadar amoniak melebihi batas aman (>20 ppm)";
        }
        if ($latest['light'] < 10 || $latest['light'] > 60) {
            $reasons[] = "Intensitas cahaya di luar rentang optimal (10-60 lux)";
        }
        
        if (empty($reasons)) {
            $reasons[] = "Semua parameter sensor dalam kondisi normal";
        }
        
        // Prediksi 6 Jam
        // $prediction_6h memiliki struktur: ['temperature' => [...], 'humidity' => [...], 'ammonia' => [...], 'light' => [...]]
        $pred6Text = [];
        if (!empty($prediction_6h) && is_array($prediction_6h)) {
            $tempPred = isset($prediction_6h['temperature']) && is_array($prediction_6h['temperature']) ? $prediction_6h['temperature'] : [];
            $humPred = isset($prediction_6h['humidity']) && is_array($prediction_6h['humidity']) ? $prediction_6h['humidity'] : [];
            $ammPred = isset($prediction_6h['ammonia']) && is_array($prediction_6h['ammonia']) ? $prediction_6h['ammonia'] : [];
            $lightPred = isset($prediction_6h['light']) && is_array($prediction_6h['light']) ? $prediction_6h['light'] : [];
            
            if (!empty($tempPred)) {
                $pred6Text[] = "Suhu: " . round(min($tempPred), 1) . "–" . round(max($tempPred), 1) . "°C";
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
            
            $trends['temperature'] = !empty($tempPred) ? $getTrendIndicator($tempPred) : '➡️ Stabil';
            $trends['humidity'] = !empty($humPred) ? $getTrendIndicator($humPred) : '➡️ Stabil';
            $trends['ammonia'] = !empty($ammPred) ? $getTrendIndicator($ammPred) : '➡️ Stabil';
            $trends['light'] = !empty($lightPred) ? $getTrendIndicator($lightPred) : '➡️ Stabil';
        } else {
            $trends = ['temperature' => '➡️ Stabil', 'humidity' => '➡️ Stabil', 'ammonia' => '➡️ Stabil', 'light' => '➡️ Stabil'];
        }
        
        // Deteksi Anomali
        $anomalyStatus = empty($anomalies) ? 'NORMAL' : 'ANOMALI';
        $anomalyCount = count($anomalies);
        $anomalyList = [];
        if (!empty($anomalies)) {
            foreach (array_slice($anomalies, 0, 5) as $anomaly) {
                $sensorName = [
                    'temperature' => 'Suhu',
                    'humidity' => 'Kelembaban',
                    'ammonia' => 'Amoniak',
                    'light' => 'Cahaya'
                ][$anomaly['type'] ?? 'unknown'] ?? ucfirst($anomaly['type'] ?? 'Unknown');
                $anomalyList[] = "• {$sensorName}: " . ($anomaly['message'] ?? 'Anomali terdeteksi');
            }
        }
        
        // Rekomendasi Tindak Lanjut
        $recommendations = [];
        if ($statusLabel === 'buruk') {
            $recommendations[] = "Segera periksa dan sesuaikan kondisi lingkungan kandang";
            $recommendations[] = "Tingkatkan ventilasi untuk mengurangi kadar amoniak";
            $recommendations[] = "Periksa sistem pemanas/penyejuk untuk mengatur suhu";
            $recommendations[] = "Hubungi dokter hewan jika kondisi tidak membaik";
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
        
        // Build Message
        $message = "<b>🐔 LAPORAN MONITORING KANDANG AYAM</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Status Kandang
        $message .= "<b>📊 Status Kandang</b>\n\n";
        $message .= "{$statusIcon} <b>" . strtoupper($statusLabel) . "</b>\n";
        $message .= "<i>Confidence Score: {$confidence}%</i>\n";
        $message .= "Gauge bar: {$gaugeBar}\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Penjelasan Prediksi
        $message .= "<b>🧠 Penjelasan Prediksi (Reason-Based Explanation)</b>\n\n";
        foreach ($reasons as $reason) {
            $message .= "• {$reason}\n";
        }
        $message .= "\n━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Data Sensor Saat Ini
        $message .= "<b>⚙️ Data Sensor Saat Ini</b>\n\n";
        $message .= "🌡️ Suhu: <b>{$latest['temperature']}°C</b> — <i>" . $getStatusText($latest['temperature'], 'temperature') . "</i>\n";
        $message .= "💧 Kelembaban: <b>{$latest['humidity']}%</b> — <i>" . $getStatusText($latest['humidity'], 'humidity') . "</i>\n";
        $message .= "💨 Amoniak: <b>{$latest['ammonia']} ppm</b> — <i>" . $getStatusText($latest['ammonia'], 'ammonia') . "</i>\n";
        $message .= "💡 Cahaya: <b>{$latest['light']} lux</b> — <i>" . $getStatusText($latest['light'], 'light') . "</i>\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Prediksi 6 Jam
        $message .= "<b>📈 Prediksi 6 Jam Kedepan (Ensemble LSTM)</b>\n\n";
        foreach ($pred6Text as $pred) {
            $message .= "{$pred}\n";
        }
        $message .= "\n━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Indikator Risiko Tren
        $message .= "<b>🔥 Indikator Risiko Tren</b>\n\n";
        $message .= "Suhu: {$trends['temperature']}\n";
        $message .= "Kelembaban: {$trends['humidity']}\n";
        $message .= "Cahaya: {$trends['light']}\n";
        $message .= "Amoniak: {$trends['ammonia']}\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Deteksi Anomali
        $message .= "<b>🚨 Deteksi Anomali</b>\n\n";
        $message .= "Status: <b>{$anomalyStatus} — {$anomalyCount} Anomali</b>\n";
        if (!empty($anomalyList)) {
            $message .= "\nContoh anomali terbaru:\n";
            foreach ($anomalyList as $anom) {
                $message .= "{$anom}\n";
            }
            $message .= "\n<i>> Anomali berasal dari kombinasi Isolation Forest + threshold.</i>\n";
        } else {
            $message .= "\n<i>Tidak ada anomali terdeteksi.</i>\n";
        }
        $message .= "\n━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Rekomendasi
        $message .= "<b>🧭 Rekomendasi Tindak Lanjut</b>\n\n";
        foreach ($recommendations as $index => $rec) {
            $message .= ($index + 1) . ". {$rec}\n";
        }
        $message .= "\n━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Timestamp
        $wibTime = now()->setTimezone('Asia/Jakarta');
        $message .= "🕐 {$wibTime->format('d/m/Y H:i:s')} WIB\n\n";
        $message .= "<i>📤 Dikirim otomatis oleh ChickPatrol Monitoring System</i>\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━";
        
        return $message;
    }

    /**
     * Format pesan monitoring untuk Telegram (legacy - kept for backward compatibility)
     */
    protected function formatMonitoringMessage($latest, $status, $prediction_6h, $anomalies, $forecast_summary_6h)
    {
        $statusEmoji = [
            'baik' => '✅',
            'perhatian' => '⚠️',
            'buruk' => '🚨',
            'tidak diketahui' => '❓'
        ];

        $statusLabel = $status['label'] ?? 'tidak diketahui';
        $statusEmojiIcon = $statusEmoji[$statusLabel] ?? '❓';
        $confidence = isset($status['confidence']) ? round($status['confidence'] * 100, 1) : 0;

        $message = "<b>🐔 LAPORAN MONITORING KANDANG AYAM</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Status Kandang
        $message .= "<b>📊 Status Kandang:</b>\n";
        $message .= "{$statusEmojiIcon} <b>" . strtoupper($statusLabel) . "</b>";
        if ($confidence > 0) {
            $message .= " (Keyakinan: {$confidence}%)";
        }
        $message .= "\n";
        if (isset($status['message'])) {
            $message .= "💬 " . $status['message'] . "\n";
        }
        $message .= "\n";

        // Nilai Sensor Saat Ini
        $message .= "<b>📈 Nilai Sensor Saat Ini:</b>\n";
        $message .= "🌡️ Suhu: <b>{$latest['temperature']}°C</b>\n";
        $message .= "💧 Kelembaban: <b>{$latest['humidity']}%</b>\n";
        $message .= "💨 Amoniak: <b>{$latest['ammonia']} ppm</b>\n";
        $message .= "💡 Cahaya: <b>{$latest['light']} lux</b>\n";
        $message .= "\n";

        // Prediksi 6 Jam
        if (!empty($forecast_summary_6h)) {
            $message .= "<b>🔮 Prediksi 6 Jam Ke Depan:</b>\n";
            foreach ($forecast_summary_6h as $forecast) {
                $metric = $forecast['metric'] ?? 'Unknown';
                $summary = $forecast['summary'] ?? 'Data tidak tersedia';
                $risk = $forecast['risk'] ?? 'tidak diketahui';
                
                $riskEmoji = '✅';
                if (strpos($risk, 'di luar batas aman') !== false) {
                    $riskEmoji = '🚨';
                } elseif (strpos($risk, 'potensi') !== false) {
                    $riskEmoji = '⚠️';
                }
                
                $message .= "{$riskEmoji} {$summary}\n";
            }
            $message .= "\n";
        }

        // Anomali
        if (!empty($anomalies)) {
            $criticalAnomalies = array_filter($anomalies, function($a) {
                return ($a['severity'] ?? 'warning') === 'critical';
            });
            $warningAnomalies = array_filter($anomalies, function($a) {
                return ($a['severity'] ?? 'warning') === 'warning';
            });

            $message .= "<b>⚠️ Anomali Terdeteksi:</b>\n";
            
            if (!empty($criticalAnomalies)) {
                $message .= "🚨 <b>KRITIS (" . count($criticalAnomalies) . "):</b>\n";
                foreach (array_slice($criticalAnomalies, 0, 5) as $anomaly) {
                    $message .= "• " . ($anomaly['message'] ?? $anomaly['type'] ?? 'Anomali') . "\n";
                }
            }
            
            if (!empty($warningAnomalies)) {
                $message .= "⚠️ <b>PERINGATAN (" . count($warningAnomalies) . "):</b>\n";
                foreach (array_slice($warningAnomalies, 0, 5) as $anomaly) {
                    $message .= "• " . ($anomaly['message'] ?? $anomaly['type'] ?? 'Anomali') . "\n";
                }
            }
            
            if (count($anomalies) > 10) {
                $message .= "\n<i>... dan " . (count($anomalies) - 10) . " anomali lainnya</i>\n";
            }
            $message .= "\n";
        } else {
            $message .= "<b>✅ Tidak Ada Anomali</b>\n\n";
        }

        // Timestamp dengan WIB
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        // Set timezone ke WIB (Asia/Jakarta)
        $wibTime = now()->setTimezone('Asia/Jakarta');
        $message .= "🕐 " . $wibTime->format('d/m/Y H:i:s') . " WIB\n";
        $message .= "<i>Dikirim otomatis oleh ChickPatrol Monitoring System</i>";

        return $message;
    }
}

