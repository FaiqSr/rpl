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
        if (!$this->botToken || !$this->chatId) {
            Log::warning('Telegram credentials not configured');
            return false;
        }

        try {
            // Format pesan
            $message = $this->formatMonitoringMessage($latest, $status, $prediction_6h, $anomalies, $forecast_summary_6h);
            
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
     * Format pesan monitoring untuk Telegram
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

