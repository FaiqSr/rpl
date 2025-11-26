<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramNotificationService;
use App\Services\MachineLearningService;
use App\Models\SensorReading;
use Illuminate\Support\Facades\Log;

class SendTelegramNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send-monitoring';

    /**
     * The console command description.
     *
     * @var string
     * @var string
     */
    protected $description = 'Send monitoring notification to Telegram every 5 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $wibTime = now()->setTimezone('Asia/Jakarta');
        $this->info('🔄 Checking Telegram notification at ' . $wibTime->format('Y-m-d H:i:s') . ' WIB');
        
        // Check if Telegram notifications are enabled
        // Read directly from .env file to ensure latest value (bypass config cache)
        $telegramEnabled = $this->getEnvValue('TELEGRAM_NOTIFICATIONS_ENABLED', 'true');
        
        // Handle string 'true'/'false' and boolean true/false
        if ($telegramEnabled === 'false' || $telegramEnabled === false || $telegramEnabled === '0' || $telegramEnabled === 0) {
            $this->info('⏸️ Telegram notifications are disabled');
            Log::info('Telegram notifications disabled', [
                'time' => $wibTime->format('Y-m-d H:i:s') . ' WIB',
                'enabled' => $telegramEnabled
            ]);
            return 0;
        }

        // Check if Telegram credentials are configured
        // Read directly from .env file to ensure latest value (bypass config cache)
        $botToken = $this->getEnvValue('TELEGRAM_BOT_TOKEN');
        $chatId = $this->getEnvValue('TELEGRAM_CHAT_ID');
        
        if (!$botToken || !$chatId) {
            $this->warn('Telegram credentials not configured');
            Log::warning('Telegram credentials not configured', [
                'has_token' => !empty($botToken),
                'has_chat_id' => !empty($chatId)
            ]);
            return 0;
        }

        try {
            $mlService = new MachineLearningService();
            
            // Get latest 30 sensor readings - SAMA DENGAN YANG DIGUNAKAN DI WEB MONITORING
            // Ambil 30 data terakhir, diurutkan dari yang paling lama (untuk history ML)
            $sensorReadings = SensorReading::orderBy('recorded_at', 'asc')
                ->limit(30)
                ->get();

            if ($sensorReadings->count() < 30) {
                $this->warn('Insufficient sensor data (need 30, got ' . $sensorReadings->count() . ')');
                return 0;
            }

            // Format history data (dari yang paling lama ke yang terbaru)
            $history = [];
            foreach ($sensorReadings as $reading) {
                $history[] = [
                    'time' => $reading->recorded_at->format('Y-m-d H:00'),
                    'temperature' => (float) $reading->suhu_c,
                    'humidity' => (float) $reading->kelembaban_rh,
                    'ammonia' => (float) $reading->amonia_ppm,
                    'light' => (float) $reading->cahaya_lux
                ];
            }

            // Get latest reading - AMBIL DATA TERAKHIR (sama dengan di web monitoring)
            $latestReading = $sensorReadings->last();
            $latest = [
                'time' => $latestReading->recorded_at->format('Y-m-d H:00'),
                'temperature' => (float) $latestReading->suhu_c,
                'humidity' => (float) $latestReading->kelembaban_rh,
                'ammonia' => (float) $latestReading->amonia_ppm,
                'light' => (float) $latestReading->cahaya_lux
            ];
            
            // Log untuk debugging - pastikan data sama dengan web
            Log::info('Telegram notification data', [
                'latest_temperature' => $latest['temperature'],
                'latest_humidity' => $latest['humidity'],
                'latest_ammonia' => $latest['ammonia'],
                'latest_light' => $latest['light'],
                'latest_time' => $latest['time']
            ]);

            // Get ML predictions
            $mlResults = $mlService->getPredictions($history);
            
            $pred6 = $mlResults['prediction_6h'] ?? ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
            $status = $mlResults['status'] ?? ['label' => 'tidak diketahui', 'severity' => 'warning', 'message' => 'Status tidak dapat ditentukan'];
            $anomalies = $mlResults['anomalies'] ?? [];
            $forecast6Summary = $mlResults['forecast_summary_6h'] ?? [];

            // Check status label - only send notification if status is PERHATIAN or BURUK
            $statusLabel = strtolower($status['label'] ?? 'tidak diketahui');
            
            // Log status untuk debugging
            $wibTime = now()->setTimezone('Asia/Jakarta');
            $this->info('📊 Status kandang: ' . strtoupper($statusLabel) . ' (Waktu: ' . $wibTime->format('H:i:s') . ' WIB)');
            Log::info('Telegram notification check', [
                'time' => $wibTime->format('Y-m-d H:i:s') . ' WIB',
                'status' => $statusLabel,
                'enabled' => $telegramEnabled,
                'has_credentials' => !empty($botToken) && !empty($chatId)
            ]);
            
            // Only send notification if condition is not good (PERHATIAN or BURUK)
            if ($statusLabel === 'baik') {
                $this->info('✅ Kondisi kandang BAIK - Notifikasi tidak dikirim (status: ' . $statusLabel . ')');
                Log::info('Telegram notification skipped - kondisi baik', [
                    'time' => $wibTime->format('Y-m-d H:i:s') . ' WIB',
                    'status' => $statusLabel,
                    'reason' => 'Kondisi kandang dalam keadaan baik, tidak perlu notifikasi'
                ]);
                return 0;
            }
            
            $this->info('⚠️ Kondisi kandang ' . strtoupper($statusLabel) . ' - Akan mengirim notifikasi Telegram...');

            // Send Telegram notification only for PERHATIAN or BURUK
            $telegramService = new TelegramNotificationService();
            $sent = $telegramService->sendMonitoringNotification(
                $latest,
                $status,
                $pred6,
                $anomalies,
                $forecast6Summary
            );

            if ($sent) {
                $wibTime = now()->setTimezone('Asia/Jakarta');
                $this->info('✅ Telegram notification sent successfully at ' . $wibTime->format('Y-m-d H:i:s') . ' WIB (Status: ' . strtoupper($statusLabel) . ')');
                Log::info('Telegram notification sent successfully', [
                    'time' => $wibTime->format('Y-m-d H:i:s') . ' WIB',
                    'status' => $statusLabel,
                    'reason' => 'Kondisi kandang memerlukan perhatian'
                ]);
                return 0;
            } else {
                $wibTime = now()->setTimezone('Asia/Jakarta');
                $this->error('❌ Failed to send Telegram notification');
                Log::error('Failed to send Telegram notification', [
                    'time' => $wibTime->format('Y-m-d H:i:s') . ' WIB',
                    'status' => $statusLabel
                ]);
                return 1;
            }
        } catch (\Exception $e) {
            Log::error('Error sending Telegram notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'time' => now()->toDateTimeString()
            ]);
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Read value directly from .env file (bypass config cache)
     * 
     * @param string $key
     * @param mixed $default
     * @return string|null
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
        
        // Match the key=value pattern
        if (preg_match('/^' . preg_quote($key, '/') . '\s*=\s*(.*)$/m', $envContent, $matches)) {
            $value = trim($matches[1]);
            // Remove quotes if present
            $value = trim($value, '"\'');
            return $value;
        }
        
        return $default;
    }
}

