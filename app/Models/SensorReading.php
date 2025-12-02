<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    use HasFactory;

    protected $table = 'sensor_readings';

    protected $fillable = [
        'amonia_ppm',
        'suhu_c',
        'kelembaban_rh',
        'cahaya_lux',
        'recorded_at',
        'derivative_amonia',
        'derivative_suhu',
        'derivative_kelembaban',
        'derivative_cahaya',
        'gradient_amonia',
        'gradient_suhu',
        'gradient_kelembaban',
        'gradient_cahaya'
    ];

    protected $casts = [
        'amonia_ppm' => 'decimal:2',
        'suhu_c' => 'decimal:1',
        'kelembaban_rh' => 'decimal:1',
        'cahaya_lux' => 'decimal:1',
        'recorded_at' => 'datetime',
        'derivative_amonia' => 'decimal:2',
        'derivative_suhu' => 'decimal:2',
        'derivative_kelembaban' => 'decimal:2',
        'derivative_cahaya' => 'decimal:2',
        'gradient_amonia' => 'decimal:2',
        'gradient_suhu' => 'decimal:2',
        'gradient_kelembaban' => 'decimal:2',
        'gradient_cahaya' => 'decimal:2'
    ];
}
