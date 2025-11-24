<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RobotActivity extends Model
{
    protected $fillable = [
        'robot_id', 'activity_type', 'description', 'position', 'metadata', 'occurred_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime'
    ];

    public function robot()
    {
        return $this->belongsTo(Robot::class, 'robot_id', 'robot_id');
    }
}
