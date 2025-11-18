<?php

use Illuminate\Support\Facades\Route;

// Public Routes - Semua bisa diakses tanpa login
Route::get('/', function () {
    return view('store.home');
})->name('home');

// Authentication Pages (hanya tampilan, tidak ada proses backend)
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function () {
    // TODO: Implement registration logic
    return redirect()->route('dashboard')->with('success', 'Registrasi berhasil!');
})->name('register.post');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    // TODO: Implement login logic
    return redirect()->route('dashboard')->with('success', 'Login berhasil!');
})->name('login.post');

// Dashboard (bisa langsung diakses tanpa login)
Route::get('/dashboard', function () {
    return view('dashboard.seller');
})->name('dashboard');

Route::get('/dashboard/products', function () {
    return view('dashboard.products');
})->name('dashboard.products');

Route::get('/dashboard/tools', function () {
    return view('dashboard.tools');
})->name('dashboard.tools');

Route::get('/dashboard/tools/monitoring', function () {
    return view('dashboard.tools-monitoring');
})->name('dashboard.tools.monitoring');

Route::get('/dashboard/sales', function () {
    return view('dashboard.sales');
})->name('dashboard.sales');

Route::get('/dashboard/chat', function () {
    return view('dashboard.chat');
})->name('dashboard.chat');

// Other Pages
Route::get('/articles', function () {
    return view('articles');
})->name('articles');

Route::get('/marketplace', function () {
    return view('marketplace');
})->name('marketplace');

Route::get('/profile', function () {
    return view('profile');
})->name('profile');

// Monitoring API mock (sensor + ML predictions + anomaly detection + status)
Route::get('/api/monitoring/tools', function () {
    $now = now();
    // Build 24h history with random but plausible poultry farm values
    $history = [];
    for ($i = 23; $i >= 0; $i--) {
        $timestamp = $now->copy()->subHours($i)->format('Y-m-d H:00');
        $temp = 24 + rand(-3, 3) + ($i > 12 ? 0.5 : 0); // Slight afternoon increase
        $humidity = 65 + rand(-5, 5);
        $ammonia = max(5, 10 + rand(-3, 4));
        $light = ($i >= 6 && $i <= 18) ? 700 + rand(-100, 100) : 120 + rand(-30, 30);
        $history[] = [
            'time' => $timestamp,
            'temperature' => round($temp, 1),
            'humidity' => round($humidity, 1),
            'ammonia' => round($ammonia, 1),
            'light' => $light
        ];
    }

    $latest = end($history);

    // Simple trend prediction (next 6 hours) using last 6 deltas linear extrapolation
    $temps = array_column($history, 'temperature');
    $humids = array_column($history, 'humidity');
    $ammonias = array_column($history, 'ammonia');
    $lights = array_column($history, 'light');
    $predict = function ($arr) {
        $n = count($arr);
        $recent = array_slice($arr, -6);
        $deltas = [];
        for ($i = 1; $i < count($recent); $i++) $deltas[] = $recent[$i] - $recent[$i-1];
        $avgDelta = count($deltas) ? array_sum($deltas)/count($deltas) : 0;
        $base = end($arr);
        $out = [];
        for ($h = 1; $h <= 6; $h++) $out[] = round($base + $avgDelta*$h, 2);
        return $out;
    };

    // Simple anomaly detection: flag points beyond thresholds
    $anomalies = [];
    foreach ($history as $point) {
        if ($point['temperature'] > 30 || $point['temperature'] < 20) {
            $anomalies[] = [
                'type' => 'temperature',
                'value' => $point['temperature'],
                'time' => $point['time'],
                'message' => 'Suhu di luar rentang optimal (20-30°C)'
            ];
        }
        if ($point['ammonia'] > 25) {
            $anomalies[] = [
                'type' => 'ammonia',
                'value' => $point['ammonia'],
                'time' => $point['time'],
                'message' => 'Kadar amoniak tinggi, cek ventilasi'
            ];
        }
    }

    // Extend prediction to 24h (reuse avg delta, but dampen after 12h)
    $predict24 = function ($arr) {
        $recent = array_slice($arr, -6);
        $deltas = [];
        for ($i=1;$i<count($recent);$i++) $deltas[] = $recent[$i]-$recent[$i-1];
        $avgDelta = count($deltas)? array_sum($deltas)/count($deltas):0;
        $base = end($arr);
        $out = [];
        for ($h=1;$h<=24;$h++) {
            // Dampening factor: reduce impact after 12h
            $factor = $h <= 12 ? 1 : 0.5;
            $out[] = round($base + $avgDelta*$h*$factor,2);
        }
        return $out;
    };

    // Environment status classification helper
    $statusLabel = function($latest) {
        $issues = 0;
        if ($latest['temperature'] < 20 || $latest['temperature'] > 30) $issues++;
        if ($latest['humidity'] < 55 || $latest['humidity'] > 75) $issues++;
        if ($latest['ammonia'] > 25) $issues++;
        if ($latest['light'] < 200 && (int)date('G') >= 8 && (int)date('G') <= 17) $issues++;
        if ($issues === 0) return ['label'=>'baik','severity'=>'normal','message'=>'Semua parameter dalam batas aman'];
        if ($issues === 1) return ['label'=>'perlu perhatian ringan','severity'=>'warning','message'=>'Ada 1 parameter perlu ditinjau'];
        if ($issues === 2) return ['label'=>'kurang stabil','severity'=>'warning','message'=>'Beberapa parameter di luar kisaran ideal'];
        return ['label'=>'tidak optimal','severity'=>'critical','message'=>'Banyak parameter bermasalah, lakukan pemeriksaan'];
    };
    $currentStatus = $statusLabel($latest);

    // Forecast qualitative (next 6h & 24h) based on predicted temperature & ammonia trends
    $pred6 = [
        'temperature' => $predict($temps),
        'humidity' => $predict($humids),
        'ammonia' => $predict($ammonias),
        'light' => $predict($lights)
    ];
    $pred24 = [
        'temperature' => $predict24($temps),
        'humidity' => $predict24($humids),
        'ammonia' => $predict24($ammonias),
        'light' => $predict24($lights)
    ];

    $qualitativeForecast = function($series, $metric, $unit, $safeLow, $safeHigh) {
        $min = min($series); $max = max($series); $trend = $series[array_key_last($series)] - $series[0];
        $dir = $trend > 0.5 ? 'meningkat' : ($trend < -0.5 ? 'menurun' : 'stabil');
        $risk = ($min < $safeLow || $max > $safeHigh) ? 'potensi keluar batas aman' : 'dalam kisaran aman';
        return [
            'metric'=>$metric,
            'summary'=>"$metric $dir ($min–$max $unit) $risk",
            'range'=>['min'=>$min,'max'=>$max,'unit'=>$unit],
            'trend'=>$dir,
            'risk'=>$risk
        ];
    };

    $forecast6Summary = [
        $qualitativeForecast($pred6['temperature'],'Suhu','°C',20,30),
        $qualitativeForecast($pred6['humidity'],'Kelembaban','%',55,75),
        $qualitativeForecast($pred6['ammonia'],'Amoniak','ppm',0,25),
        $qualitativeForecast($pred6['light'],'Cahaya','lux',200,900)
    ];
    $forecast24Summary = [
        $qualitativeForecast($pred24['temperature'],'Suhu','°C',20,30),
        $qualitativeForecast($pred24['humidity'],'Kelembaban','%',55,75),
        $qualitativeForecast($pred24['ammonia'],'Amoniak','ppm',0,25),
        $qualitativeForecast($pred24['light'],'Cahaya','lux',200,900)
    ];

    return response()->json([
        'meta' => [
            'generated_at' => $now->toDateTimeString(),
            'interval' => 'hourly',
            'history_hours' => count($history)
        ],
        'latest' => $latest,
        'history' => $history,
        'prediction_6h' => $pred6,
        'prediction_24h' => $pred24,
        'status' => $currentStatus,
        'forecast_summary_6h' => $forecast6Summary,
        'forecast_summary_24h' => $forecast24Summary,
        'anomalies' => $anomalies
    ]);
});
