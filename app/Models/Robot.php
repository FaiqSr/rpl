<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Robot extends Model
{
    protected $fillable = [
        'robot_id', 'name', 'model', 'location',
        'operational_status', 'battery_level', 'last_activity_at',
        'current_position', 'total_distance_today', 'operating_hours_today',
        'uptime_percentage', 'health_status', 'patrol_count_today'
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'health_status' => 'array',
        'battery_level' => 'integer',
        'total_distance_today' => 'decimal:2',
        'operating_hours_today' => 'integer',
        'uptime_percentage' => 'decimal:2',
        'patrol_count_today' => 'integer'
    ];

    public function activities()
    {
        return $this->hasMany(RobotActivity::class, 'robot_id', 'robot_id');
    }

    public function maintenances()
    {
        return $this->hasMany(RobotMaintenance::class, 'robot_id', 'robot_id');
    }

    public function getStatusBadgeClass()
    {
        return match($this->operational_status) {
            'operating' => 'status-operating',
            'idle' => 'status-idle',
            'maintenance' => 'status-maintenance',
            'offline' => 'status-offline',
            'charging' => 'status-charging',
            default => 'status-offline'
        };
    }

    public function getStatusText()
    {
        return match($this->operational_status) {
            'operating' => 'Beroperasi',
            'idle' => 'Idle',
            'maintenance' => 'Maintenance',
            'offline' => 'Offline',
            'charging' => 'Mengisi Daya',
            default => 'Tidak Diketahui'
        };
    }

    public function getLastActivityText()
    {
        if (!$this->last_activity_at) {
            return 'Belum ada aktivitas';
        }
        
        $diff = $this->last_activity_at->diffForHumans();
        return "Terakhir aktif: {$diff}";
    }
}
