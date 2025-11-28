<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ToolMaintenance extends Model
{
    protected $table = 'tool_maintenances';
    
    protected $fillable = [
        'tool_id', 'maintenance_type', 'title', 'description',
        'scheduled_date', 'completed_date', 'status', 'cost',
        'technician_name', 'notes', 'parts_replaced', 'next_service_days'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
        'parts_replaced' => 'array',
        'next_service_days' => 'integer'
    ];

    public function tool()
    {
        return $this->belongsTo(Tools::class, 'tool_id', 'tool_id');
    }

    public function getMaintenanceTypeText()
    {
        return match($this->maintenance_type) {
            'routine' => 'Rutin',
            'repair' => 'Perbaikan',
            'cleaning' => 'Pembersihan',
            'parts_replacement' => 'Penggantian Parts',
            'inspection' => 'Inspeksi',
            default => 'Tidak Diketahui'
        };
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'scheduled' => 'badge-warning',
            'in_progress' => 'badge-info',
            'completed' => 'badge-success',
            'cancelled' => 'badge-secondary',
            default => 'badge-secondary'
        };
    }

    public function getDaysUntilScheduled()
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return null;
        }
        
        return Carbon::now()->diffInDays($this->scheduled_date, false);
    }

    public function isOverdue()
    {
        return $this->status === 'scheduled' && 
               $this->scheduled_date < Carbon::now();
    }
}

