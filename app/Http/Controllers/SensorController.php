<?php

namespace App\Http\Controllers;

use App\Services\MLService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorController extends Controller
{
    protected $mlService;

    public function __construct(MLService $mlService)
    {
        $this->mlService = $mlService;
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'amonia_ppm' => 'required|numeric',
            'suhu_c' => 'required|numeric',
            'kelembaban_rh' => 'required|numeric',
            'cahaya_lux' => 'required|numeric',
        ]);

        // Ambil history dari database (30 data terakhir)
        // Jika tabel sensor_readings tidak ada, gunakan data dummy
        try {
            $history = DB::table('sensor_readings')
                ->orderBy('created_at', 'desc')
                ->limit(30)
                ->get(['amonia_ppm', 'suhu_c', 'kelembaban_rh', 'cahaya_lux'])
                ->reverse()
                ->map(function($item) {
                    return [$item->amonia_ppm, $item->suhu_c, $item->kelembaban_rh, $item->cahaya_lux];
                })
                ->toArray();
        } catch (\Exception $e) {
            // Jika tabel tidak ada, gunakan array kosong
            $history = [];
        }

        $current = [
            $request->amonia_ppm,
            $request->suhu_c,
            $request->kelembaban_rh,
            $request->cahaya_lux
        ];

        $result = $this->mlService->analyzeAll($current, $history);

        return response()->json($result);
    }
}

