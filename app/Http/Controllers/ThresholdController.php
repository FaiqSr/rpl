<?php

namespace App\Http\Controllers;

use App\Models\ThresholdProfile;
use App\Models\ThresholdValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ThresholdController extends Controller
{
    /**
     * Get all threshold profiles
     */
    public function getProfiles()
    {
        try {
            $profiles = ThresholdProfile::orderBy('age_min_days')
                ->orderBy('profile_key')
                ->get(['id', 'profile_key', 'profile_name', 'age_min_days', 'age_max_days', 'is_default']);
            
            return response()->json([
                'success' => true,
                'profiles' => $profiles
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting threshold profiles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar profile'
            ], 500);
        }
    }

    /**
     * Get threshold values for a specific profile
     */
    public function getProfile($profileKey)
    {
        try {
            $profile = ThresholdProfile::where('profile_key', $profileKey)
                ->with('thresholdValues')
                ->first();
            
            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile tidak ditemukan'
                ], 404);
            }

            // Format thresholds for frontend (map database columns to frontend format)
            $thresholds = [];
            foreach ($profile->thresholdValues as $value) {
                $thresholds[$value->sensor_type] = [
                    // Amoniak
                    'ideal_max' => $value->ideal_max,
                    'warn_max' => $value->warn_max,
                    'danger_max' => $value->danger_max,
                    // Suhu (map danger_min/max to danger_low/high)
                    'ideal_min' => $value->ideal_min,
                    'ideal_max' => $value->ideal_max,
                    'danger_low' => $value->danger_min, // Map to frontend format
                    'danger_high' => $value->danger_max, // Map to frontend format
                    // Kelembaban (map warn_max/danger_max to warn_high/danger_high)
                    'warn_high' => $value->warn_max, // Map to frontend format
                    'danger_high' => $value->danger_max, // Map to frontend format
                    // Cahaya (map ideal_min/max to ideal_low/high, warn_min/max to warn_low/high)
                    'ideal_low' => $value->ideal_min, // Map to frontend format
                    'ideal_high' => $value->ideal_max, // Map to frontend format
                    'warn_low' => $value->warn_min, // Map to frontend format
                    'warn_high' => $value->warn_max, // Map to frontend format
                ];
                
                // Clean up: only include relevant fields for each sensor type
                if ($value->sensor_type === 'amonia_ppm') {
                    $thresholds[$value->sensor_type] = [
                        'ideal_max' => $value->ideal_max,
                        'warn_max' => $value->warn_max,
                        'danger_max' => $value->danger_max,
                    ];
                } elseif ($value->sensor_type === 'suhu_c') {
                    $thresholds[$value->sensor_type] = [
                        'ideal_min' => $value->ideal_min,
                        'ideal_max' => $value->ideal_max,
                        'danger_low' => $value->danger_min,
                        'danger_high' => $value->danger_max,
                    ];
                } elseif ($value->sensor_type === 'kelembaban_rh') {
                    $thresholds[$value->sensor_type] = [
                        'ideal_min' => $value->ideal_min,
                        'ideal_max' => $value->ideal_max,
                        'warn_high' => $value->warn_max,
                        'danger_high' => $value->danger_max,
                    ];
                } elseif ($value->sensor_type === 'cahaya_lux') {
                    $thresholds[$value->sensor_type] = [
                        'ideal_low' => $value->ideal_min,
                        'ideal_high' => $value->ideal_max,
                        'warn_low' => $value->warn_min,
                        'warn_high' => $value->warn_max,
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'profile' => [
                    'profile_key' => $profile->profile_key,
                    'profile_name' => $profile->profile_name,
                    'is_default' => $profile->is_default,
                ],
                'thresholds' => $thresholds
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting threshold profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat threshold profile'
            ], 500);
        }
    }

    /**
     * Save/Update threshold values for a profile
     */
    public function saveProfile(Request $request, $profileKey)
    {
        try {
            $request->validate([
                'amonia_ppm' => 'nullable|array',
                'suhu_c' => 'nullable|array',
                'kelembaban_rh' => 'nullable|array',
                'cahaya_lux' => 'nullable|array',
            ]);

            $profile = ThresholdProfile::where('profile_key', $profileKey)->first();
            
            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile tidak ditemukan'
                ], 404);
            }

            // Prevent editing default profile
            if ($profile->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile default tidak dapat diubah. Buat profile baru atau edit profile umur tertentu.'
                ], 400);
            }

            DB::beginTransaction();

            $sensors = [
                'amonia_ppm' => $request->input('amonia_ppm'),
                'suhu_c' => $request->input('suhu_c'),
                'kelembaban_rh' => $request->input('kelembaban_rh'),
                'cahaya_lux' => $request->input('cahaya_lux'),
            ];

            foreach ($sensors as $sensorType => $values) {
                if ($values) {
                    // Map frontend format to database format
                    $dbValues = [];
                    
                    if ($sensorType === 'amonia_ppm') {
                        $dbValues = [
                            'ideal_max' => $values['ideal_max'] ?? null,
                            'warn_max' => $values['warn_max'] ?? null,
                            'danger_max' => $values['danger_max'] ?? null,
                        ];
                    } elseif ($sensorType === 'suhu_c') {
                        $dbValues = [
                            'ideal_min' => $values['ideal_min'] ?? null,
                            'ideal_max' => $values['ideal_max'] ?? null,
                            'danger_min' => $values['danger_low'] ?? null, // Map from frontend
                            'danger_max' => $values['danger_high'] ?? null, // Map from frontend
                        ];
                    } elseif ($sensorType === 'kelembaban_rh') {
                        $dbValues = [
                            'ideal_min' => $values['ideal_min'] ?? null,
                            'ideal_max' => $values['ideal_max'] ?? null,
                            'warn_max' => $values['warn_high'] ?? null, // Map from frontend
                            'danger_max' => $values['danger_high'] ?? null, // Map from frontend
                        ];
                    } elseif ($sensorType === 'cahaya_lux') {
                        $dbValues = [
                            'ideal_min' => $values['ideal_low'] ?? null, // Map from frontend
                            'ideal_max' => $values['ideal_high'] ?? null, // Map from frontend
                            'warn_min' => $values['warn_low'] ?? null, // Map from frontend
                            'warn_max' => $values['warn_high'] ?? null, // Map from frontend
                        ];
                    }
                    
                    ThresholdValue::updateOrCreate(
                        [
                            'profile_id' => $profile->id,
                            'sensor_type' => $sensorType,
                        ],
                        $dbValues
                    );
                }
            }

            DB::commit();
            
            // Clear model cache to ensure fresh data on next query
            ThresholdProfile::clearBootedModels();
            ThresholdValue::clearBootedModels();
            
            // Clear Laravel cache if any
            \Illuminate\Support\Facades\Cache::forget('threshold_profile_' . $profileKey);
            \Illuminate\Support\Facades\Cache::forget('threshold_values_' . $profileKey);

            return response()->json([
                'success' => true,
                'message' => 'Threshold berhasil disimpan'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving threshold profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan threshold: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset profile to default values
     */
    public function resetToDefault($profileKey)
    {
        try {
            $profile = ThresholdProfile::where('profile_key', $profileKey)->first();
            $defaultProfile = ThresholdProfile::where('profile_key', 'default')->first();
            
            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile tidak ditemukan'
                ], 404);
            }

            if (!$defaultProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile default tidak ditemukan'
                ], 404);
            }

            // Prevent resetting default profile
            if ($profile->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile default tidak dapat di-reset'
                ], 400);
            }

            DB::beginTransaction();

            // Delete existing threshold values for this profile
            ThresholdValue::where('profile_id', $profile->id)->delete();

            // Copy threshold values from default profile
            $defaultValues = ThresholdValue::where('profile_id', $defaultProfile->id)->get();
            
            foreach ($defaultValues as $defaultValue) {
                ThresholdValue::create([
                    'profile_id' => $profile->id,
                    'sensor_type' => $defaultValue->sensor_type,
                    'ideal_min' => $defaultValue->ideal_min,
                    'ideal_max' => $defaultValue->ideal_max,
                    'warn_min' => $defaultValue->warn_min,
                    'warn_max' => $defaultValue->warn_max,
                    'danger_min' => $defaultValue->danger_min,
                    'danger_max' => $defaultValue->danger_max,
                ]);
            }

            DB::commit();
            
            // Clear model cache to ensure fresh data on next query
            ThresholdProfile::clearBootedModels();
            ThresholdValue::clearBootedModels();
            
            // Clear Laravel cache if any
            \Illuminate\Support\Facades\Cache::forget('threshold_profile_' . $profileKey);
            \Illuminate\Support\Facades\Cache::forget('threshold_values_' . $profileKey);

            return response()->json([
                'success' => true,
                'message' => 'Threshold berhasil di-reset ke default'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error resetting threshold profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset threshold: ' . $e->getMessage()
            ], 500);
        }
    }
}
