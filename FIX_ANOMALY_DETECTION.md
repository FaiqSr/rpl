# ✅ Fix: Deteksi Anomali - Sensor Spesifik & Threshold

## ❌ Masalah yang Ditemukan

1. **Deteksi anomali menampilkan "unknown"** - seharusnya menunjukkan sensor spesifik (suhu, kelembaban, amoniak, atau cahaya)
2. **Terlalu banyak anomali terdeteksi** - perlu dicek threshold/logika
3. **Sensor cahaya menampilkan 700 lux** - di dataset saat training ML tidak ada yang sampai 700 (ideal: 20-40 lux, warn_high: 60 lux)

## ✅ Perbaikan yang Dilakukan

### 1. Update Fungsi `detect_anomaly` di Python API

**Fitur Baru:**
- ✅ **Identifikasi sensor spesifik** yang anomali
- ✅ **Threshold berdasarkan model_metadata.json**:
  - Amoniak: ideal_max: 20, warn_max: 25, danger_max: 35
  - Suhu: ideal_min: 23, ideal_max: 34, danger_low: 20, danger_high: 35
  - Kelembaban: ideal_min: 50, ideal_max: 70, warn_high: 75, danger_high: 80
  - Cahaya: ideal_low: 20, ideal_high: 40, warn_low: 10, warn_high: 60
- ✅ **Fallback logic** jika Isolation Forest mendeteksi anomali tapi tidak ada sensor spesifik yang terdeteksi
- ✅ **Return detail lengkap**: `anomaly_sensors`, `anomaly_details`, `sensor_values`

### 2. Update Endpoint `/api/detect-anomaly`

- ✅ Menggunakan fungsi `detect_anomaly` yang sudah diperbaiki
- ✅ Return detail sensor spesifik yang anomali

### 3. Update Endpoint `/predict`

- ✅ Menggunakan `anomaly_details` dari fungsi `detect_anomaly`
- ✅ Menampilkan sensor spesifik untuk setiap anomali
- ✅ Tidak lagi menampilkan "unknown"

### 4. Perbaiki Nilai Default Cahaya di Laravel

**Sebelum:**
```php
$baseLight = ($hour >= 6 && $hour <= 18) ? 700 : 120;  // ❌ Terlalu tinggi!
```

**Sesudah:**
```php
// Cahaya: siang hari 30-40 lux, malam hari 20-25 lux (sesuai dataset)
$baseLight = ($hour >= 6 && $hour <= 18) ? 35 : 22;  // ✅ Sesuai dataset
```

## 📝 Format Response Baru

### Response dari `detect_anomaly`:
```python
{
    'is_anomaly': bool,
    'anomaly_score': float,
    'status': 'ANOMALI' | 'NORMAL',
    'anomaly_sensors': ['ammonia', 'temperature', ...],  # List sensor yang anomali
    'anomaly_details': [
        {
            'sensor': 'ammonia',
            'value': 25.5,
            'message': 'Amoniak tinggi (nilai: 25.5 ppm)'
        },
        ...
    ],
    'sensor_values': {
        'ammonia': float,
        'temperature': float,
        'humidity': float,
        'light': float
    }
}
```

### Response Anomali di `/predict`:
```json
{
    "anomalies": [
        {
            "type": "ammonia",  // ✅ Bukan "unknown" lagi!
            "value": 25.5,
            "time": "2025-11-22 17:00",
            "message": "Amoniak tinggi (nilai: 25.5 ppm)",
            "status": "ANOMALI",
            "anomaly_score": -0.65,
            "severity": "warning"
        }
    ]
}
```

## 🧪 Testing

### Test 1: Deteksi Anomali Amoniak
```bash
curl -X POST http://localhost:5000/api/detect-anomaly \
  -H "Content-Type: application/json" \
  -d '{
    "amonia_ppm": 30,
    "suhu_c": 28,
    "kelembaban_rh": 65,
    "cahaya_lux": 35
  }'
```

**Expected:**
- `is_anomaly`: true
- `anomaly_sensors`: ["ammonia"]
- `anomaly_details[0].sensor`: "ammonia"
- `anomaly_details[0].message`: "Amoniak tinggi (nilai: 30.0 ppm)"

### Test 2: Deteksi Anomali Cahaya
```bash
curl -X POST http://localhost:5000/api/detect-anomaly \
  -H "Content-Type: application/json" \
  -d '{
    "amonia_ppm": 10,
    "suhu_c": 28,
    "kelembaban_rh": 65,
    "cahaya_lux": 5
  }'
```

**Expected:**
- `is_anomaly`: true
- `anomaly_sensors`: ["light"]
- `anomaly_details[0].sensor`: "light"
- `anomaly_details[0].message`: "Cahaya kurang optimal (nilai: 5.0 lux)"

## ✅ Checklist

- [x] Update fungsi `detect_anomaly` untuk identifikasi sensor spesifik
- [x] Update endpoint `/api/detect-anomaly`
- [x] Update endpoint `/predict` untuk menggunakan detail sensor
- [x] Perbaiki nilai default cahaya di Laravel (700 → 35/22)
- [x] Update threshold sesuai model_metadata.json
- [x] Tambahkan fallback logic untuk anomali tanpa sensor spesifik

## 🎯 Hasil

1. ✅ **Deteksi anomali sekarang menunjukkan sensor spesifik** (ammonia, temperature, humidity, light)
2. ✅ **Threshold disesuaikan** dengan model_metadata.json
3. ✅ **Nilai default cahaya diperbaiki** (700 → 35/22 lux sesuai dataset)
4. ✅ **Tidak lagi menampilkan "unknown"** untuk anomali

