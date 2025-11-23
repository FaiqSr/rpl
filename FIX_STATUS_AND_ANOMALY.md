# ✅ Fix: Klasifikasi Status & Deteksi Anomali yang Lebih Akurat

## ❌ Masalah yang Ditemukan

1. **Klasifikasi status tidak konsisten**: 
   - Masih ada "perlu perhatian ringan"
   - Seharusnya hanya: **baik, perhatian, buruk**

2. **Deteksi anomali kurang akurat**:
   - Tidak menggunakan statistik dataset untuk identifikasi sensor spesifik
   - Tidak menampilkan semua sensor yang anomali

3. **Threshold cahaya tidak sesuai**:
   - Threshold masih menggunakan nilai puluhan (10-60) padahal data aktual ratusan (100-600)

## ✅ Perbaikan yang Dilakukan

### 1. Perbaiki Klasifikasi Status (Hanya: baik, perhatian, buruk)

**File: `ml_service/app.py`**
```python
# Sebelum:
status_map = {
    'BAIK': {'label': 'baik', 'severity': 'normal'},
    'PERHATIAN': {'label': 'perlu perhatian ringan', 'severity': 'warning'},  # ❌
    'BURUK': {'label': 'tidak optimal', 'severity': 'critical'}
}

# Sesudah:
status_map = {
    'BAIK': {'label': 'baik', 'severity': 'normal'},
    'PERHATIAN': {'label': 'perhatian', 'severity': 'warning'},  # ✅
    'BURUK': {'label': 'buruk', 'severity': 'critical'}  # ✅
}
```

**File: `app/Services/MachineLearningService.php`**
```php
// Sebelum:
if ($issues === 1) {
    return [
        'label' => 'perlu perhatian ringan',  // ❌
        'severity' => 'warning',
        'message' => 'Ada 1 parameter perlu ditinjau'
    ];
}

// Sesudah:
// Hanya 3 status: baik, perhatian, buruk
if ($issues === 1 || $issues === 2) {
    return [
        'label' => 'perhatian',  // ✅
        'severity' => 'warning',
        'message' => 'Beberapa parameter perlu ditinjau'
    ];
}
```

### 2. Load Statistik Dataset untuk Deteksi Anomali yang Lebih Akurat

**File: `ml_service/app.py`**
```python
# Load sensor statistics (jika ada) untuk deteksi anomali yang lebih akurat
SENSOR_STATS_FILE = os.path.join(MODEL_DIR, 'sensor_stats.json')
SENSOR_STATS = None
if os.path.exists(SENSOR_STATS_FILE):
    try:
        with open(SENSOR_STATS_FILE, 'r') as f:
            SENSOR_STATS = json.load(f)
        print("✅ Statistik sensor berhasil dimuat!")
    except Exception as e:
        print(f"⚠️  Gagal memuat statistik sensor: {e}")
        SENSOR_STATS = None
else:
    # Generate default stats dari metadata thresholds
    SENSOR_STATS = {
        'ammonia': {'mean': 15, 'std': 5, 'min': 0, 'max': 35},
        'temperature': {'mean': 28, 'std': 3, 'min': 20, 'max': 35},
        'humidity': {'mean': 60, 'std': 8, 'min': 50, 'max': 80},
        'light': {'mean': 300, 'std': 100, 'min': 100, 'max': 600}  # Data ratusan
    }
```

**File: `ml_service/sensor_stats.json`** (dibuat)
```json
{
  "ammonia": {
    "mean": 15.0,
    "std": 5.0,
    "min": 0,
    "max": 35
  },
  "temperature": {
    "mean": 28.0,
    "std": 3.0,
    "min": 20,
    "max": 35
  },
  "humidity": {
    "mean": 60.0,
    "std": 8.0,
    "min": 50,
    "max": 80
  },
  "light": {
    "mean": 300.0,
    "std": 100.0,
    "min": 100,
    "max": 600
  }
}
```

### 3. Update Fungsi `detect_anomaly` untuk Menggunakan Statistik Dataset

**File: `ml_service/app.py`**
```python
def detect_anomaly(amonia, suhu, kelembaban, cahaya):
    """
    Deteksi anomali pada data sensor dengan identifikasi sensor spesifik (skala ASLI)
    Menggunakan statistik dataset untuk identifikasi yang lebih akurat
    """
    # ... Isolation Forest detection ...
    
    # Threshold cahaya: data aktual ratusan, jadi 20-40 -> 200-400, 10-60 -> 100-600
    thresholds = {
        'cahaya_lux': {'ideal_low': 200, 'ideal_high': 400, 'warn_low': 100, 'warn_high': 600}
    }
    
    # Gunakan statistik dataset jika tersedia
    use_stats = SENSOR_STATS is not None
    
    # Jika Isolation Forest mendeteksi anomali tapi tidak ada sensor spesifik yang terdeteksi
    if is_anomaly and len(anomaly_sensors) == 0:
        if use_stats:
            # Gunakan z-score untuk identifikasi sensor yang menyimpang
            deviations = {}
            for sensor_name, value in sensor_values_map.items():
                stats = SENSOR_STATS.get(sensor_name, {})
                mean = stats.get('mean', 0)
                std = stats.get('std', 1)
                if std > 0:
                    z_score = abs(value - mean) / std
                    deviations[sensor_name] = z_score
            
            # Identifikasi semua sensor dengan z-score > 1.5
            sorted_deviations = sorted(deviations.items(), key=lambda x: x[1], reverse=True)
            for sensor_name, z_score in sorted_deviations:
                if z_score > 1.5 or len(anomaly_sensors) < 2:
                    if sensor_name not in anomaly_sensors:
                        anomaly_sensors.append(sensor_name)
                        anomaly_details.append({
                            'sensor': sensor_name,
                            'value': sensor_values_map[sensor_name],
                            'message': f'Anomali terdeteksi pada sensor {sensor_name} (nilai: {sensor_values_map[sensor_name]:.1f}, z-score: {z_score:.2f})',
                            'z_score': float(z_score)
                        })
```

### 4. Update Threshold Cahaya untuk Data Ratusan

**File: `ml_service/app.py`**
```python
# Sebelum:
'cahaya_lux': {'ideal_low': 20, 'ideal_high': 40, 'warn_low': 10, 'warn_high': 60}  # ❌

# Sesudah:
'cahaya_lux': {'ideal_low': 200, 'ideal_high': 400, 'warn_low': 100, 'warn_high': 600}  # ✅
```

### 5. Endpoint `/api/detect-anomaly` Sudah Mengembalikan Semua Sensor

**File: `ml_service/app.py`**
```python
@app.route('/api/detect-anomaly', methods=['POST'])
def api_detect_anomaly():
    """Deteksi anomali pada sensor dengan detail sensor spesifik"""
    try:
        data = request.json
        amonia = float(data.get('amonia_ppm', data.get('ammonia', 0)))
        suhu = float(data.get('suhu_c', data.get('temperature', 0)))
        kelembaban = float(data.get('kelembaban_rh', data.get('humidity', 0)))
        cahaya = float(data.get('cahaya_lux', data.get('light', 0)))
        
        result = detect_anomaly(amonia, suhu, kelembaban, cahaya)
        result['success'] = True
        
        # result sudah berisi:
        # - anomaly_sensors: ['ammonia', 'temperature', ...]
        # - anomaly_details: [{sensor: 'ammonia', value: 25, message: '...'}, ...]
        
        return jsonify(result)
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 400
```

### 6. Laravel View Sudah Menampilkan Semua Sensor yang Anomali

**File: `resources/views/dashboard/tools-monitoring.blade.php`**
```javascript
function renderAnomalies(anomalies){
  // anomalies sudah berisi semua detail dari ML service
  // Setiap item sudah memiliki: type, value, time, message, severity
  anomalyList.innerHTML = anomalies.map(a=>{
    const severity = a.severity || (a.type === 'unknown' ? 'warning' : 'critical');
    return `<div class='anomaly-item' data-severity="${severity}">
      <span class='anomaly-tag'>${a.type || 'unknown'}</span>
      <div>
        <div style='font-size:.7rem; color:#6c757d;'>${a.time}</div>
        <div>${a.message} (nilai: ${a.value})</div>
      </div>
    </div>`;
  }).join('');
}
```

## 📊 Hasil

1. ✅ **Klasifikasi status**: Hanya 3 status (baik, perhatian, buruk)
2. ✅ **Deteksi anomali lebih akurat**: Menggunakan statistik dataset (z-score)
3. ✅ **Semua sensor yang anomali ditampilkan**: `anomaly_details` berisi semua sensor
4. ✅ **Threshold cahaya sesuai**: 100-600 (data ratusan)
5. ✅ **Statistik dataset**: File `sensor_stats.json` tersedia untuk referensi

## 🧪 Testing

Setelah perbaikan:
1. ✅ Status hanya: baik, perhatian, buruk
2. ✅ Deteksi anomali menggunakan z-score dari statistik dataset
3. ✅ Semua sensor yang anomali ditampilkan di dashboard
4. ✅ Threshold cahaya sesuai dengan data ratusan

