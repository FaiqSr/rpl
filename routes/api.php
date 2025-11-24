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
        $enabled = env('TELEGRAM_NOTIFICATIONS_ENABLED', 'true');
        // Handle string 'true'/'false' and boolean true/false
        $isEnabled = ($enabled === 'true' || $enabled === true || $enabled === '1' || $enabled === 1);
        
        return response()->json([
            'success' => true,
            'enabled' => $isEnabled,
            'bot_token' => env('TELEGRAM_BOT_TOKEN') ? 'configured' : 'not_configured',
            'chat_id' => env('TELEGRAM_CHAT_ID') ? 'configured' : 'not_configured',
            'raw_value' => $enabled // For debugging
        ]);
    })->name('notification-status');
});
// });
