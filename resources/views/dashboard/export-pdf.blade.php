<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Sensor - ChickPatrol</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2F2F2F;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats {
            margin: 20px 0;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .stats h3 {
            margin-top: 0;
            color: #4CAF50;
            font-size: 16px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        .stat-item {
            background: white;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }
        .stat-item h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #666;
        }
        .stat-item .values {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        .stat-item .values span {
            font-weight: 600;
            color: #2F2F2F;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            background: #4CAF50;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e9ecef;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #e9ecef;
            padding-top: 10px;
        }
        .page-break {
            page-break-before: always;
        }
        .chart-container {
            margin: 20px 0;
            page-break-inside: avoid;
        }
        .chart-title {
            font-size: 14px;
            font-weight: 600;
            color: #2F2F2F;
            margin-bottom: 10px;
        }
        .chart-svg {
            width: 100%;
            height: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            background: #fafafa;
        }
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Laporan Data Sensor</h1>
        <p>ChickPatrol - Monitoring Kandang Ayam</p>
        <p>Dibuat: {{ $generatedAt }}</p>
    </div>

    <div class="stats">
        <h3>📈 Statistik Keseluruhan</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <h4>Amonia (ppm)</h4>
                <div class="values">
                    <span>Min: {{ $stats['amonia']['min'] }}</span>
                    <span>Max: {{ $stats['amonia']['max'] }}</span>
                    <span>Rata-rata: {{ $stats['amonia']['avg'] }}</span>
                </div>
            </div>
            <div class="stat-item">
                <h4>Suhu (°C)</h4>
                <div class="values">
                    <span>Min: {{ $stats['suhu']['min'] }}</span>
                    <span>Max: {{ $stats['suhu']['max'] }}</span>
                    <span>Rata-rata: {{ $stats['suhu']['avg'] }}</span>
                </div>
            </div>
            <div class="stat-item">
                <h4>Kelembaban (%)</h4>
                <div class="values">
                    <span>Min: {{ $stats['kelembaban']['min'] }}</span>
                    <span>Max: {{ $stats['kelembaban']['max'] }}</span>
                    <span>Rata-rata: {{ $stats['kelembaban']['avg'] }}</span>
                </div>
            </div>
            <div class="stat-item">
                <h4>Cahaya (lux)</h4>
                <div class="values">
                    <span>Min: {{ $stats['cahaya']['min'] }}</span>
                    <span>Max: {{ $stats['cahaya']['max'] }}</span>
                    <span>Rata-rata: {{ $stats['cahaya']['avg'] }}</span>
                </div>
            </div>
        </div>
        <p style="margin-top: 15px; font-weight: 600;">Total Data: {{ $stats['total'] }} record</p>
    </div>

    <div class="page-break"></div>

    <h3 style="margin-top: 0;">📈 Grafik Trend Parameter Sensor</h3>
    <div class="chart-grid">
        @php
            // Use chart data from controller or prepare from data
            $chartDataLocal = isset($chartData) ? $chartData : [
                'amonia' => $data->take(50)->pluck('amonia_ppm')->toArray(),
                'suhu' => $data->take(50)->pluck('suhu_c')->toArray(),
                'kelembaban' => $data->take(50)->pluck('kelembaban_rh')->toArray(),
                'cahaya' => $data->take(50)->pluck('cahaya_lux')->toArray(),
            ];
            
            $amoniaValues = $chartDataLocal['amonia'] ?? [];
            $suhuValues = $chartDataLocal['suhu'] ?? [];
            $kelembabanValues = $chartDataLocal['kelembaban'] ?? [];
            $cahayaValues = $chartDataLocal['cahaya'] ?? [];
            
            $chartWidth = 700;
            $chartHeight = 200;
            $padding = 40;
            $plotWidth = $chartWidth - ($padding * 2);
            $plotHeight = $chartHeight - ($padding * 2);
            
            // Calculate min/max for scaling
            $amoniaMin = !empty($amoniaValues) ? min($amoniaValues) : 0;
            $amoniaMax = !empty($amoniaValues) ? max($amoniaValues) : 1;
            $suhuMin = !empty($suhuValues) ? min($suhuValues) : 0;
            $suhuMax = !empty($suhuValues) ? max($suhuValues) : 1;
            $kelembabanMin = !empty($kelembabanValues) ? min($kelembabanValues) : 0;
            $kelembabanMax = !empty($kelembabanValues) ? max($kelembabanValues) : 1;
            $cahayaMin = !empty($cahayaValues) ? min($cahayaValues) : 0;
            $cahayaMax = !empty($cahayaValues) ? max($cahayaValues) : 1;
            
            // Helper function to generate SVG path
            $generatePath = function($values, $min, $max, $plotWidth, $plotHeight) use ($padding) {
                if (empty($values)) return '';
                $path = '';
                $count = count($values);
                $range = ($max - $min) > 0 ? ($max - $min) : 1;
                for ($i = 0; $i < $count; $i++) {
                    $x = $padding + ($count > 1 ? ($i / ($count - 1)) : 0) * $plotWidth;
                    $y = $padding + $plotHeight - (($values[$i] - $min) / $range) * $plotHeight;
                    $path .= ($i == 0 ? "M $x $y " : "L $x $y ");
                }
                return $path;
            };
        @endphp
        
        <!-- Grafik Amonia -->
        <div class="chart-container">
            <div class="chart-title">Amonia (ppm)</div>
            <svg class="chart-svg" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" xmlns="http://www.w3.org/2000/svg">
                <!-- Grid lines -->
                @for($i = 0; $i <= 4; $i++)
                    <line x1="{{ $padding }}" y1="{{ $padding + ($i * $plotHeight / 4) }}" 
                          x2="{{ $padding + $plotWidth }}" y2="{{ $padding + ($i * $plotHeight / 4) }}" 
                          stroke="#e5e7eb" stroke-width="0.5"/>
                    <text x="{{ $padding - 5 }}" y="{{ $padding + ($i * $plotHeight / 4) + 3 }}" 
                          font-size="9" fill="#666" text-anchor="end">
                        {{ number_format($amoniaMax - ($i * ($amoniaMax - $amoniaMin) / 4), 1) }}
                    </text>
                @endfor
                <!-- Chart line -->
                <path d="{{ $generatePath($amoniaValues, $amoniaMin, $amoniaMax, $plotWidth, $plotHeight) }}" 
                      fill="none" stroke="#EF4444" stroke-width="2"/>
                <!-- Fill area -->
                <path d="{{ $generatePath($amoniaValues, $amoniaMin, $amoniaMax, $plotWidth, $plotHeight) }} L {{ $padding + $plotWidth }} {{ $padding + $plotHeight }} L {{ $padding }} {{ $padding + $plotHeight }} Z" 
                      fill="#EF4444" opacity="0.2"/>
            </svg>
        </div>
        
        <!-- Grafik Suhu -->
        <div class="chart-container">
            <div class="chart-title">Suhu (°C)</div>
            <svg class="chart-svg" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" xmlns="http://www.w3.org/2000/svg">
                @for($i = 0; $i <= 4; $i++)
                    <line x1="{{ $padding }}" y1="{{ $padding + ($i * $plotHeight / 4) }}" 
                          x2="{{ $padding + $plotWidth }}" y2="{{ $padding + ($i * $plotHeight / 4) }}" 
                          stroke="#e5e7eb" stroke-width="0.5"/>
                    <text x="{{ $padding - 5 }}" y="{{ $padding + ($i * $plotHeight / 4) + 3 }}" 
                          font-size="9" fill="#666" text-anchor="end">
                        {{ number_format($suhuMax - ($i * ($suhuMax - $suhuMin) / 4), 1) }}
                    </text>
                @endfor
                <path d="{{ $generatePath($suhuValues, $suhuMin, $suhuMax, $plotWidth, $plotHeight) }}" 
                      fill="none" stroke="#3B82F6" stroke-width="2"/>
                <path d="{{ $generatePath($suhuValues, $suhuMin, $suhuMax, $plotWidth, $plotHeight) }} L {{ $padding + $plotWidth }} {{ $padding + $plotHeight }} L {{ $padding }} {{ $padding + $plotHeight }} Z" 
                      fill="#3B82F6" opacity="0.2"/>
            </svg>
        </div>
        
        <!-- Grafik Kelembaban -->
        <div class="chart-container">
            <div class="chart-title">Kelembaban (%)</div>
            <svg class="chart-svg" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" xmlns="http://www.w3.org/2000/svg">
                @for($i = 0; $i <= 4; $i++)
                    <line x1="{{ $padding }}" y1="{{ $padding + ($i * $plotHeight / 4) }}" 
                          x2="{{ $padding + $plotWidth }}" y2="{{ $padding + ($i * $plotHeight / 4) }}" 
                          stroke="#e5e7eb" stroke-width="0.5"/>
                    <text x="{{ $padding - 5 }}" y="{{ $padding + ($i * $plotHeight / 4) + 3 }}" 
                          font-size="9" fill="#666" text-anchor="end">
                        {{ number_format($kelembabanMax - ($i * ($kelembabanMax - $kelembabanMin) / 4), 1) }}
                    </text>
                @endfor
                <path d="{{ $generatePath($kelembabanValues, $kelembabanMin, $kelembabanMax, $plotWidth, $plotHeight) }}" 
                      fill="none" stroke="#10B981" stroke-width="2"/>
                <path d="{{ $generatePath($kelembabanValues, $kelembabanMin, $kelembabanMax, $plotWidth, $plotHeight) }} L {{ $padding + $plotWidth }} {{ $padding + $plotHeight }} L {{ $padding }} {{ $padding + $plotHeight }} Z" 
                      fill="#10B981" opacity="0.2"/>
            </svg>
        </div>
        
        <!-- Grafik Cahaya -->
        <div class="chart-container">
            <div class="chart-title">Cahaya (lux)</div>
            <svg class="chart-svg" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" xmlns="http://www.w3.org/2000/svg">
                @for($i = 0; $i <= 4; $i++)
                    <line x1="{{ $padding }}" y1="{{ $padding + ($i * $plotHeight / 4) }}" 
                          x2="{{ $padding + $plotWidth }}" y2="{{ $padding + ($i * $plotHeight / 4) }}" 
                          stroke="#e5e7eb" stroke-width="0.5"/>
                    <text x="{{ $padding - 5 }}" y="{{ $padding + ($i * $plotHeight / 4) + 3 }}" 
                          font-size="9" fill="#666" text-anchor="end">
                        {{ number_format($cahayaMax - ($i * ($cahayaMax - $cahayaMin) / 4), 1) }}
                    </text>
                @endfor
                <path d="{{ $generatePath($cahayaValues, $cahayaMin, $cahayaMax, $plotWidth, $plotHeight) }}" 
                      fill="none" stroke="#F59E0B" stroke-width="2"/>
                <path d="{{ $generatePath($cahayaValues, $cahayaMin, $cahayaMax, $plotWidth, $plotHeight) }} L {{ $padding + $plotWidth }} {{ $padding + $plotHeight }} L {{ $padding }} {{ $padding + $plotHeight }} Z" 
                      fill="#F59E0B" opacity="0.2"/>
            </svg>
        </div>
    </div>

    <div class="page-break"></div>

    <h3 style="margin-top: 0;">📋 Ringkasan Harian</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jumlah Data</th>
                <th>Amonia (avg)</th>
                <th>Suhu (avg)</th>
                <th>Kelembaban (avg)</th>
                <th>Cahaya (avg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyData as $day)
            <tr>
                <td>{{ $day['date'] }}</td>
                <td>{{ $day['count'] }}</td>
                <td>{{ $day['amonia_avg'] }}</td>
                <td>{{ $day['suhu_avg'] }}</td>
                <td>{{ $day['kelembaban_avg'] }}</td>
                <td>{{ $day['cahaya_avg'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <h3 style="margin-top: 0;">📊 Detail Data Sensor (Sample - 100 data pertama)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Amonia (ppm)</th>
                <th>Suhu (°C)</th>
                <th>Kelembaban (%)</th>
                <th>Cahaya (lux)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $now = \Carbon\Carbon::now()->setTimezone('Asia/Jakarta');
            @endphp
            @foreach($data->take(100) as $index => $row)
            @php
                // Gunakan recorded_at yang sudah di-convert ke WIB
                $displayTime = $row->recorded_at_wib ?? \Carbon\Carbon::parse($row->recorded_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s');
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $displayTime }} WIB</td>
                <td>{{ number_format($row->amonia_ppm, 2) }}</td>
                <td>{{ number_format($row->suhu_c, 1) }}</td>
                <td>{{ number_format($row->kelembaban_rh, 1) }}</td>
                <td>{{ number_format($row->cahaya_lux, 1) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dihasilkan secara otomatis oleh sistem ChickPatrol</p>
        <p>Untuk informasi lebih lanjut, hubungi administrator sistem</p>
    </div>
</body>
</html>

