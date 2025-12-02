<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

// Route::middleware(['auth:sanctum'])->group(function () {
Route::prefix('article')->name('article.')->group(function () {
    Route::get('/', [ArticleController::class, 'CartByUserJson']);
});

// Public API - Article Categories
Route::get('/article-categories', function () {
    $categories = \App\Models\ArticleCategory::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();
    
    return response()->json([
        'success' => true,
        'categories' => $categories
    ]);
});

Route::prefix('cart')->name('cart')->group(function () {
    Route::get('/', [CartController::class, 'CartByUserJson'])->name('cartGetAllJson');
});

// ML Service Routes
Route::prefix('sensor')->name('sensor.')->group(function () {
    Route::post('/analyze', [\App\Http\Controllers\SensorController::class, 'analyze'])->name('analyze');
});

// Threshold Management Routes
Route::prefix('threshold')->name('threshold.')->group(function () {
    Route::get('/profiles', [\App\Http\Controllers\ThresholdController::class, 'getProfiles'])->name('profiles');
    Route::get('/profile/{profileKey}', [\App\Http\Controllers\ThresholdController::class, 'getProfile'])->name('profile');
    Route::post('/profile/{profileKey}', [\App\Http\Controllers\ThresholdController::class, 'saveProfile'])->name('save');
    Route::post('/profile/{profileKey}/reset', [\App\Http\Controllers\ThresholdController::class, 'resetToDefault'])->name('reset');
});

// Telegram Settings Routes
Route::prefix('telegram')->name('telegram.')->group(function () {
    Route::post('/test', function (Request $request) {
        try {
            // Validate request
            try {
                $validated = $request->validate([
                    'bot_token' => 'required|string',
                    'chat_id' => 'required|string'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Token Bot dan Chat ID harus diisi',
                    'errors' => $e->errors()
                ], 400);
            }
            
            $botToken = trim($request->bot_token ?? '');
            $chatId = trim($request->chat_id ?? '');
            
            // Remove quotes if present
            $botToken = trim($botToken, '"\'');
            $chatId = trim($chatId, '"\'');
            
            // Validate token format (should be numeric:token) - more flexible pattern
            if (empty($botToken) || !preg_match('/^\d+:[A-Za-z0-9_-]+$/', $botToken)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Format token tidak valid. Token harus dalam format: 123456789:ABC-DEF1234ghIkl-zyx57W2v1u123ew11'
                ], 400);
            }
            
            if (empty($chatId)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Chat ID tidak boleh kosong'
                ], 400);
            }
            
            // Retry mechanism untuk mengatasi connection reset
            $maxRetries = 3;
            $retryDelay = 1; // seconds
            $lastError = null;
            
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    // Increase timeout untuk setiap retry
                    $timeout = 15 + ($attempt * 5); // 15s, 20s, 25s
                    
                    $response = \Illuminate\Support\Facades\Http::timeout($timeout)
                        ->retry(2, 1000) // Retry 2 kali dengan delay 1 detik
                        ->get("https://api.telegram.org/bot{$botToken}/getMe");
                    
                    // Handle HTTP errors first
                    if ($response->failed()) {
                        try {
                            $errorBody = $response->json();
                        } catch (\Exception $e) {
                            $errorBody = [];
                        }
                        $errorDescription = $errorBody['description'] ?? 'Gagal menghubungi Telegram API';
                        $errorCode = $errorBody['error_code'] ?? $response->status();
                        
                        // Provide more helpful error messages
                        if ($errorCode === 401) {
                            $errorDescription = 'Token tidak valid atau sudah expired. Silakan buat token baru dari @BotFather.';
                        } elseif ($errorCode === 400) {
                            $errorDescription = 'Format token tidak valid. Pastikan token dalam format: 123456789:ABC-DEF1234...';
                        }
                        
                        // Jika error 401 atau 400, tidak perlu retry
                        if ($errorCode == 401 || $errorCode == 400) {
                            Log::error('Telegram Test HTTP Error', [
                                'status' => $response->status(),
                                'error' => $errorDescription,
                                'error_code' => $errorCode,
                                'response' => $errorBody,
                                'token_prefix' => substr($botToken, 0, 10) . '...'
                            ]);
                            return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
                        }
                        
                        // Untuk error lain, lanjutkan retry
                        $lastError = $errorDescription;
                        if ($attempt < $maxRetries) {
                            sleep($retryDelay);
                            continue;
                        }
                        
                        Log::error('Telegram Test HTTP Error', [
                            'status' => $response->status(),
                            'error' => $errorDescription,
                            'error_code' => $errorCode,
                            'response' => $errorBody,
                            'token_prefix' => substr($botToken, 0, 10) . '...',
                            'attempt' => $attempt
                        ]);
                        return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
                    }
                    
                    // Try to parse JSON response
                    try {
                        $data = $response->json();
                    } catch (\Exception $e) {
                        Log::error('Telegram Test JSON Parse Error', ['error' => $e->getMessage(), 'response_body' => substr($response->body(), 0, 200)]);
                        if ($attempt < $maxRetries) {
                            sleep($retryDelay);
                            continue;
                        }
                        return response()->json(['success' => false, 'message' => 'Gagal memparse response dari Telegram API'], 500);
                    }
                    
                    if (isset($data['ok']) && $data['ok'] === true) {
                    // Also verify chat_id by trying to get chat info
                    try {
                        $chatResponse = \Illuminate\Support\Facades\Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getChat", [
                            'chat_id' => $chatId
                        ]);
                        
                        if ($chatResponse->failed()) {
                            $chatErrorBody = $chatResponse->json();
                            $chatError = $chatErrorBody['description'] ?? 'Chat ID tidak valid';
                            Log::warning('Telegram Chat ID Error', ['error' => $chatError, 'chat_id' => $chatId, 'response' => $chatErrorBody]);
                            return response()->json(['success' => false, 'message' => 'Bot token valid, tapi Chat ID error: ' . $chatError], 400);
                        }
                        
                        try {
                            $chatData = $chatResponse->json();
                        } catch (\Exception $e) {
                            Log::warning('Telegram Chat ID JSON Parse Error', ['error' => $e->getMessage()]);
                            return response()->json(['success' => true, 'message' => 'Bot token valid, Chat ID perlu diverifikasi']);
                        }
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
                        
                        // Jika error 401 atau 400, tidak perlu retry
                        if ($errorCode == 401 || $errorCode == 400) {
                            Log::error('Telegram Test Error', ['error' => $errorDescription, 'error_code' => $errorCode, 'response' => $data, 'token_prefix' => substr($botToken, 0, 10) . '...']);
                            return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
                        }
                        
                        $lastError = $errorDescription;
                        if ($attempt < $maxRetries) {
                            sleep($retryDelay);
                            continue;
                        }
                        
                        Log::error('Telegram Test Error', ['error' => $errorDescription, 'error_code' => $errorCode, 'response' => $data, 'token_prefix' => substr($botToken, 0, 10) . '...', 'attempt' => $attempt]);
                        return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
                        
                    } catch (\Illuminate\Http\Client\ConnectionException $e) {
                        $lastError = $e->getMessage();
                        Log::warning('Telegram Test Connection Error (Attempt ' . $attempt . '/' . $maxRetries . ')', [
                            'error' => $e->getMessage(),
                            'attempt' => $attempt
                        ]);
                        
                        if ($attempt < $maxRetries) {
                            sleep($retryDelay);
                            continue;
                        }
                        
                        // Last attempt failed
                        Log::error('Telegram Test Connection Error - All Retries Failed', ['error' => $e->getMessage()]);
                        return response()->json([
                            'success' => false, 
                            'message' => 'Tidak dapat terhubung ke Telegram API setelah ' . $maxRetries . ' kali percobaan. Periksa koneksi internet atau coba lagi nanti.',
                            'error_type' => 'connection_error'
                        ], 500);
                        
                    } catch (\Throwable $e) {
                        $lastError = $e->getMessage();
                        Log::error('Telegram Test Error (Attempt ' . $attempt . '/' . $maxRetries . ')', [
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'attempt' => $attempt
                        ]);
                        
                        if ($attempt < $maxRetries) {
                            sleep($retryDelay);
                            continue;
                        }
                        
                        // Last attempt failed
                        return response()->json([
                            'success' => false, 
                            'message' => 'Error: ' . $e->getMessage() . ' (Setelah ' . $maxRetries . ' kali percobaan)'
                        ], 500);
                    }
                }
                
                // Fallback jika semua retry gagal
                return response()->json([
                    'success' => false, 
                    'message' => 'Gagal menghubungi Telegram API setelah ' . $maxRetries . ' kali percobaan. ' . ($lastError ? 'Error: ' . $lastError : '')
                ], 500);
        } catch (\Throwable $e) {
            Log::error('Telegram Test Outer Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    })->name('test');
    
    Route::post('/send-test', function (Request $request) {
        try {
            // Validate request
            try {
                $request->validate([
                    'bot_token' => 'required|string',
                    'chat_id' => 'required|string',
                    'message' => 'required|string'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['success' => false, 'message' => 'Semua field harus diisi'], 400);
            }
            
            $botToken = trim($request->bot_token ?? '');
            $chatId = trim($request->chat_id ?? '');
            
            // Remove quotes if present
            $botToken = trim($botToken, '"\'');
            $chatId = trim($chatId, '"\'');
            
            // Validate inputs
            if (empty($botToken)) {
                return response()->json(['success' => false, 'message' => 'Token Bot tidak boleh kosong'], 400);
            }
            
            if (empty($chatId)) {
                return response()->json(['success' => false, 'message' => 'Chat ID tidak boleh kosong'], 400);
            }
            
            if (empty($request->message)) {
                return response()->json(['success' => false, 'message' => 'Pesan tidak boleh kosong'], 400);
            }
            
            // Retry mechanism untuk mengatasi connection reset
            $maxRetries = 3;
            $retryDelay = 1; // seconds
            $lastError = null;
            
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    // Increase timeout untuk setiap retry
                    $timeout = 15 + ($attempt * 5); // 15s, 20s, 25s
                    
                    $response = \Illuminate\Support\Facades\Http::timeout($timeout)
                        ->retry(2, 1000) // Retry 2 kali dengan delay 1 detik
                        ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                            'chat_id' => $chatId,
                            'text' => $request->message,
                            'parse_mode' => 'HTML'
                        ]);
                    
                    // Handle HTTP errors first
                    if ($response->failed()) {
                        try {
                            $errorBody = $response->json();
                        } catch (\Exception $e) {
                            $errorBody = ['description' => 'HTTP Error ' . $response->status()];
                        }
                        
                        $errorDescription = $errorBody['description'] ?? 'Gagal mengirim pesan';
                        $errorCode = $errorBody['error_code'] ?? $response->status();
                        
                        // Jika error 401 atau 400, tidak perlu retry
                        if ($errorCode == 401 || $errorCode == 400) {
                            Log::error('Telegram Send HTTP Error', [
                                'status' => $response->status(),
                                'error' => $errorDescription,
                                'error_code' => $errorCode,
                                'body' => $errorBody
                            ]);
                            return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
                        }
                        
                        // Untuk error lain, lanjutkan retry
                        $lastError = $errorDescription;
                        if ($attempt < $maxRetries) {
                            sleep($retryDelay);
                            continue;
                        }
                    }
                    
                    // Try to parse JSON response
                    try {
                        $data = $response->json();
                    } catch (\Exception $e) {
                        Log::error('Telegram Send JSON Parse Error', ['error' => $e->getMessage(), 'response_body' => substr($response->body(), 0, 200)]);
                        if ($attempt < $maxRetries) {
                            sleep($retryDelay);
                            continue;
                        }
                        return response()->json(['success' => false, 'message' => 'Gagal memparse response dari Telegram API'], 500);
                    }
                    
                    if (isset($data['ok']) && $data['ok'] === true) {
                        Log::info('Telegram Send Success', ['attempt' => $attempt]);
                        return response()->json(['success' => true, 'message' => 'Pesan berhasil dikirim']);
                    }
                    
                    // If response is successful but ok is false, get error description
                    $errorDescription = $data['description'] ?? 'Gagal mengirim pesan';
                    $errorCode = $data['error_code'] ?? 0;
                    
                    // Jika error 401 atau 400, tidak perlu retry
                    if ($errorCode == 401 || $errorCode == 400) {
                        Log::error('Telegram API Error', ['response' => $data, 'error_code' => $errorCode]);
                        return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
                    }
                    
                    $lastError = $errorDescription;
                    if ($attempt < $maxRetries) {
                        sleep($retryDelay);
                        continue;
                    }
                    
                    Log::error('Telegram API Error', ['response' => $data, 'error_code' => $errorCode]);
                    return response()->json(['success' => false, 'message' => $errorDescription, 'error_code' => $errorCode], 400);
                    
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $lastError = $e->getMessage();
                    Log::warning('Telegram Connection Error (Attempt ' . $attempt . '/' . $maxRetries . ')', [
                        'error' => $e->getMessage(),
                        'attempt' => $attempt
                    ]);
                    
                    if ($attempt < $maxRetries) {
                        sleep($retryDelay);
                        continue;
                    }
                    
                    // Last attempt failed
                    Log::error('Telegram Connection Error - All Retries Failed', ['error' => $e->getMessage()]);
                    return response()->json([
                        'success' => false, 
                        'message' => 'Tidak dapat terhubung ke Telegram API setelah ' . $maxRetries . ' kali percobaan. Periksa koneksi internet atau coba lagi nanti.',
                        'error_type' => 'connection_error'
                    ], 500);
                    
                } catch (\Throwable $e) {
                    $lastError = $e->getMessage();
                    Log::error('Telegram Send Error (Attempt ' . $attempt . '/' . $maxRetries . ')', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'attempt' => $attempt
                    ]);
                    
                    if ($attempt < $maxRetries) {
                        sleep($retryDelay);
                        continue;
                    }
                    
                    // Last attempt failed
                    return response()->json([
                        'success' => false, 
                        'message' => 'Error: ' . $e->getMessage() . ' (Setelah ' . $maxRetries . ' kali percobaan)'
                    ], 500);
                }
            }
            
            // Fallback jika semua retry gagal
            return response()->json([
                'success' => false, 
                'message' => 'Gagal mengirim pesan setelah ' . $maxRetries . ' kali percobaan. ' . ($lastError ? 'Error: ' . $lastError : '')
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Telegram Send Outer Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);
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

// Tools Status API Routes
Route::prefix('tools')->name('tools.')->group(function () {
    Route::get('/status', function () {
        try {
            $tools = \App\Models\Tools::all()->map(function ($tool) {
                return [
                    'tool_id' => $tool->tool_id,
                    'name' => $tool->name,
                    'model' => $tool->model,
                    'location' => $tool->location,
                    'operational_status' => $tool->operational_status,
                    'status_text' => $tool->getStatusText(),
                    'status_badge_class' => $tool->getStatusBadgeClass(),
                    'battery_level' => $tool->battery_level,
                    'last_activity_at' => $tool->last_activity_at?->toIso8601String(),
                    'last_activity_text' => $tool->getLastActivityText(),
                    'current_position' => $tool->current_position,
                    'total_distance_today' => $tool->total_distance_today,
                    'operating_hours_today' => $tool->operating_hours_today,
                    'operating_hours_formatted' => gmdate('H:i', $tool->operating_hours_today * 60),
                    'uptime_percentage' => $tool->uptime_percentage,
                    'health_status' => $tool->health_status,
                    'patrol_count_today' => $tool->patrol_count_today,
                    'health_summary' => $tool->health_status ? 
                        (collect($tool->health_status)->every(fn($v) => $v === 'normal' || $v === 'good') ? 
                            'Semua sistem normal' : 'Perlu perhatian') : 
                        'Tidak diketahui'
                ];
            });
            
            return response()->json([
                'success' => true,
                'tools' => $tools,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tool status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat status alat',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('status');
    
    // Start Patrol
    Route::post('/{toolId}/start-patrol', function ($toolId) {
        try {
            $tool = \App\Models\Tools::where('tool_id', $toolId)->firstOrFail();
            
            // Validasi: alat harus dalam status yang bisa di-start
            if (!in_array($tool->operational_status, ['idle', 'offline', 'charging'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alat tidak dapat memulai patrol saat ini'
                ], 400);
            }
            
            // Update status
            $tool->update([
                'operational_status' => 'operating',
                'last_activity_at' => now()
            ]);
            
            // Log activity
            \App\Models\ToolActivity::create([
                'tool_id' => $toolId,
                'activity_type' => 'patrol_start',
                'description' => 'Patrol dimulai secara manual',
                'occurred_at' => now()
            ]);
            
            // TODO: Kirim command ke alat hardware (via MQTT/HTTP/Serial)
            
            return response()->json([
                'success' => true,
                'message' => 'Patrol berhasil dimulai',
                'tool' => $tool->fresh()
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
    Route::post('/{toolId}/stop-patrol', function ($toolId) {
        try {
            $tool = \App\Models\Tools::where('tool_id', $toolId)->firstOrFail();
            
            if ($tool->operational_status !== 'operating') {
                return response()->json([
                    'success' => false,
                    'message' => 'Alat tidak sedang beroperasi'
                ], 400);
            }
            
            $tool->update([
                'operational_status' => 'idle',
                'last_activity_at' => now()
            ]);
            
            \App\Models\ToolActivity::create([
                'tool_id' => $toolId,
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
    Route::post('/{toolId}/return-to-base', function ($toolId) {
        try {
            $tool = \App\Models\Tools::where('tool_id', $toolId)->firstOrFail();
            
            $tool->update([
                'operational_status' => 'charging',
                'last_activity_at' => now()
            ]);
            
            \App\Models\ToolActivity::create([
                'tool_id' => $toolId,
                'activity_type' => 'position_update',
                'description' => 'Alat kembali ke base station',
                'position' => 'Base Station',
                'occurred_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Alat sedang kembali ke base station'
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
    Route::post('/{toolId}/emergency-stop', function ($toolId) {
        try {
            $tool = \App\Models\Tools::where('tool_id', $toolId)->firstOrFail();
            
            $tool->update([
                'operational_status' => 'offline',
                'last_activity_at' => now()
            ]);
            
            \App\Models\ToolActivity::create([
                'tool_id' => $toolId,
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
    
    // Get maintenance schedule untuk alat
    Route::get('/{toolId}/maintenance', function ($toolId) {
        try {
            $tool = \App\Models\Tools::where('tool_id', $toolId)->firstOrFail();
            
            // Get upcoming maintenance
            $upcoming = \App\Models\ToolMaintenance::where('tool_id', $toolId)
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
            $nextService = \App\Models\ToolMaintenance::where('tool_id', $toolId)
                ->where('status', 'scheduled')
                ->orderBy('scheduled_date', 'asc')
                ->first();
            
            // Get maintenance history (last 5)
            $history = \App\Models\ToolMaintenance::where('tool_id', $toolId)
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

// Tools CRUD API
Route::prefix('tools')->name('tools.')->group(function () {
    // Get all tools
    Route::get('/', function () {
        try {
            $tools = \App\Models\Tools::all()->map(function ($tool) {
                $defaultImage = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23ffeaa7'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23fdcb6e' font-size='20'%3E🐔%3C/text%3E%3C/svg%3E";
                return [
                    'id' => $tool->id,
                    'tool_id' => $tool->tool_id,
                    'name' => $tool->name,
                    'model' => $tool->model,
                    'location' => $tool->location,
                    'status' => $tool->operational_status === 'operating' || $tool->operational_status === 'idle' ? 'active' : 'inactive',
                    'operational_status' => $tool->operational_status,
                    'category' => 'Alat', // Default category
                    'description' => 'Alat monitoring otomatis',
                    'rating' => 4, // Default rating
                    'image' => $tool->image_url ?: $defaultImage,
                    'image_url' => $tool->image_url
                ];
            });
            
            return response()->json([
                'success' => true,
                'tools' => $tools
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tools: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data tools'
            ], 500);
        }
    })->name('index');
    
    // Create new tool (only POST without ID)
    Route::post('/', function (Request $request) {
        // Don't process if it's an update request (has ID in URL)
        if ($request->route()->parameter('id')) {
            return response()->json([
                'success' => false,
                'message' => 'Use PUT or POST /api/tools/{id} for updates'
            ], 400);
        }
        try {
            // Log request data for debugging
            Log::info('Tool create request:', [
                'has_file' => $request->hasFile('image_file'),
                'name' => $request->input('name'),
                'model' => $request->input('model'),
                'all_inputs' => $request->all(),
                'content_type' => $request->header('Content-Type')
            ]);
            
            // Validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive',
            ];
            
            // Add image validation only if file is uploaded
            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
            } else {
                $rules['image_url'] = 'nullable|string';
            }
            
            $validated = $request->validate($rules);
            
            // Handle image upload
            $imageUrl = null;
            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
                
                // Store file using Storage facade
                $path = $file->storeAs('tools', $filename, 'public');
                $imageUrl = asset('storage/' . $path);
            } elseif (!empty($validated['image_url'])) {
                $imageUrl = $validated['image_url'];
                
                // Check if it's a base64 image
                if (preg_match('/^data:image\/(\w+);base64,/', $imageUrl, $matches)) {
                    $imageData = substr($imageUrl, strpos($imageUrl, ',') + 1);
                    $imageData = base64_decode($imageData);
                    $extension = $matches[1] ?? 'jpg';
                    $filename = \Illuminate\Support\Str::uuid() . '.' . $extension;
                    
                    // Store using Storage facade
                    \Illuminate\Support\Facades\Storage::disk('public')->put('tools/' . $filename, $imageData);
                    $imageUrl = asset('storage/tools/' . $filename);
                }
            }
            
            // Generate tool_id
            $toolId = 'CHICKPATROL-' . str_pad(\App\Models\Tools::count() + 1, 3, '0', STR_PAD_LEFT);
            
            $operationalStatus = ($validated['status'] ?? 'active') === 'active' ? 'idle' : 'offline';
            
            $tool = \App\Models\Tools::create([
                'tool_id' => $toolId,
                'name' => $validated['name'],
                'model' => $validated['model'],
                'location' => $validated['location'] ?? null,
                'image_url' => $imageUrl,
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
            
            // Refresh tool to get latest data
            $tool->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil ditambahkan',
                'tool' => [
                    'id' => $tool->id,
                    'tool_id' => $tool->tool_id,
                    'name' => $tool->name,
                    'model' => $tool->model,
                    'location' => $tool->location,
                    'image' => $tool->image_url,
                    'image_url' => $tool->image_url,
                    'status' => $tool->operational_status === 'operating' || $tool->operational_status === 'idle' ? 'active' : 'inactive'
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages[$field] = is_array($messages) ? $messages[0] : $messages;
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_values($errorMessages)),
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating tool: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan alat: ' . $e->getMessage()
            ], 500);
        }
    })->name('store');
    
    // Update tool (supports both PUT and POST for file uploads)
    Route::match(['put', 'post'], '/{id}', function (Request $request, $id) {
        try {
            $tool = \App\Models\Tools::findOrFail($id);
            
            // Log request for debugging
            Log::info('Tool update request:', [
                'id' => $id,
                'method' => $request->method(),
                'has_file' => $request->hasFile('image_file'),
                'name' => $request->input('name'),
                'model' => $request->input('model'),
                'all_inputs' => $request->except(['image_file'])
            ]);
            
            // Validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive',
            ];
            
            // Add image validation only if file is uploaded
            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
            } else {
                $rules['image_url'] = 'nullable|string';
            }
            
            $validated = $request->validate($rules);
            
            // Handle image upload
            $imageUrl = $tool->image_url; // Keep existing image by default
            if ($request->hasFile('image_file')) {
                // Delete old image if exists
                if ($tool->image_url && strpos($tool->image_url, 'storage/tools/') !== false) {
                    $oldFilename = basename($tool->image_url);
                    $oldPath = 'tools/' . $oldFilename;
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                
                $file = $request->file('image_file');
                $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
                
                // Store file using Storage facade
                $path = $file->storeAs('tools', $filename, 'public');
                $imageUrl = asset('storage/' . $path);
            } elseif (isset($validated['image_url']) && $validated['image_url'] !== $tool->image_url) {
                $imageUrl = $validated['image_url'];
                
                // Check if it's a base64 image
                if (preg_match('/^data:image\/(\w+);base64,/', $imageUrl, $matches)) {
                    // Delete old image if exists
                    if ($tool->image_url && strpos($tool->image_url, 'storage/tools/') !== false) {
                        $oldFilename = basename($tool->image_url);
                        $oldPath = 'tools/' . $oldFilename;
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                    
                    $imageData = substr($imageUrl, strpos($imageUrl, ',') + 1);
                    $imageData = base64_decode($imageData);
                    $extension = $matches[1] ?? 'jpg';
                    $filename = \Illuminate\Support\Str::uuid() . '.' . $extension;
                    
                    // Store using Storage facade
                    Storage::disk('public')->put('tools/' . $filename, $imageData);
                    $imageUrl = asset('storage/tools/' . $filename);
                }
            }
            
            $operationalStatus = ($validated['status'] ?? 'active') === 'active' ? 
                ($tool->operational_status === 'offline' ? 'idle' : $tool->operational_status) : 
                'offline';
            
            $tool->update([
                'name' => $validated['name'],
                'model' => $validated['model'],
                'location' => $validated['location'] ?? null,
                'image_url' => $imageUrl,
                'operational_status' => $operationalStatus
            ]);
            
            // Refresh tool to get latest data
            $tool->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil diperbarui',
                'tool' => [
                    'id' => $tool->id,
                    'tool_id' => $tool->tool_id,
                    'name' => $tool->name,
                    'model' => $tool->model,
                    'location' => $tool->location,
                    'image' => $tool->image_url,
                    'image_url' => $tool->image_url,
                    'status' => $tool->operational_status === 'operating' || $tool->operational_status === 'idle' ? 'active' : 'inactive'
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages[$field] = is_array($messages) ? $messages[0] : $messages;
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_values($errorMessages)),
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating tool: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui alat: ' . $e->getMessage()
            ], 500);
        }
    })->name('update');
    
    // Delete tool
    Route::delete('/{id}', function ($id) {
        try {
            $tool = \App\Models\Tools::findOrFail($id);
            $tool->delete();
            
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
