# ✅ Fix: Threshold Cahaya Sesuai Aturan Boiler

## ❌ Masalah yang Ditemukan

1. **Threshold cahaya disesuaikan dengan dataset** (100-600):
   - Seharusnya tetap **10-60 lux** sesuai aturan boiler
   - Dataset menggunakan nilai ratusan, tapi threshold harus tetap 10-60

2. **Informasi cahaya tidak selalu menampilkan "di luar batas aman"**:
   - Seharusnya jika nilai cahaya (setelah dikonversi) di luar 10-60, selalu tampilkan "di luar batas aman"
   - Berlaku untuk tren 24 jam dan setiap prediksi

## ✅ Perbaikan yang Dilakukan

### 1. Kembalikan Threshold Cahaya ke 10-60 (Sesuai Aturan Boiler)

**File: `ml_service/app.py`**
```python
# Sebelum:
'cahaya_lux': {'ideal_low': 200, 'ideal_high': 400, 'warn_low': 100, 'warn_high': 600}  # ❌

# Sesudah:
'cahaya_lux': {'ideal_low': 20, 'ideal_high': 40, 'warn_low': 10, 'warn_high': 60}  # ✅
```

### 2. Konversi Nilai Cahaya dari Ratusan ke Puluhan untuk Pengecekan Threshold

**File: `ml_service/app.py` - Fungsi `detect_anomaly`**
```python
# Untuk cahaya, konversi dari ratusan ke puluhan untuk pengecekan threshold (sesuai aturan boiler)
cahaya_normalized = cahaya / 10.0
if cahaya_normalized < thresholds['cahaya_lux']['warn_low']:
    anomaly_sensors.append('light')
    anomaly_details.append({
        'sensor': 'light',
        'value': cahaya,
        'message': f'Cahaya kurang optimal (nilai: {cahaya_normalized:.1f} lux, di bawah 10 lux)'
    })
elif cahaya_normalized > thresholds['cahaya_lux']['warn_high']:
    anomaly_sensors.append('light')
    anomaly_details.append({
        'sensor': 'light',
        'value': cahaya,
        'message': f'Cahaya terlalu tinggi (nilai: {cahaya_normalized:.1f} lux, di atas 60 lux)'
    })
```

### 3. Perbaiki Forecast Summary untuk Cahaya

**File: `ml_service/app.py` - Endpoint `/predict`**
```python
def check_light_risk(light_values):
    """Cek apakah nilai cahaya (dalam ratusan) di luar batas aman (10-60 lux)"""
    # Konversi dari ratusan ke puluhan untuk pengecekan threshold
    light_values_normalized = [v / 10.0 for v in light_values] if light_values else []
    if not light_values_normalized:
        return 'tidak diketahui'
    min_val = min(light_values_normalized)
    max_val = max(light_values_normalized)
    # Jika ada nilai di luar 10-60, maka "di luar batas aman"
    if min_val < 10 or max_val > 60:
        return 'di luar batas aman'
    return 'dalam kisaran aman'

# Forecast 6h
light_6h_values = [p['light'] for p in pred_6h]
light_6h_risk = check_light_risk(light_6h_values)
light_6h_min = min(light_6h_values) / 10.0 if light_6h_values else 0
light_6h_max = max(light_6h_values) / 10.0 if light_6h_values else 0
# ... generate forecast summary dengan risk yang benar
```

### 4. Perbaiki Laravel untuk Forecast Summary Cahaya

**File: `routes/web.php`**
```php
// Threshold untuk cahaya: sesuai aturan boiler (10-60 lux)
$checkLightRisk = function($lightValues) {
    if (empty($lightValues) || !is_array($lightValues)) {
        return 'tidak diketahui';
    }
    // Konversi dari ratusan ke puluhan untuk pengecekan threshold
    $normalized = array_map(function($v) { return $v / 10.0; }, $lightValues);
    $min = min($normalized);
    $max = max($normalized);
    // Jika ada nilai di luar 10-60, maka "di luar batas aman"
    if ($min < 10 || $max > 60) {
        return 'di luar batas aman';
    }
    return 'dalam kisaran aman';
};

$generateLightForecast = function($lightValues, $metric, $unit) use ($checkLightRisk) {
    // ... generate forecast dengan risk yang benar
};
```

### 5. Perbaiki Fallback Deviasi untuk Cahaya

**File: `ml_service/app.py` - Fallback di `detect_anomaly`**
```python
# Sebelum:
'light': abs(cahaya - 300) / 300 if cahaya > 0 else 999  # Normal ~300 lux (data ratusan)  # ❌

# Sesudah:
# Untuk cahaya, normal ~30 lux (sesuai aturan boiler), tapi data aktual ratusan
# Jadi konversi dulu ke puluhan untuk perhitungan deviasi
cahaya_normalized = cahaya / 10.0
'light': abs(cahaya_normalized - 30) / 30 if cahaya > 0 else 999  # Normal ~30 lux (aturan boiler)  # ✅
```

## 📊 Logika Konversi

| Data Aktual (Ratusan) | Konversi (÷ 10) | Threshold | Status |
|----------------------|----------------|-----------|-------|
| 100 lux | 10 lux | 10-60 | ✅ Dalam batas aman |
| 300 lux | 30 lux | 10-60 | ✅ Dalam batas aman |
| 600 lux | 60 lux | 10-60 | ✅ Dalam batas aman |
| 700 lux | 70 lux | > 60 | ❌ Di luar batas aman |
| 50 lux | 5 lux | < 10 | ❌ Di luar batas aman |

## ✅ Hasil

1. ✅ **Threshold cahaya**: Tetap 10-60 lux sesuai aturan boiler
2. ✅ **Konversi nilai**: Data ratusan dikonversi ke puluhan untuk pengecekan threshold
3. ✅ **Forecast summary**: Selalu menampilkan "di luar batas aman" jika nilai di luar 10-60
4. ✅ **Tren 24 jam**: Menggunakan threshold 10-60 untuk pengecekan
5. ✅ **Prediksi 6h & 24h**: Menggunakan threshold 10-60 untuk pengecekan

## 🧪 Testing

Setelah perbaikan:
1. ✅ Cahaya 300 lux (30 lux setelah konversi) → "dalam kisaran aman" ✅
2. ✅ Cahaya 700 lux (70 lux setelah konversi) → "di luar batas aman" ⚠️
3. ✅ Cahaya 50 lux (5 lux setelah konversi) → "di luar batas aman" ⚠️
4. ✅ Forecast 6h & 24h menggunakan threshold 10-60
5. ✅ Anomali detection menggunakan threshold 10-60

