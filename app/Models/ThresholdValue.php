<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThresholdValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'sensor_type',
        'ideal_min',
        'ideal_max',
        'warn_min',
        'warn_max',
        'danger_min',
        'danger_max',
    ];

    protected $casts = [
        'ideal_min' => 'decimal:2',
        'ideal_max' => 'decimal:2',
        'warn_min' => 'decimal:2',
        'warn_max' => 'decimal:2',
        'danger_min' => 'decimal:2',
        'danger_max' => 'decimal:2',
    ];

    /**
     * Get the profile that owns this threshold value
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(ThresholdProfile::class, 'profile_id');
    }
}
