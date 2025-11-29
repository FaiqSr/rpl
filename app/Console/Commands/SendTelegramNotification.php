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
    protected $signature = 'telegram:send-monitoring {--test : Run in test mode with 10 second loop}';

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
        // Test mode: Run in loop every 10 seconds
        if ($this->option('test')) {
            $this->info('🧪 TESTING MODE: Running Telegram notification every 10 seconds');
            $this->info('Press Ctrl+C to stop');
            $this->info('');
            
            while (true) {
                $this->processNotification();
                sleep(10); // Wait 10 seconds before next check
            }
        }
        
        // Normal mode: Run once (called by scheduler)
        return $this->processNotification();
    }
    
    protected function processNotification()
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
        
        // Get state file untuk tracking
        $stateFile = storage_path('app/telegram_notification_state.json');
        $state = $this->getNotificationState($stateFile);

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
            
            // Get latest 30 sensor readings - SAMA PERSIS DENGAN WEB MONITORING
            // Web monitoring menggunakan orderBy('recorded_at', 'asc') yang mengambil 30 data TERLAMA
            // Lalu menggunakan end($history) untuk mendapatkan data TERBARU
            $sensorReadings = SensorReading::orderBy('recorded_at', 'asc')
                ->limit(30)
                ->get();

            if ($sensorReadings->count() < 30) {
                $this->warn('Insufficient sensor data (need 30, got ' . $sensorReadings->count() . ')');
                return 0;
            }

            // Format history data (dari yang paling lama ke yang terbaru) - SAMA DENGAN WEB
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
            // Gunakan end($history) seperti di web monitoring untuk konsistensi
            $latest = end($history);
            if (!$latest) {
                $latestReading = $sensorReadings->last();
                $latest = [
                    'time' => $latestReading->recorded_at->format('Y-m-d H:00'),
                    'temperature' => (float) $latestReading->suhu_c,
                    'humidity' => (float) $latestReading->kelembaban_rh,
                    'ammonia' => (float) $latestReading->amonia_ppm,
                    'light' => (float) $latestReading->cahaya_lux
                ];
            }
            
            // Log untuk debugging - pastikan data sama dengan web
            // Log untuk debugging - pastikan data sama dengan web monitoring
            Log::info('Telegram notification - Data preparation', [
                'history_count' => count($history),
                'history_first_time' => !empty($history) ? $history[0]['time'] : null,
                'history_last_time' => !empty($history) ? end($history)['time'] : null,
                'latest_temperature' => $latest['temperature'],
                'latest_humidity' => $latest['humidity'],
                'latest_ammonia' => $latest['ammonia'],
                'latest_light' => $latest['light'],
                'latest_time' => $latest['time']
            ]);

            // Get ML predictions - SAMA DENGAN WEB MONITORING
            $mlResults = $mlService->getPredictions($history);
            
            // Log ML results untuk debugging
            Log::info('Telegram notification - ML Results', [
                'has_prediction_6h' => !empty($mlResults['prediction_6h']),
                'has_status' => !empty($mlResults['status']),
                'has_anomalies' => !empty($mlResults['anomalies']),
                'has_forecast_summary_6h' => !empty($mlResults['forecast_summary_6h']),
                'ml_status_label' => $mlResults['status']['label'] ?? 'unknown',
                'ml_status_probability' => $mlResults['status']['probability'] ?? null,
                'anomalies_count' => is_array($mlResults['anomalies'] ?? []) ? count($mlResults['anomalies']) : 0
            ]);
            
            $pred6 = $mlResults['prediction_6h'] ?? ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
            $pred24 = $mlResults['prediction_24h'] ?? ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
            $mlStatus = $mlResults['status'] ?? ['label' => 'tidak diketahui', 'severity' => 'warning', 'message' => 'Status tidak dapat ditentukan'];
            $anomalies = $mlResults['anomalies'] ?? [];
            // Gunakan forecast_summary dari ML service jika ada (SAMA DENGAN WEB MONITORING)
            $forecast6SummaryFromML = $mlResults['forecast_summary_6h'] ?? null;
            $forecast24SummaryFromML = $mlResults['forecast_summary_24h'] ?? null;
            
            // ============================================
            // APPLY SAME LOGIC AS WEB MONITORING
            // ============================================
            // Get threshold from database (ALWAYS FRESH - no cache) - SAMA PERSIS DENGAN WEB MONITORING
            // Gunakan profile yang paling baru di-update dari database (dinamis)
            // Ini memastikan Telegram notification selalu menggunakan threshold terbaru yang di-setting
            // Web monitoring menggunakan profile dari localStorage 'selectedThresholdProfile' atau query parameter 'profile'
            // Untuk Telegram, kita ambil profile yang paling baru di-update dari database
            // Force fresh query - clear any model cache (SAMA PERSIS DENGAN WEB MONITORING)
            \App\Models\ThresholdProfile::clearBootedModels();
            \App\Models\ThresholdValue::clearBootedModels();
            
            // Ambil active profile (profile yang paling baru di-update)
            $thresholdProfile = \App\Models\ThresholdProfile::getActiveProfile();
            $profileKey = $thresholdProfile ? $thresholdProfile->profile_key : 'default';
            
            // Map threshold dari database format ke format yang digunakan di kode (SAMA PERSIS DENGAN WEB MONITORING)
            $thresholds = [];
            if ($thresholdProfile) {
                foreach ($thresholdProfile->thresholdValues as $value) {
                    // Convert dari database format (suhu_c, kelembaban_rh, dll) ke format kode (temperature, humidity, dll)
                    if ($value->sensor_type === 'amonia_ppm') {
                        $thresholds['ammonia'] = [
                            'ideal_max' => (float) $value->ideal_max,
                            'warn_max' => (float) $value->warn_max,
                            'danger_max' => (float) $value->danger_max,
                        ];
                    } elseif ($value->sensor_type === 'suhu_c') {
                        $thresholds['temperature'] = [
                            'ideal_min' => (float) $value->ideal_min,
                            'ideal_max' => (float) $value->ideal_max,
                            'danger_low' => (float) $value->danger_min,
                            'danger_high' => (float) $value->danger_max,
                        ];
                    } elseif ($value->sensor_type === 'kelembaban_rh') {
                        $thresholds['humidity'] = [
                            'ideal_min' => (float) $value->ideal_min,
                            'ideal_max' => (float) $value->ideal_max,
                            'warn_low' => (float) $value->ideal_min, // Use ideal_min as warn_low
                            'warn_high' => (float) $value->warn_max,
                            'danger_high' => (float) $value->danger_max,
                        ];
                    } elseif ($value->sensor_type === 'cahaya_lux') {
                        $thresholds['light'] = [
                            'ideal_low' => (float) $value->ideal_min,
                            'ideal_high' => (float) $value->ideal_max,
                            'warn_low' => (float) $value->warn_min,
                            'warn_high' => (float) $value->warn_max,
                        ];
                    }
                }
            }
            
            // Log threshold yang digunakan (SAMA PERSIS DENGAN WEB MONITORING)
            // Profile diambil secara dinamis dari database berdasarkan threshold values yang paling baru di-update
            // Ini memastikan Telegram notification selalu menggunakan threshold terbaru yang di-setting
            Log::info('=== TELEGRAM NOTIFICATION - THRESHOLD YANG DIGUNAKAN (DINAMIS DARI DATABASE) ===', [
                'profile' => $profileKey,
                'profile_id' => $thresholdProfile ? $thresholdProfile->id : null,
                'profile_name' => $thresholdProfile ? $thresholdProfile->profile_name : null,
                'thresholds' => $thresholds,
                'threshold_values_count' => $thresholdProfile ? $thresholdProfile->thresholdValues->count() : 0,
                'note' => 'Profile diambil secara dinamis dari database berdasarkan threshold values yang paling baru di-update'
            ]);
            
            // Fallback to default thresholds if database is empty
            if (empty($thresholds)) {
                $thresholds = [
                    'temperature' => ['ideal_min' => 23, 'ideal_max' => 34, 'danger_low' => 20, 'danger_high' => 37],
                    'humidity' => ['ideal_min' => 50, 'ideal_max' => 70, 'warn_low' => 50, 'warn_high' => 80, 'danger_high' => 80],
                    'ammonia' => ['ideal_max' => 20, 'warn_max' => 35, 'danger_max' => 35],
                    'light' => ['ideal_low' => 20, 'ideal_high' => 40, 'warn_low' => 10, 'warn_high' => 60],
                ];
            }
            
            // Helper function: Calculate threshold score (same as web monitoring)
            $calculateThresholdScore = function($sensorData, $thresholds) {
                $issues = 0;
                $criticalIssues = 0;
                $warnings = 0;
                
                // Validasi setiap sensor dengan threshold
                $temp = $sensorData['temperature'] ?? $sensorData['suhu_c'] ?? 0;
                $suhuTh = $thresholds['temperature'] ?? null;
                if ($suhuTh) {
                    if ($temp < ($suhuTh['danger_low'] ?? 20) || $temp > ($suhuTh['danger_high'] ?? 37)) {
                        $criticalIssues++;
                    } elseif ($temp < ($suhuTh['ideal_min'] ?? 23) || $temp > ($suhuTh['ideal_max'] ?? 34)) {
                        $warnings++;
                    }
                }
                
                $humidity = $sensorData['humidity'] ?? $sensorData['kelembaban_rh'] ?? 0;
                $kelembabanTh = $thresholds['humidity'] ?? null;
                if ($kelembabanTh) {
                    if ($humidity > ($kelembabanTh['danger_high'] ?? 80)) {
                        $criticalIssues++;
                    } elseif ($humidity < ($kelembabanTh['ideal_min'] ?? 50) || $humidity > ($kelembabanTh['warn_high'] ?? 80)) {
                        $warnings++;
                    }
                }
                
                $amonia = $sensorData['ammonia'] ?? $sensorData['amonia_ppm'] ?? 0;
                $amoniaTh = $thresholds['ammonia'] ?? null;
                if ($amoniaTh) {
                    if ($amonia > ($amoniaTh['danger_max'] ?? 35)) {
                        $criticalIssues++;
                    } elseif ($amonia >= ($amoniaTh['warn_max'] ?? 35)) {
                        $warnings++;
                    }
                }
                
                // Cahaya: SAMA PERSIS DENGAN WEB MONITORING calculateThresholdScore
                // Di web monitoring, calculateThresholdScore TIDAK membagi 10 untuk cahaya
                // Hanya threshold validation yang membagi 10
                $cahaya = $sensorData['light'] ?? $sensorData['cahaya_lux'] ?? 0;
                $cahayaTh = $thresholds['light'] ?? null;
                if ($cahayaTh) {
                    if ($cahaya < ($cahayaTh['warn_low'] ?? 10) || $cahaya > ($cahayaTh['warn_high'] ?? 60)) {
                        $criticalIssues++;
                    } elseif ($cahaya < ($cahayaTh['ideal_low'] ?? 20) || $cahaya > ($cahayaTh['ideal_high'] ?? 40)) {
                        $warnings++;
                    }
                }
                
                // Hitung probability berdasarkan threshold validation
                $thresholdProb = ['BAIK' => 0, 'PERHATIAN' => 0, 'BURUK' => 0];
                
                if ($criticalIssues >= 3) {
                    $thresholdProb['BURUK'] = 0.9;
                    $thresholdProb['PERHATIAN'] = 0.1;
                    $thresholdProb['BAIK'] = 0.0;
                } elseif ($criticalIssues >= 2) {
                    $thresholdProb['BURUK'] = 0.7;
                    $thresholdProb['PERHATIAN'] = 0.3;
                    $thresholdProb['BAIK'] = 0.0;
                } elseif ($criticalIssues >= 1 || $warnings >= 2) {
                    $thresholdProb['BURUK'] = 0.3;
                    $thresholdProb['PERHATIAN'] = 0.6;
                    $thresholdProb['BAIK'] = 0.1;
                } elseif ($warnings >= 1) {
                    $thresholdProb['PERHATIAN'] = 0.8;
                    $thresholdProb['BAIK'] = 0.2;
                    $thresholdProb['BURUK'] = 0.0;
                } else {
                    // Semua sensor dalam range ideal
                    $thresholdProb['BAIK'] = 0.95;
                    $thresholdProb['PERHATIAN'] = 0.05;
                    $thresholdProb['BURUK'] = 0.0;
                }
                
                return $thresholdProb;
            };
            
            // Helper function: Adjust probabilities (same as web monitoring)
            $adjustProbabilitiesBasedOnThreshold = function($mlProbabilities, $thresholdScore, $sensorData, $thresholds) {
                $baseProb = [
                    'BAIK' => (float)($mlProbabilities['BAIK'] ?? 0),
                    'PERHATIAN' => (float)($mlProbabilities['PERHATIAN'] ?? 0),
                    'BURUK' => (float)($mlProbabilities['BURUK'] ?? 0)
                ];
                
                // Combine ML probability dengan threshold score (weighted)
                $mlWeight = 0.6;  // 60% dari ML
                $thresholdWeight = 0.4;  // 40% dari threshold validation
                
                $adjustedProb = [
                    'BAIK' => ($baseProb['BAIK'] * $mlWeight) + ($thresholdScore['BAIK'] * $thresholdWeight),
                    'PERHATIAN' => ($baseProb['PERHATIAN'] * $mlWeight) + ($thresholdScore['PERHATIAN'] * $thresholdWeight),
                    'BURUK' => ($baseProb['BURUK'] * $mlWeight) + ($thresholdScore['BURUK'] * $thresholdWeight)
                ];
                
                // Normalize (pastikan total = 1.0)
                $total = array_sum($adjustedProb);
                if ($total > 0) {
                    foreach ($adjustedProb as $key => $value) {
                        $adjustedProb[$key] = $value / $total;
                    }
                }
                
                return $adjustedProb;
            };
            
            // Get ML probabilities (original dari model)
            $mlProbabilities = $mlStatus['probability'] ?? [
                'BAIK' => 0.0,
                'PERHATIAN' => 0.0,
                'BURUK' => 0.0
            ];
            
            // Calculate threshold score
            if (empty($thresholds)) {
                $thresholdScore = ['BAIK' => 0.95, 'PERHATIAN' => 0.05, 'BURUK' => 0.0];
            } else {
                $thresholdScore = $calculateThresholdScore($latest, $thresholds);
            }
            
            // Adjust probability (combine ML + Threshold)
            if (empty($thresholds)) {
                $adjustedProbabilities = $mlProbabilities;
            } else {
                $adjustedProbabilities = $adjustProbabilitiesBasedOnThreshold(
                    $mlProbabilities,
                    $thresholdScore,
                    $latest,
                    $thresholds
                );
            }
            
            // Determine final status dari adjusted probability (SAME AS WEB MONITORING)
            $finalStatusFromAdjustedProb = array_search(max($adjustedProbabilities), $adjustedProbabilities);
            $finalConfidenceFromAdjustedProb = max($adjustedProbabilities);
            
            // ============================================
            // THRESHOLD VALIDATION & HYBRID DECISION (SAME AS WEB MONITORING)
            // ============================================
            // Apply threshold validation untuk safety override
            $thresholdBasedLabel = 'baik';
            $thresholdIssues = 0;
            $criticalThresholdIssues = 0;
            
            if (!empty($thresholds) && !empty($latest)) {
                $temp = (float) ($latest['temperature'] ?? 0);
                $humid = (float) ($latest['humidity'] ?? 0);
                $ammonia = (float) ($latest['ammonia'] ?? 0);
                $light = (float) ($latest['light'] ?? 0);
                
                // Ambil threshold values
                $tempIdealMin = (float) ($thresholds['temperature']['ideal_min'] ?? 23);
                $tempIdealMax = (float) ($thresholds['temperature']['ideal_max'] ?? 34);
                $tempDangerLow = (float) ($thresholds['temperature']['danger_low'] ?? 20);
                $tempDangerHigh = (float) ($thresholds['temperature']['danger_high'] ?? 37);
                
                $humidIdealMin = (float) ($thresholds['humidity']['ideal_min'] ?? 50);
                $humidIdealMax = (float) ($thresholds['humidity']['ideal_max'] ?? 70);
                $humidWarnLow = (float) ($thresholds['humidity']['warn_low'] ?? 50);
                $humidWarnHigh = (float) ($thresholds['humidity']['warn_high'] ?? 80);
                $humidDangerHigh = (float) ($thresholds['humidity']['danger_high'] ?? 80);
                
                $ammoniaIdealMax = (float) ($thresholds['ammonia']['ideal_max'] ?? 20);
                $ammoniaWarnMax = (float) ($thresholds['ammonia']['warn_max'] ?? 35);
                $ammoniaDangerMax = (float) ($thresholds['ammonia']['danger_max'] ?? 35);
                
                $lightIdealLow = (float) ($thresholds['light']['ideal_low'] ?? 20);
                $lightIdealHigh = (float) ($thresholds['light']['ideal_high'] ?? 40);
                $lightWarnLow = (float) ($thresholds['light']['warn_low'] ?? 10);
                $lightWarnHigh = (float) ($thresholds['light']['warn_high'] ?? 60);
                
                // Validasi threshold (SAMA DENGAN WEB MONITORING)
                // Suhu
                if ($temp >= $tempIdealMin && $temp <= $tempIdealMax) {
                    // AMAN
                } elseif ($temp < $tempDangerLow || $temp > $tempDangerHigh) {
                    $thresholdIssues++;
                    $criticalThresholdIssues++;
                    $thresholdBasedLabel = 'buruk';
                } else {
                    $thresholdIssues++;
                    if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
                }
                
                // Kelembaban
                if ($humid >= $humidIdealMin && $humid <= $humidIdealMax) {
                    // AMAN
                } elseif ($humid > $humidDangerHigh) {
                    $thresholdIssues++;
                    $criticalThresholdIssues++;
                    $thresholdBasedLabel = 'buruk';
                } elseif ($humid < $humidWarnLow || ($humid > $humidIdealMax && $humid <= $humidWarnHigh)) {
                    $thresholdIssues++;
                    if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
                }
                
                // Amoniak
                if ($ammonia <= $ammoniaIdealMax) {
                    // AMAN
                } elseif ($ammonia > $ammoniaDangerMax) {
                    $thresholdIssues++;
                    $criticalThresholdIssues++;
                    $thresholdBasedLabel = 'buruk';
                } elseif ($ammonia > $ammoniaWarnMax) {
                    $thresholdIssues++;
                    if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
                }
                
                // Cahaya (konversi dari ratusan ke puluhan - SAMA DENGAN WEB MONITORING)
                $lightForCheck = $light / 10;
                if ($lightForCheck >= $lightIdealLow && $lightForCheck <= $lightIdealHigh) {
                    // AMAN
                } elseif ($lightForCheck < $lightWarnLow || $lightForCheck > $lightWarnHigh) {
                    $thresholdIssues++;
                    $criticalThresholdIssues++;
                    $thresholdBasedLabel = 'buruk';
                } else {
                    $thresholdIssues++;
                    if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
                }
                
                // Jika ada 3+ sensor di luar batas, pastikan status adalah BURUK
                if ($thresholdIssues >= 3) {
                    $thresholdBasedLabel = 'buruk';
                }
            }
            
            // Determine final status dengan safety override (SAMA PERSIS DENGAN WEB MONITORING)
            $adjustedStatusLabel = strtoupper($finalStatusFromAdjustedProb ?? 'BAIK');
            $thresholdLabel = strtoupper($thresholdBasedLabel);
            
            // Helper function: Calculate Agreement Score (SAMA PERSIS DENGAN WEB MONITORING)
            $calculateAgreement = function($mlStatus, $thresholdStatus, $mlConfidence) {
                $statusValue = ['BAIK' => 0, 'PERHATIAN' => 1, 'BURUK' => 2];
                $mlLabel = strtoupper($mlStatus['label'] ?? 'BAIK');
                $thresholdLabel = strtoupper($thresholdStatus);
                
                $mlValue = $statusValue[$mlLabel] ?? 1;
                $thresholdValue = $statusValue[$thresholdLabel] ?? 1;
                
                if ($mlValue == $thresholdValue) {
                    return 1.0;
                }
                
                if (abs($mlValue - $thresholdValue) == 1) {
                    if ($thresholdValue > $mlValue) {
                        return 0.3; // Threshold lebih kritis, agreement rendah
                    } else {
                        return 0.6; // ML lebih kritis, agreement medium
                    }
                }
                
                return 0.1; // Agreement sangat rendah
            };
            
            // Calculate Agreement Score (SAMA PERSIS DENGAN WEB MONITORING)
            $agreementScore = $calculateAgreement($mlStatus, $thresholdLabel, (float)($mlStatus['confidence'] ?? 0.7));
            
            // Safety override: Jika ada 3+ critical issues, HARUS BURUK
            if ($criticalThresholdIssues >= 3) {
                $finalStatusLabel = 'BURUK';
                Log::info('→ Decision: BURUK (safety override - 3+ critical issues)');
            }
            // Safety override: Jika semua sensor aman (0 issues), HARUS BAIK
            elseif ($thresholdIssues == 0 && $criticalThresholdIssues == 0) {
                $finalStatusLabel = 'BAIK';
                Log::info('→ Decision: BAIK (safety override - semua sensor aman)');
            }
            // Gunakan adjusted probability sebagai primary decision
            else {
                $finalStatusLabel = $adjustedStatusLabel;
                Log::info('→ Decision: ' . $finalStatusLabel . ' (from adjusted probabilities)');
            }
            
            // Calculate final confidence dengan boost/penalty (SAMA PERSIS DENGAN WEB MONITORING)
            $baseConfidence = $finalConfidenceFromAdjustedProb;
            
            // BOOST 1: Jika adjusted probability sesuai dengan threshold validation
            if (strtoupper($thresholdLabel) == $finalStatusLabel) {
                $baseConfidence = min($baseConfidence + 0.15, 1.0); // Boost lebih besar
            }
            
            // BOOST 2: Untuk status BAIK dengan semua sensor aman (0 issues)
            if ($finalStatusLabel === 'BAIK' && $thresholdIssues == 0 && $criticalThresholdIssues == 0) {
                $baseConfidence = min($baseConfidence + 0.25, 1.0); // Boost besar untuk BAIK
            }
            
            // BOOST 3: Agreement tinggi antara ML dan threshold (SAMA PERSIS DENGAN WEB MONITORING)
            if ($agreementScore >= 0.8) {
                $baseConfidence = min($baseConfidence + 0.1, 1.0);
            }
            
            // PENALTY: Hanya untuk critical cases yang berbeda dengan threshold validation
            if (strtoupper($thresholdLabel) != $finalStatusLabel && $criticalThresholdIssues >= 2) {
                $baseConfidence = max($baseConfidence - 0.2, 0.3);
            }
            
            // PENALTY: Agreement sangat rendah (SAMA PERSIS DENGAN WEB MONITORING)
            if ($agreementScore < 0.2) {
                $baseConfidence = max($baseConfidence - 0.15, 0.3);
            }
            
            $finalConfidence = round($baseConfidence, 2);
            
            // Update status dengan final decision
            $status = $mlStatus;
            $status['label'] = strtolower($finalStatusLabel);
            $status['confidence'] = $finalConfidence;
            $status['probability'] = $adjustedProbabilities;
            
            // Generate forecast_summary_6h - SAMA PERSIS DENGAN WEB MONITORING
            // Gunakan dari ML service jika ada (SAMA PERSIS dengan web monitoring)
            // Web monitoring menggunakan: isset($mlResults) && isset($mlResults['forecast_summary_6h']) ? $mlResults['forecast_summary_6h'] : $forecast6Summary
            $forecast6Summary = null;
            if (!empty($mlResults) && isset($mlResults['forecast_summary_6h']) && is_array($mlResults['forecast_summary_6h'])) {
                $forecast6Summary = $mlResults['forecast_summary_6h'];
            } elseif (!empty($forecast6SummaryFromML) && is_array($forecast6SummaryFromML)) {
                $forecast6Summary = $forecast6SummaryFromML;
            }
            if (empty($forecast6Summary) && !empty($pred6) && is_array($pred6)) {
                // Generate forecast summary menggunakan threshold dari database (SAMA PERSIS dengan web monitoring)
                // Get threshold values dari database
                $tempMin = isset($thresholds['temperature']['ideal_min']) ? (float)$thresholds['temperature']['ideal_min'] : 23;
                $tempMax = isset($thresholds['temperature']['ideal_max']) ? (float)$thresholds['temperature']['ideal_max'] : 34;
                $humMin = isset($thresholds['humidity']['ideal_min']) ? (float)$thresholds['humidity']['ideal_min'] : 50;
                $humMax = isset($thresholds['humidity']['ideal_max']) ? (float)$thresholds['humidity']['ideal_max'] : 70;
                $ammMax = isset($thresholds['ammonia']['ideal_max']) ? (float)$thresholds['ammonia']['ideal_max'] : 20;
                $lightIdealLow = isset($thresholds['light']['ideal_low']) ? (float)$thresholds['light']['ideal_low'] : 20;
                $lightIdealHigh = isset($thresholds['light']['ideal_high']) ? (float)$thresholds['light']['ideal_high'] : 40;
                $lightWarnLow = isset($thresholds['light']['warn_low']) ? (float)$thresholds['light']['warn_low'] : 10;
                $lightWarnHigh = isset($thresholds['light']['warn_high']) ? (float)$thresholds['light']['warn_high'] : 60;
                
                // qualitativeForecast - SAMA PERSIS dengan web monitoring
                $qualitativeForecast = function($series, $metric, $unit, $safeLow, $safeHigh) {
                    if (!is_array($series) || empty($series)) {
                        return [
                            'metric' => $metric,
                            'summary' => "$metric: Data tidak tersedia",
                            'range' => ['min' => 0, 'max' => 0, 'unit' => $unit],
                            'trend' => 'tidak diketahui',
                            'risk' => 'tidak diketahui'
                        ];
                    }
                    
                    $numericSeries = array_values(array_filter($series, function($v) {
                        return is_numeric($v);
                    }));
                    
                    if (empty($numericSeries)) {
                        return [
                            'metric' => $metric,
                            'summary' => "$metric: Data tidak valid",
                            'range' => ['min' => 0, 'max' => 0, 'unit' => $unit],
                            'trend' => 'tidak diketahui',
                            'risk' => 'tidak diketahui'
                        ];
                    }
                    
                    $min = min($numericSeries);
                    $max = max($numericSeries);
                    $firstValue = $numericSeries[0];
                    $lastValue = end($numericSeries);
                    $trend = $lastValue - $firstValue;
                    // Threshold untuk trend: 0.5 untuk suhu/kelembaban/amoniak - SAMA DENGAN WEB MONITORING
                    $dir = $trend > 0.5 ? 'meningkat' : ($trend < -0.5 ? 'menurun' : 'stabil');
                    $risk = ($min < $safeLow || $max > $safeHigh) ? 'potensi keluar batas aman' : 'dalam kisaran aman';
                    
                    // Format summary: "Suhu stabil (32.82–33.22 °C) dalam kisaran aman" - SAMA DENGAN WEB MONITORING
                    return [
                        'metric' => $metric,
                        'summary' => "$metric $dir (" . round($min, 2) . "–" . round($max, 2) . " $unit) $risk",
                        'range' => ['min' => round($min, 2), 'max' => round($max, 2), 'unit' => $unit],
                        'trend' => $dir,
                        'risk' => $risk
                    ];
                };
                
                // generateLightForecast - SAMA PERSIS dengan web monitoring
                // Di web monitoring, checkLightRisk TIDAK mengkonversi nilai cahaya (tetap ratusan)
                // Tapi threshold yang digunakan adalah 10-60 (dalam puluhan)
                // Jadi kita perlu mengkonversi untuk pengecekan threshold
                $checkLightRisk = function($lightValues) use ($lightWarnLow, $lightWarnHigh, $lightIdealLow, $lightIdealHigh) {
                    if (empty($lightValues) || !is_array($lightValues)) {
                        return 'tidak diketahui';
                    }
                    // Konversi dari ratusan ke puluhan untuk pengecekan threshold (SAMA DENGAN WEB MONITORING)
                    // Web monitoring menggunakan threshold 10-60 (dalam puluhan), jadi nilai ratusan harus dikonversi
                    $convertedValues = array_map(function($v) {
                        return is_numeric($v) ? (float)$v / 10 : 0;
                    }, $lightValues);
                    
                    $min = min($convertedValues);
                    $max = max($convertedValues);
                    
                    if ($min < $lightWarnLow || $max > $lightWarnHigh) {
                        return 'di luar batas aman';
                    }
                    if ($min < $lightIdealLow || $max > $lightIdealHigh) {
                        return 'potensi keluar batas aman';
                    }
                    return 'dalam kisaran aman';
                };
                
                $generateLightForecast = function($lightValues, $metric, $unit) use ($checkLightRisk) {
                    if (empty($lightValues) || !is_array($lightValues)) {
                        return [
                            'metric' => $metric,
                            'summary' => "$metric: Data tidak tersedia",
                            'range' => ['min' => 0, 'max' => 0, 'unit' => $unit],
                            'trend' => 'tidak diketahui',
                            'risk' => 'tidak diketahui'
                        ];
                    }
                    
                    // Display tetap menggunakan nilai ratusan (sesuai data aktual)
                    $numericSeries = array_values(array_filter($lightValues, function($v) {
                        return is_numeric($v);
                    }));
                    
                    if (empty($numericSeries)) {
                        return [
                            'metric' => $metric,
                            'summary' => "$metric: Data tidak valid",
                            'range' => ['min' => 0, 'max' => 0, 'unit' => $unit],
                            'trend' => 'tidak diketahui',
                            'risk' => 'tidak diketahui'
                        ];
                    }
                    
                    // Display tetap menggunakan nilai ratusan (sesuai data aktual) - SAMA DENGAN WEB MONITORING
                    $min = min($numericSeries);
                    $max = max($numericSeries);
                    $firstValue = $numericSeries[0];
                    $lastValue = end($numericSeries);
                    $trend = $lastValue - $firstValue;
                    // Threshold untuk trend: 5 untuk cahaya (karena dalam ratusan), sama dengan web monitoring
                    $dir = $trend > 5 ? 'meningkat' : ($trend < -5 ? 'menurun' : 'stabil');
                    $risk = $checkLightRisk($lightValues);
                    
                    // Format summary: "Cahaya stabil (56.6–57.4 lux) potensi keluar batas aman" - SAMA DENGAN WEB MONITORING
                    $riskText = $risk === 'dalam kisaran aman' ? 'dalam kisaran aman' : ($risk === 'di luar batas aman' ? 'di luar batas aman' : 'potensi keluar batas aman');
                    
                    return [
                        'metric' => $metric,
                        'summary' => "$metric $dir (" . round($min, 2) . "–" . round($max, 2) . " $unit) $riskText",
                        'range' => ['min' => round($min, 2), 'max' => round($max, 2), 'unit' => $unit],
                        'trend' => $dir,
                        'risk' => $riskText
                    ];
                };
                
                // Generate forecast summary - SAMA PERSIS dengan web monitoring
                $forecast6Summary = [
                    $qualitativeForecast($pred6['temperature'] ?? [], 'Suhu', '°C', $tempMin, $tempMax),
                    $qualitativeForecast($pred6['humidity'] ?? [], 'Kelembaban', '%', $humMin, $humMax),
                    $qualitativeForecast($pred6['ammonia'] ?? [], 'Amoniak', 'ppm', 0, $ammMax),
                    $generateLightForecast($pred6['light'] ?? [], 'Cahaya', 'lux')
                ];
                
                // Generate forecast_summary_24h - SAMA PERSIS dengan web monitoring
                $forecast24Summary = [
                    $qualitativeForecast($pred24['temperature'] ?? [], 'Suhu', '°C', $tempMin, $tempMax),
                    $qualitativeForecast($pred24['humidity'] ?? [], 'Kelembaban', '%', $humMin, $humMax),
                    $qualitativeForecast($pred24['ammonia'] ?? [], 'Amoniak', 'ppm', 0, $ammMax),
                    $generateLightForecast($pred24['light'] ?? [], 'Cahaya', 'lux')
                ];
            } else {
                // Jika forecast_summary_6h tidak ada, set forecast24Summary juga null
                $forecast24Summary = null;
            }
            
            // Jika forecast_summary_24h dari ML service ada, gunakan itu
            if (!empty($mlResults) && isset($mlResults['forecast_summary_24h']) && is_array($mlResults['forecast_summary_24h'])) {
                $forecast24Summary = $mlResults['forecast_summary_24h'];
            } elseif (!empty($forecast24SummaryFromML) && is_array($forecast24SummaryFromML)) {
                $forecast24Summary = $forecast24SummaryFromML;
            }
            
            // Log untuk debugging - SAMA PERSIS DENGAN WEB MONITORING
            Log::info('=== TELEGRAM NOTIFICATION - STATUS DETERMINATION ===', [
                'ml_probabilities_original' => $mlProbabilities,
                'threshold_score' => $thresholdScore,
                'adjusted_probabilities' => $adjustedProbabilities,
                'final_status_label' => $finalStatusLabel,
                'final_status_lowercase' => $status['label'],
                'final_confidence_from_adjusted_prob' => $finalConfidenceFromAdjustedProb,
                'agreement_score' => $agreementScore,
                'threshold_label' => $thresholdLabel,
                'threshold_issues' => $thresholdIssues,
                'critical_threshold_issues' => $criticalThresholdIssues,
                'base_confidence' => $baseConfidence,
                'final_confidence' => $finalConfidence,
                'forecast_summary_6h' => $forecast6Summary,
                'thresholds_used' => $thresholds
            ]);

            // Check status label dan tentukan interval
            $statusLabel = strtolower($status['label'] ?? 'tidak diketahui');
            $isUrgent = ($statusLabel === 'perhatian' || $statusLabel === 'buruk');
            
            // Log status untuk debugging
            $wibTime = now()->setTimezone('Asia/Jakarta');
            $this->info('📊 Status kandang: ' . strtoupper($statusLabel) . ' (Waktu: ' . $wibTime->format('H:i:s') . ' WIB)');
            Log::info('Telegram notification check', [
                'time' => $wibTime->format('Y-m-d H:i:s') . ' WIB',
                'status' => $statusLabel,
                'is_urgent' => $isUrgent,
                'enabled' => $telegramEnabled,
                'has_credentials' => !empty($botToken) && !empty($chatId)
            ]);
            
            // Tentukan apakah perlu kirim notifikasi berdasarkan interval dan kondisi
            // TESTING MODE: Skip interval check, langsung kirim setiap kali dipanggil
            $isTestMode = $this->option('test');
            
            if (!$isTestMode) {
                // Normal mode: Cek interval
                $urgentIntervalSeconds = 300; // 5 menit untuk normal mode
                $normalIntervalSeconds = 3600; // 1 jam untuk normal mode
                
                if ($isUrgent) {
                    // Kondisi PERHATIAN atau BURUK: kirim setiap 5 menit sampai kondisi membaik
                    if (!$state['urgent_mode']) {
                        $state['urgent_mode'] = true;
                        $state['last_urgent_notification'] = time();
                        $this->info('🚨 Masuk mode URGENT - Status ' . strtoupper($statusLabel) . ' - Notifikasi akan dikirim setiap 5 menit');
                    }
                    
                    $secondsSinceLast = time() - $state['last_notification'];
                    if ($secondsSinceLast < $urgentIntervalSeconds) {
                        $this->info('⏸️ Kondisi ' . strtoupper($statusLabel) . ' - Tunggu 5 menit sejak notifikasi terakhir (' . round($secondsSinceLast / 60, 1) . ' menit)');
                        return 0;
                    }
                    
                    $this->info('⚠️ Kondisi ' . strtoupper($statusLabel) . ' - Kirim notifikasi urgent (setiap 5 menit sampai kondisi membaik)');
                } else {
                    // Kondisi BAIK: kirim 1 jam sekali
                    if ($state['urgent_mode']) {
                        $state['urgent_mode'] = false;
                        $this->info('✅ Keluar dari mode urgent - Status BAIK');
                    }
                    
                    $secondsSinceLast = time() - $state['last_notification'];
                    if ($secondsSinceLast < $normalIntervalSeconds) {
                        $this->info('⏸️ Kondisi BAIK - Tunggu 1 jam sejak notifikasi terakhir (' . round($secondsSinceLast / 60, 1) . ' menit)');
                        return 0;
                    }
                    
                    $this->info('✅ Kondisi BAIK - Kirim laporan rutin (interval 1 jam)');
                }
            } else {
                // TESTING MODE: Skip interval check, langsung kirim
                $this->info('🧪 TESTING MODE - Status ' . strtoupper($statusLabel) . ' - Kirim notifikasi (skip interval check)');
            }

            // Get history 24 jam terakhir untuk trend analysis
            $history24h = [];
            $history24Readings = SensorReading::orderBy('recorded_at', 'desc')
                ->limit(24)
                ->get()
                ->reverse(); // Reverse untuk mendapatkan dari yang paling lama ke terbaru
            
            foreach ($history24Readings as $reading) {
                $history24h[] = [
                    'temperature' => (float) $reading->suhu_c,
                    'humidity' => (float) $reading->kelembaban_rh,
                    'ammonia' => (float) $reading->amonia_ppm,
                    'light' => (float) $reading->cahaya_lux
                ];
            }
            
            // Send Telegram notification - pass thresholds, forecast_summary_24h, dan history 24h juga
            $telegramService = new TelegramNotificationService();
            $sent = $telegramService->sendMonitoringNotification(
                $latest,
                $status,
                $pred6,
                $anomalies,
                $forecast6Summary,
                $thresholds, // Pass thresholds untuk validasi status sensor
                $history24h, // Pass history 24h untuk trend analysis
                $forecast24Summary ?? null // Pass forecast_summary_24h
            );

            if ($sent) {
                // Update state setelah berhasil kirim
                $state['last_notification'] = time();
                if ($isUrgent) {
                    $state['last_urgent_notification'] = time();
                }
                $this->saveNotificationState($stateFile, $state);
                
                $wibTime = now()->setTimezone('Asia/Jakarta');
                $urgentInfo = $isUrgent ? ' [Urgent - setiap 10 detik - TESTING]' : ' [Rutin - setiap 30 detik - TESTING]';
                $this->info('✅ Telegram notification sent successfully at ' . $wibTime->format('Y-m-d H:i:s') . ' WIB (Status: ' . strtoupper($statusLabel) . $urgentInfo . ')');
                Log::info('Telegram notification sent successfully', [
                    'time' => $wibTime->format('Y-m-d H:i:s') . ' WIB',
                    'status' => $statusLabel,
                    'is_urgent' => $isUrgent,
                    'reason' => $isUrgent ? 'Kondisi kandang memerlukan perhatian urgent - notifikasi setiap 10 detik sampai kondisi membaik (TESTING MODE)' : 'Laporan rutin setiap 30 detik (TESTING MODE)'
                ]);
                return 0;
            } else {
                $wibTime = now()->setTimezone('Asia/Jakarta');
                $this->error('❌ Failed to send Telegram notification');
                $this->warn('💡 Cek log di storage/logs/laravel.log untuk detail error');
                Log::error('Failed to send Telegram notification', [
                    'time' => $wibTime->format('Y-m-d H:i:s') . ' WIB',
                    'status' => $statusLabel,
                    'latest_data' => $latest,
                    'has_thresholds' => !empty($thresholds)
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
    
    /**
     * Get notification state dari file
     */
    protected function getNotificationState($stateFile)
    {
        $default = [
            'last_notification' => 0,
            'last_urgent_notification' => 0,
            'urgent_mode' => false
        ];
        
        if (!file_exists($stateFile)) {
            return $default;
        }
        
        $content = file_get_contents($stateFile);
        if ($content === false) {
            return $default;
        }
        
        $state = json_decode($content, true);
        if (!is_array($state)) {
            return $default;
        }
        
        return array_merge($default, $state);
    }
    
    /**
     * Save notification state ke file
     */
    protected function saveNotificationState($stateFile, $state)
    {
        $dir = dirname($stateFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT));
    }
}

