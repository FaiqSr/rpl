<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolActivity extends Model
{
    protected $table = 'tool_activities';
    
    protected $fillable = [
        'tool_id', 'activity_type', 'description', 'position', 'metadata', 'occurred_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime'
    ];

    public function tool()
    {
        return $this->belongsTo(Tools::class, 'tool_id', 'tool_id');
    }
}

