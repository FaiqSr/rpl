<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Route::middleware(['auth:sanctum'])->group(function () {
Route::prefix('article')->name('article.')->group(function () {
    Route::get('/', [ArticleController::class, 'CartByUserJson']);
});

Route::prefix('cart')->name('cart')->group(function () {
    Route::get('/', [CartController::class, 'CartByUserJson'])->name('cartGetAllJson');
});

// ML Service Routes
Route::prefix('sensor')->name('sensor.')->group(function () {
    Route::post('/analyze', [\App\Http\Controllers\SensorController::class, 'analyze'])->name('analyze');
});

// Telegram Settings Routes
Route::prefix('telegram')->name('telegram.')->group(function () {
    Route::post('/test', function (Request $request) {
        try {
            $request->validate([
                'bot_token' => 'required|string',
                'chat_id' => 'required|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Token Bot dan Chat ID harus diisi'], 400);
        }
        
        $botToken = trim($request->bot_token);
        $chatId = trim($request->chat_id);
        
        // Remove quotes if present
        $botToken = trim($botToken, '"\'');
        $chatId = trim($chatId, '"\'');
        
        // Validate token format (should be numeric:token)
        if (!preg_match('/^\d+:[A-Za-z0-9_-]+$/', $botToken)) {
            return response()->json(['success' => false, 'message' => 'Format token tidak valid. Token harus dalam format: 123456789:ABC-DEF1234ghIkl-zyx57W2v1u123ew11'], 400);
        }
        
        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getMe");
            
            // Handle HTTP errors first
            if ($response->failed()) {
                $errorBody = $response->json();
                $errorDescription = $errorBody['description'] ?? 'Gagal menghubungi Telegram API';
                $errorCode = $errorBody['error_code'] ?? $response->status();
                
                // Provide more helpful error messages
                if ($errorCode === 401) {
                    $errorDescription = 'Token tidak valid atau sudah expired. Silakan buat token baru dari @BotFather.';
                } elseif ($errorCode === 400) {
                    $errorDescription = 'Format token tidak valid. Pastikan token dalam format: 123456789:ABC-DEF1234...';
                }
                
                Log::error('Telegram Test HTTP Error', [
                    'status' => $response->status(),
                    'error' => $errorDescription,
                    'error_code' => $errorCode,
                    'response' => $errorBody,
                    'token_prefix' => substr($botToken, 0, 10) . '...'
                ]);
                return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
            }
            
            $data = $response->json();
            
            if (isset($data['ok']) && $data['ok'] === true) {
                // Also verify chat_id by trying to get chat info
                try {
                    $chatResponse = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getChat", [
                        'chat_id' => $chatId
                    ]);
                    
                    if ($chatResponse->failed()) {
                        $chatErrorBody = $chatResponse->json();
                        $chatError = $chatErrorBody['description'] ?? 'Chat ID tidak valid';
                        Log::warning('Telegram Chat ID Error', ['error' => $chatError, 'chat_id' => $chatId, 'response' => $chatErrorBody]);
                        return response()->json(['success' => false, 'message' => 'Bot token valid, tapi Chat ID error: ' . $chatError], 400);
                    }
                    
                    $chatData = $chatResponse->json();
                    if (isset($chatData['ok']) && $chatData['ok'] === true) {
                        return response()->json(['success' => true, 'message' => 'Bot token dan Chat ID valid']);
                    } else {
                        $chatError = $chatData['description'] ?? 'Chat ID tidak valid';
                        Log::warning('Telegram Chat ID Error', ['error' => $chatError, 'chat_id' => $chatId, 'response' => $chatData]);
                        return response()->json(['success' => false, 'message' => 'Bot token valid, tapi Chat ID error: ' . $chatError], 400);
                    }
                } catch (\Exception $e) {
                    // If chat_id check fails, still return success for bot token
                    Log::warning('Telegram Chat ID Check Failed', ['error' => $e->getMessage()]);
                    return response()->json(['success' => true, 'message' => 'Bot token valid, Chat ID perlu diverifikasi']);
                }
            }
            
            $errorDescription = $data['description'] ?? 'Bot token tidak valid';
            $errorCode = $data['error_code'] ?? 0;
            
            // Provide more helpful error messages
            if ($errorCode === 401) {
                $errorDescription = 'Token tidak valid atau sudah expired. Silakan buat token baru dari @BotFather.';
            } elseif ($errorCode === 400) {
                $errorDescription = 'Format token tidak valid. Pastikan token dalam format: 123456789:ABC-DEF1234...';
            }
            
            Log::error('Telegram Test Error', ['error' => $errorDescription, 'error_code' => $errorCode, 'response' => $data, 'token_prefix' => substr($botToken, 0, 10) . '...']);
            return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Telegram Test Connection Error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Tidak dapat terhubung ke Telegram API. Periksa koneksi internet.'], 500);
        } catch (\Exception $e) {
            Log::error('Telegram Test Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    })->name('test');
    
    Route::post('/send-test', function (Request $request) {
        try {
            $request->validate([
                'bot_token' => 'required|string',
                'chat_id' => 'required|string',
                'message' => 'required|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Semua field harus diisi'], 400);
        }
        
        $botToken = trim($request->bot_token);
        $chatId = trim($request->chat_id);
        
        // Remove quotes if present
        $botToken = trim($botToken, '"\'');
        $chatId = trim($chatId, '"\'');
        
        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $request->message,
                'parse_mode' => 'HTML'
            ]);
            
            // Handle HTTP errors first
            if ($response->failed()) {
                $errorBody = $response->json();
                $errorDescription = $errorBody['description'] ?? 'Gagal mengirim pesan';
                $errorCode = $errorBody['error_code'] ?? $response->status();
                
                Log::error('Telegram Send HTTP Error', [
                    'status' => $response->status(),
                    'error' => $errorDescription,
                    'error_code' => $errorCode,
                    'body' => $errorBody
                ]);
                return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
            }
            
            $data = $response->json();
            if (isset($data['ok']) && $data['ok'] === true) {
                return response()->json(['success' => true, 'message' => 'Pesan berhasil dikirim']);
            }
            
            // If response is successful but ok is false, get error description
            $errorDescription = $data['description'] ?? 'Gagal mengirim pesan';
            $errorCode = $data['error_code'] ?? 0;
            Log::error('Telegram API Error', ['response' => $data, 'error_code' => $errorCode]);
            return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Telegram Connection Error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Tidak dapat terhubung ke Telegram API. Periksa koneksi internet.'], 500);
        } catch (\Exception $e) {
            Log::error('Telegram Send Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    })->name('send-test');
    
    Route::post('/save', function (Request $request) {
        $request->validate([
            'bot_token' => 'required|string',
            'chat_id' => 'required|string'
        ]);
        
        try {
            // Update .env file
            $envFile = base_path('.env');
            
            if (!file_exists($envFile)) {
                return response()->json(['success' => false, 'message' => 'File .env tidak ditemukan'], 500);
            }
            
            if (!is_writable($envFile)) {
                return response()->json(['success' => false, 'message' => 'File .env tidak dapat ditulis. Periksa permission file.'], 500);
            }
            
            $envContent = file_get_contents($envFile);
            
            if ($envContent === false) {
                return response()->json(['success' => false, 'message' => 'Gagal membaca file .env'], 500);
            }
            
            // Escape special characters in token and chat_id for .env file
            $botToken = addslashes($request->bot_token);
            $chatId = addslashes($request->chat_id);
            
            // If token contains special characters, wrap in quotes
            if (preg_match('/[=\s#"]/', $request->bot_token)) {
                $botToken = '"' . str_replace('"', '\\"', $request->bot_token) . '"';
            }
            
            // Update or add TELEGRAM_BOT_TOKEN
            if (preg_match('/^TELEGRAM_BOT_TOKEN=.*/m', $envContent)) {
                $envContent = preg_replace('/^TELEGRAM_BOT_TOKEN=.*/m', "TELEGRAM_BOT_TOKEN={$botToken}", $envContent);
            } else {
                $envContent .= "\nTELEGRAM_BOT_TOKEN={$botToken}";
            }
            
            // Update or add TELEGRAM_CHAT_ID
            if (preg_match('/^TELEGRAM_CHAT_ID=.*/m', $envContent)) {
                $envContent = preg_replace('/^TELEGRAM_CHAT_ID=.*/m', "TELEGRAM_CHAT_ID={$chatId}", $envContent);
            } else {
                $envContent .= "\nTELEGRAM_CHAT_ID={$chatId}";
            }
            
            $result = file_put_contents($envFile, $envContent);
            
            if ($result === false) {
                return response()->json(['success' => false, 'message' => 'Gagal menulis ke file .env'], 500);
            }
            
            // Update config cache
            try {
                \Artisan::call('config:clear');
            } catch (\Exception $e) {
                Log::warning('Failed to clear config cache', ['error' => $e->getMessage()]);
            }
            
            return response()->json(['success' => true, 'message' => 'Pengaturan berhasil disimpan']);
        } catch (\Exception $e) {
            Log::error('Telegram Save Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    })->name('save');
    
    // Toggle notifikasi Telegram (enable/disable)
    Route::post('/toggle-notifications', function (Request $request) {
        try {
            $request->validate([
                'enabled' => 'required|boolean'
            ]);
            
            $envFile = base_path('.env');
            
            if (!file_exists($envFile)) {
                return response()->json(['success' => false, 'message' => 'File .env tidak ditemukan'], 404);
            }
            
            if (!is_writable($envFile)) {
                return response()->json(['success' => false, 'message' => 'File .env tidak dapat ditulis. Periksa permission file.'], 500);
            }
            
            $envContent = file_get_contents($envFile);
            
            if ($envContent === false) {
                return response()->json(['success' => false, 'message' => 'Gagal membaca file .env'], 500);
            }
            
            $enabled = $request->enabled ? 'true' : 'false';
            
            // Update or add TELEGRAM_NOTIFICATIONS_ENABLED
            // Handle both with and without quotes
            if (preg_match('/^TELEGRAM_NOTIFICATIONS_ENABLED\s*=\s*.*/m', $envContent)) {
                $envContent = preg_replace('/^TELEGRAM_NOTIFICATIONS_ENABLED\s*=\s*.*/m', "TELEGRAM_NOTIFICATIONS_ENABLED={$enabled}", $envContent);
            } else {
                // Add new line if file doesn't end with newline
                if (substr($envContent, -1) !== "\n") {
                    $envContent .= "\n";
                }
                $envContent .= "TELEGRAM_NOTIFICATIONS_ENABLED={$enabled}\n";
            }
            
            $result = file_put_contents($envFile, $envContent);
            
            if ($result === false) {
                return response()->json(['success' => false, 'message' => 'Gagal menulis ke file .env'], 500);
            }
            
            // Update config cache
            try {
                \Artisan::call('config:clear');
            } catch (\Exception $e) {
                Log::warning('Failed to clear config cache', ['error' => $e->getMessage()]);
            }
            
            $status = $request->enabled ? 'diaktifkan' : 'dinonaktifkan';
            return response()->json([
                'success' => true, 
                'message' => "Notifikasi Telegram berhasil {$status}",
                'enabled' => $request->enabled
            ]);
        } catch (\Exception $e) {
            Log::error('Telegram Toggle Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    })->name('toggle-notifications');
    
    // Get status notifikasi
    Route::get('/notification-status', function () {
        // Default response
        $defaultResponse = [
            'success' => true,
            'enabled' => true,
            'bot_token' => 'not_configured',
            'chat_id' => 'not_configured',
            'raw_value' => 'true'
        ];
        
        try {
            $envFile = base_path('.env');
            
            // Check if .env file exists and is readable
            if (!file_exists($envFile)) {
                Log::warning('.env file not found', ['file' => $envFile]);
                return response()->json($defaultResponse);
            }
            
            if (!is_readable($envFile)) {
                Log::warning('.env file not readable', ['file' => $envFile]);
                return response()->json($defaultResponse);
            }
            
            // Read .env file with error suppression
            $envContent = @file_get_contents($envFile);
            if ($envContent === false || empty($envContent)) {
                Log::warning('Failed to read .env file content');
                return response()->json($defaultResponse);
            }
            
            // Helper function untuk extract value dari .env content
            $getValue = function($key, $content, $default = null) {
                try {
                    $pattern = '/^' . preg_quote($key, '/') . '\s*=\s*(.*)$/m';
                    if (preg_match($pattern, $content, $matches)) {
                        $value = trim($matches[1]);
                        $value = trim($value, '"\'');
                        return $value;
                    }
                } catch (\Exception $e) {
                    // Silently fail and return default
                }
                return $default;
            };
            
            // Read values from .env
            $enabled = $getValue('TELEGRAM_NOTIFICATIONS_ENABLED', $envContent, 'true');
            $botToken = $getValue('TELEGRAM_BOT_TOKEN', $envContent, '');
            $chatId = $getValue('TELEGRAM_CHAT_ID', $envContent, '');
            
            // Handle string 'true'/'false' and boolean true/false
            $isEnabled = ($enabled === 'true' || $enabled === true || $enabled === '1' || $enabled === 1);
            
            return response()->json([
                'success' => true,
                'enabled' => $isEnabled,
                'bot_token' => !empty($botToken) ? 'configured' : 'not_configured',
                'chat_id' => !empty($chatId) ? 'configured' : 'not_configured',
                'raw_value' => $enabled
            ]);
            
        } catch (\Error $e) {
            // Catch fatal errors
            Log::error('Fatal error reading notification status', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json($defaultResponse);
            
        } catch (\Exception $e) {
            // Catch exceptions
            Log::error('Exception reading notification status', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json($defaultResponse);
            
        } catch (\Throwable $e) {
            // Catch any other throwable
            Log::error('Throwable error reading notification status', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json($defaultResponse);
        }
    })->name('notification-status');
});

// Robot Status API Routes
Route::prefix('robots')->name('robots.')->group(function () {
    Route::get('/status', function () {
        try {
            $robots = \App\Models\Robot::all()->map(function ($robot) {
                return [
                    'robot_id' => $robot->robot_id,
                    'name' => $robot->name,
                    'model' => $robot->model,
                    'location' => $robot->location,
                    'operational_status' => $robot->operational_status,
                    'status_text' => $robot->getStatusText(),
                    'status_badge_class' => $robot->getStatusBadgeClass(),
                    'battery_level' => $robot->battery_level,
                    'last_activity_at' => $robot->last_activity_at?->toIso8601String(),
                    'last_activity_text' => $robot->getLastActivityText(),
                    'current_position' => $robot->current_position,
                    'total_distance_today' => $robot->total_distance_today,
                    'operating_hours_today' => $robot->operating_hours_today,
                    'operating_hours_formatted' => gmdate('H:i', $robot->operating_hours_today * 60),
                    'uptime_percentage' => $robot->uptime_percentage,
                    'health_status' => $robot->health_status,
                    'patrol_count_today' => $robot->patrol_count_today,
                    'health_summary' => $robot->health_status ? 
                        (collect($robot->health_status)->every(fn($v) => $v === 'normal' || $v === 'good') ? 
                            'Semua sistem normal' : 'Perlu perhatian') : 
                        'Tidak diketahui'
                ];
            });
            
            return response()->json([
                'success' => true,
                'robots' => $robots,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching robot status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat status robot',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('status');
    
    // Start Patrol
    Route::post('/{robotId}/start-patrol', function ($robotId) {
        try {
            $robot = \App\Models\Robot::where('robot_id', $robotId)->firstOrFail();
            
            // Validasi: robot harus dalam status yang bisa di-start
            if (!in_array($robot->operational_status, ['idle', 'offline', 'charging'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Robot tidak dapat memulai patrol saat ini'
                ], 400);
            }
            
            // Update status
            $robot->update([
                'operational_status' => 'operating',
                'last_activity_at' => now()
            ]);
            
            // Log activity
            \App\Models\RobotActivity::create([
                'robot_id' => $robotId,
                'activity_type' => 'patrol_start',
                'description' => 'Patrol dimulai secara manual',
                'occurred_at' => now()
            ]);
            
            // TODO: Kirim command ke robot hardware (via MQTT/HTTP/Serial)
            
            return response()->json([
                'success' => true,
                'message' => 'Patrol berhasil dimulai',
                'robot' => $robot->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error starting patrol: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai patrol: ' . $e->getMessage()
            ], 500);
        }
    })->name('start-patrol');
    
    // Stop Patrol
    Route::post('/{robotId}/stop-patrol', function ($robotId) {
        try {
            $robot = \App\Models\Robot::where('robot_id', $robotId)->firstOrFail();
            
            if ($robot->operational_status !== 'operating') {
                return response()->json([
                    'success' => false,
                    'message' => 'Robot tidak sedang beroperasi'
                ], 400);
            }
            
            $robot->update([
                'operational_status' => 'idle',
                'last_activity_at' => now()
            ]);
            
            \App\Models\RobotActivity::create([
                'robot_id' => $robotId,
                'activity_type' => 'patrol_end',
                'description' => 'Patrol dihentikan secara manual',
                'occurred_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Patrol berhasil dihentikan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error stopping patrol: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghentikan patrol: ' . $e->getMessage()
            ], 500);
        }
    })->name('stop-patrol');
    
    // Return to Base
    Route::post('/{robotId}/return-to-base', function ($robotId) {
        try {
            $robot = \App\Models\Robot::where('robot_id', $robotId)->firstOrFail();
            
            $robot->update([
                'operational_status' => 'charging',
                'last_activity_at' => now()
            ]);
            
            \App\Models\RobotActivity::create([
                'robot_id' => $robotId,
                'activity_type' => 'position_update',
                'description' => 'Robot kembali ke base station',
                'position' => 'Base Station',
                'occurred_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Robot sedang kembali ke base station'
            ]);
        } catch (\Exception $e) {
            Log::error('Error returning to base: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    })->name('return-to-base');
    
    // Emergency Stop
    Route::post('/{robotId}/emergency-stop', function ($robotId) {
        try {
            $robot = \App\Models\Robot::where('robot_id', $robotId)->firstOrFail();
            
            $robot->update([
                'operational_status' => 'offline',
                'last_activity_at' => now()
            ]);
            
            \App\Models\RobotActivity::create([
                'robot_id' => $robotId,
                'activity_type' => 'error',
                'description' => 'EMERGENCY STOP - Dihentikan secara manual',
                'occurred_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'EMERGENCY STOP diaktifkan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error emergency stop: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    })->name('emergency-stop');
    
    // Get maintenance schedule untuk robot
    Route::get('/{robotId}/maintenance', function ($robotId) {
        try {
            $robot = \App\Models\Robot::where('robot_id', $robotId)->firstOrFail();
            
            // Get upcoming maintenance
            $upcoming = \App\Models\RobotMaintenance::where('robot_id', $robotId)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->orderBy('scheduled_date', 'asc')
                ->get()
                ->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'type' => $m->maintenance_type,
                        'type_text' => $m->getMaintenanceTypeText(),
                        'title' => $m->title,
                        'scheduled_date' => $m->scheduled_date->format('Y-m-d'),
                        'scheduled_date_formatted' => $m->scheduled_date->format('d/m/Y'),
                        'days_until' => $m->getDaysUntilScheduled(),
                        'is_overdue' => $m->isOverdue(),
                        'status' => $m->status,
                        'status_badge_class' => $m->getStatusBadgeClass()
                    ];
                });
            
            // Get next service date
            $nextService = \App\Models\RobotMaintenance::where('robot_id', $robotId)
                ->where('status', 'scheduled')
                ->orderBy('scheduled_date', 'asc')
                ->first();
            
            // Get maintenance history (last 5)
            $history = \App\Models\RobotMaintenance::where('robot_id', $robotId)
                ->where('status', 'completed')
                ->orderBy('completed_date', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'type_text' => $m->getMaintenanceTypeText(),
                        'title' => $m->title,
                        'completed_date' => $m->completed_date->format('d/m/Y'),
                        'cost' => $m->cost,
                        'technician' => $m->technician_name
                    ];
                });
            
            return response()->json([
                'success' => true,
                'upcoming' => $upcoming,
                'next_service' => $nextService ? [
                    'date' => $nextService->scheduled_date->format('Y-m-d'),
                    'date_formatted' => $nextService->scheduled_date->format('d/m/Y'),
                    'days_until' => $nextService->getDaysUntilScheduled(),
                    'title' => $nextService->title,
                    'is_overdue' => $nextService->isOverdue()
                ] : null,
                'history' => $history
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching maintenance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    })->name('maintenance');
});

// Tools/Robots CRUD API
Route::prefix('tools')->name('tools.')->group(function () {
    // Get all tools (robots)
    Route::get('/', function () {
        try {
            $robots = \App\Models\Robot::all()->map(function ($robot) {
                return [
                    'id' => $robot->id,
                    'robot_id' => $robot->robot_id,
                    'name' => $robot->name,
                    'model' => $robot->model,
                    'location' => $robot->location,
                    'status' => $robot->operational_status === 'operating' || $robot->operational_status === 'idle' ? 'active' : 'inactive',
                    'operational_status' => $robot->operational_status,
                    'category' => 'Robot', // Default category
                    'description' => 'Robot monitoring otomatis',
                    'rating' => 4, // Default rating
                    'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23ffeaa7'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23fdcb6e' font-size='20'%3E🐔%3C/text%3E%3C/svg%3E"
                ];
            });
            
            return response()->json([
                'success' => true,
                'tools' => $robots
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tools: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data tools'
            ], 500);
        }
    })->name('index');
    
    // Create new tool (robot)
    Route::post('/', function (Request $request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive'
            ]);
            
            // Generate robot_id
            $robotId = 'CHICKPATROL-' . str_pad(\App\Models\Robot::count() + 1, 3, '0', STR_PAD_LEFT);
            
            $operationalStatus = ($validated['status'] ?? 'active') === 'active' ? 'idle' : 'offline';
            
            $robot = \App\Models\Robot::create([
                'robot_id' => $robotId,
                'name' => $validated['name'],
                'model' => $validated['model'],
                'location' => $validated['location'] ?? null,
                'operational_status' => $operationalStatus,
                'battery_level' => 100,
                'last_activity_at' => now(),
                'health_status' => [
                    'motors' => 'normal',
                    'sensors' => 'normal',
                    'battery' => 'good',
                    'navigation' => 'normal'
                ]
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil ditambahkan',
                'tool' => [
                    'id' => $robot->id,
                    'robot_id' => $robot->robot_id,
                    'name' => $robot->name,
                    'model' => $robot->model,
                    'location' => $robot->location,
                    'status' => $robot->operational_status === 'operating' || $robot->operational_status === 'idle' ? 'active' : 'inactive'
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating tool: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan alat: ' . $e->getMessage()
            ], 500);
        }
    })->name('store');
    
    // Update tool (robot)
    Route::put('/{id}', function (Request $request, $id) {
        try {
            $robot = \App\Models\Robot::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive'
            ]);
            
            $operationalStatus = ($validated['status'] ?? 'active') === 'active' ? 
                ($robot->operational_status === 'offline' ? 'idle' : $robot->operational_status) : 
                'offline';
            
            $robot->update([
                'name' => $validated['name'],
                'model' => $validated['model'],
                'location' => $validated['location'] ?? null,
                'operational_status' => $operationalStatus
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil diperbarui',
                'tool' => [
                    'id' => $robot->id,
                    'robot_id' => $robot->robot_id,
                    'name' => $robot->name,
                    'model' => $robot->model,
                    'location' => $robot->location,
                    'status' => $robot->operational_status === 'operating' || $robot->operational_status === 'idle' ? 'active' : 'inactive'
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating tool: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui alat: ' . $e->getMessage()
            ], 500);
        }
    })->name('update');
    
    // Delete tool (robot)
    Route::delete('/{id}', function ($id) {
        try {
            $robot = \App\Models\Robot::findOrFail($id);
            $robot->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting tool: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus alat: ' . $e->getMessage()
            ], 500);
        }
    })->name('delete');
});
// });
