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

    /**
     * Get active threshold profile berdasarkan threshold values yang paling baru di-update
     * Ini digunakan untuk Telegram notification agar selalu menggunakan threshold terbaru yang di-setting
     * 
     * Logika:
     * 1. Ambil profile yang threshold values-nya paling baru di-update (menunjukkan threshold yang baru saja di-setting)
     * 2. Jika tidak ada, gunakan profile default
     * 3. Jika tidak ada default, gunakan profile pertama yang ada
     * 
     * @return ThresholdProfile|null
     */
    public static function getActiveProfile(): ?self
    {
        // Ambil profile berdasarkan threshold values yang paling baru di-update
        // Ini menunjukkan threshold mana yang baru saja di-setting oleh user
        // Gunakan subquery untuk mendapatkan profile_id dengan threshold value terbaru
        $profileId = \App\Models\ThresholdValue::select('profile_id')
            ->orderBy('updated_at', 'desc')
            ->limit(1)
            ->value('profile_id');
        
        if ($profileId) {
            $profile = self::where('id', $profileId)
                ->with('thresholdValues')
                ->first();
            
            if ($profile) {
                return $profile;
            }
        }
        
        // Jika tidak ada profile dengan threshold values, coba ambil default profile
        $profile = self::where('is_default', true)
            ->with('thresholdValues')
            ->first();
        
        // Jika masih tidak ada, ambil profile pertama yang ada
        if (!$profile) {
            $profile = self::with('thresholdValues')
                ->first();
        }
        
        return $profile;
    }
}
