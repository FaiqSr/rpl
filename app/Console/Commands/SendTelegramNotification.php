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
        // Check if Telegram notifications are enabled
        $telegramEnabled = env('TELEGRAM_NOTIFICATIONS_ENABLED', 'true');
        // Handle string 'true'/'false' and boolean true/false
        if ($telegramEnabled === 'false' || $telegramEnabled === false || $telegramEnabled === '0' || $telegramEnabled === 0) {
            $this->info('Telegram notifications are disabled');
            Log::info('Telegram notifications disabled', ['enabled' => $telegramEnabled]);
            return 0;
        }

        // Check if Telegram credentials are configured
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');
        
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
            
            // Get latest 30 sensor readings
            $sensorReadings = SensorReading::orderBy('recorded_at', 'desc')
                ->limit(30)
                ->get()
                ->reverse()
                ->values();

            if ($sensorReadings->count() < 30) {
                $this->warn('Insufficient sensor data (need 30, got ' . $sensorReadings->count() . ')');
                return 0;
            }

            // Format history data
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

            // Get latest reading
            $latestReading = $sensorReadings->last();
            $latest = [
                'time' => $latestReading->recorded_at->format('Y-m-d H:00'),
                'temperature' => (float) $latestReading->suhu_c,
                'humidity' => (float) $latestReading->kelembaban_rh,
                'ammonia' => (float) $latestReading->amonia_ppm,
                'light' => (float) $latestReading->cahaya_lux
            ];

            // Get ML predictions
            $mlResults = $mlService->getPredictions($history);
            
            $pred6 = $mlResults['prediction_6h'] ?? ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
            $status = $mlResults['status'] ?? ['label' => 'tidak diketahui', 'severity' => 'warning', 'message' => 'Status tidak dapat ditentukan'];
            $anomalies = $mlResults['anomalies'] ?? [];
            $forecast6Summary = $mlResults['forecast_summary_6h'] ?? [];

            // Send Telegram notification
            $telegramService = new TelegramNotificationService();
            $sent = $telegramService->sendMonitoringNotification(
                $latest,
                $status,
                $pred6,
                $anomalies,
                $forecast6Summary
            );

            if ($sent) {
                $this->info('✅ Telegram notification sent successfully at ' . now()->format('Y-m-d H:i:s'));
                Log::info('Telegram notification sent successfully', [
                    'time' => now()->toDateTimeString(),
                    'status' => $status['label'] ?? 'unknown'
                ]);
                return 0;
            } else {
                $this->error('❌ Failed to send Telegram notification');
                Log::error('Failed to send Telegram notification', [
                    'time' => now()->toDateTimeString()
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
}

