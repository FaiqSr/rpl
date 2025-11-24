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
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Laporan Data Sensor</h1>
        <p>ChickPatrol - Monitoring Kandang Ayam</p>
        <p>Dibuat: {{ $generatedAt }} WIB</p>
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
                // Gunakan tanggal real-time saat ini, hitung mundur dari data terakhir
                // Data terakhir = waktu saat ini, data sebelumnya = mundur per jam
                $totalData = $data->count();
                $hoursAgo = $totalData - $index - 1;
                $displayTime = $now->copy()->subHours($hoursAgo);
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $displayTime->format('Y-m-d H:i') }}</td>
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

