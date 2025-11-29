<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorReading;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export data sensor ke PDF (Laporan)
     */
    public function exportPdf()
    {
        // Limit data untuk menghindari timeout (ambil 1000 data terakhir)
        // Gunakan chunk untuk menghindari memory issue
        $data = SensorReading::orderBy('recorded_at', 'desc')
            ->limit(1000)
            ->get()
            ->reverse()
            ->values(); // Reset keys untuk indexing yang benar
        
        // Hitung statistik
        $stats = [
            'total' => $data->count(),
            'amonia' => [
                'min' => $data->min('amonia_ppm'),
                'max' => $data->max('amonia_ppm'),
                'avg' => round($data->avg('amonia_ppm'), 2)
            ],
            'suhu' => [
                'min' => $data->min('suhu_c'),
                'max' => $data->max('suhu_c'),
                'avg' => round($data->avg('suhu_c'), 2)
            ],
            'kelembaban' => [
                'min' => $data->min('kelembaban_rh'),
                'max' => $data->max('kelembaban_rh'),
                'avg' => round($data->avg('kelembaban_rh'), 2)
            ],
            'cahaya' => [
                'min' => $data->min('cahaya_lux'),
                'max' => $data->max('cahaya_lux'),
                'avg' => round($data->avg('cahaya_lux'), 2)
            ]
        ];
        
        // Group data per hari untuk summary
        // Gunakan tanggal real-time saat ini untuk semua data
        $now = now()->setTimezone('Asia/Jakarta');
        
        // Group berdasarkan urutan hari (setiap 24 data = 1 hari, mulai dari hari ini)
        $dailyData = [];
        $groupedByDayIndex = $data->groupBy(function($item, $index) {
            // Setiap 24 data = 1 hari
            return (int)($index / 24);
        });
        
        // Format daily data dengan tanggal real-time (mundur dari hari ini)
        foreach ($groupedByDayIndex as $dayIndex => $dayData) {
            $displayDate = $now->copy()->subDays($dayIndex)->format('Y-m-d');
            $dailyData[] = [
                'date' => $displayDate,
                'count' => $dayData->count(),
                'amonia_avg' => round($dayData->avg('amonia_ppm'), 2),
                'suhu_avg' => round($dayData->avg('suhu_c'), 2),
                'kelembaban_avg' => round($dayData->avg('kelembaban_rh'), 2),
                'cahaya_avg' => round($dayData->avg('cahaya_lux'), 2)
            ];
        }
        
        // Sort by date descending (terbaru dulu)
        usort($dailyData, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        
        // Process data timestamps to WIB
        $data->transform(function($item) {
            if ($item->recorded_at) {
                $item->recorded_at_wib = \Carbon\Carbon::parse($item->recorded_at)
                    ->setTimezone('Asia/Jakarta')
                    ->format('d/m/Y H:i:s');
            }
            return $item;
        });
        
        // Prepare chart data (50 data terakhir untuk grafik)
        $chartData = $data->take(50)->values();
        $chartDataArray = [
            'amonia' => $chartData->pluck('amonia_ppm')->toArray(),
            'suhu' => $chartData->pluck('suhu_c')->toArray(),
            'kelembaban' => $chartData->pluck('kelembaban_rh')->toArray(),
            'cahaya' => $chartData->pluck('cahaya_lux')->toArray(),
        ];
        
        try {
        $pdf = Pdf::loadView('dashboard.export-pdf', [
            'data' => $data,
            'stats' => $stats,
            'dailyData' => $dailyData,
                'chartData' => $chartDataArray,
                'generatedAt' => $now->format('d/m/Y H:i:s') . ' WIB'
            ])->setPaper('a4', 'landscape')
              ->setOption('enable-local-file-access', true)
              ->setOption('isHtml5ParserEnabled', true)
              ->setOption('isRemoteEnabled', false);
        
        return $pdf->download('laporan-sensor-' . $now->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh PDF. Silakan coba lagi.');
        }
    }
    
    /**
     * Export data sensor ke CSV (Dataset - hanya nilai parameter, tanpa timestamp)
     */
    public function exportCsv()
    {
        $data = SensorReading::orderBy('recorded_at', 'asc')
            ->get(['amonia_ppm', 'suhu_c', 'kelembaban_rh', 'cahaya_lux']);
        
        $now = now()->setTimezone('Asia/Jakarta');
        $filename = 'dataset-sensor-' . $now->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Header CSV (hanya nama parameter)
            fputcsv($file, ['amonia_ppm', 'suhu_c', 'kelembaban_rh', 'cahaya_lux']);
            
            // Data (hanya nilai parameter, tanpa timestamp)
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->amonia_ppm,
                    $row->suhu_c,
                    $row->kelembaban_rh,
                    $row->cahaya_lux
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
