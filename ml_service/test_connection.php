<?php
/**
 * Test koneksi dari Laravel ke ML Service
 * Jalankan: php test_connection.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mlServiceUrl = env('ML_SERVICE_URL', 'http://localhost:5000');

echo "========================================\n";
echo "Test Koneksi ML Service\n";
echo "========================================\n";
echo "ML Service URL: $mlServiceUrl\n\n";

// Test 1: Health Check
echo "[1/3] Testing Health Endpoint...\n";
try {
    $response = \Illuminate\Support\Facades\Http::timeout(5)->get("$mlServiceUrl/health");
    if ($response->successful()) {
        $data = $response->json();
        echo "✅ Health Check: OK\n";
        echo "   Status: " . ($data['status'] ?? 'unknown') . "\n";
        echo "   Models Loaded: " . ($data['models_loaded'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ Health Check: Failed (HTTP {$response->status()})\n";
    }
} catch (\Exception $e) {
    echo "❌ Health Check: Error - " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Root Endpoint
echo "[2/3] Testing Root Endpoint...\n";
try {
    $response = \Illuminate\Support\Facades\Http::timeout(5)->get("$mlServiceUrl/");
    if ($response->successful()) {
        $data = $response->json();
        echo "✅ Root Endpoint: OK\n";
        echo "   Service: " . ($data['service'] ?? 'unknown') . "\n";
        echo "   Status: " . ($data['status'] ?? 'unknown') . "\n";
    } else {
        echo "❌ Root Endpoint: Failed (HTTP {$response->status()})\n";
    }
} catch (\Exception $e) {
    echo "❌ Root Endpoint: Error - " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: ML Service Class
echo "[3/3] Testing ML Service Class...\n";
try {
    $mlService = new \App\Services\MachineLearningService();
    $connected = $mlService->testConnection();
    if ($connected) {
        echo "✅ ML Service Class: Connected\n";
    } else {
        echo "⚠️  ML Service Class: Not Connected (check ML_SERVICE_URL in .env)\n";
        echo "   Current URL from env: " . env('ML_SERVICE_URL', 'not set') . "\n";
    }
} catch (\Exception $e) {
    echo "❌ ML Service Class: Error - " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";
echo "========================================\n";
echo "Test Selesai!\n";
echo "========================================\n";

