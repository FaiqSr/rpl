<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThresholdProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_key',
        'profile_name',
        'age_min_days',
        'age_max_days',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'age_min_days' => 'integer',
        'age_max_days' => 'integer',
    ];

    /**
     * Get threshold values for this profile
     */
    public function thresholdValues(): HasMany
    {
        return $this->hasMany(ThresholdValue::class, 'profile_id');
    }

    /**
     * Get threshold values as array keyed by sensor_type
     */
    public function getThresholdsArray(): array
    {
        $thresholds = [];
        foreach ($this->thresholdValues as $value) {
            $thresholds[$value->sensor_type] = [
                'ideal_min' => $value->ideal_min,
                'ideal_max' => $value->ideal_max,
                'warn_min' => $value->warn_min,
                'warn_max' => $value->warn_max,
                'danger_min' => $value->danger_min,
                'danger_max' => $value->danger_max,
            ];
        }
        return $thresholds;
    }
}
