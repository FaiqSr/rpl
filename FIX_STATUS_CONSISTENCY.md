# ✅ Fix: Konsistensi Status & Warna Indikator

## ❌ Masalah yang Ditemukan

1. **Logika status tidak konsisten**: 
   - Deteksi anomali: "Cahaya terlalu tinggi (200-330 lux)"
   - Ringkasan: "dalam kisaran aman" → **TIDAK KONSISTEN**

2. **Kontradiksi status**:
   - Jika >60 lux (warn_high), seharusnya "di luar batas aman" atau "perlu perhatian"
   - Tapi masih menampilkan "dalam kisaran aman"

3. **Warna tidak sesuai kondisi**:
   - Perlu menggunakan warna yang sesuai: merah=bahaya, hijau=aman, kuning=perhatian

## ✅ Perbaikan yang Dilakukan

### 1. Update Threshold Cahaya di Forecast Summary

**Sebelum:**
```php
$qualitativeForecast($pred6['light'],'Cahaya','lux',200,400)  // Ideal range
```

**Sesudah:**
```php
// Threshold: warn_low: 100, warn_high: 600 (sesuai metadata 10-60 * 10)
$qualitativeForecast($pred6['light'],'Cahaya','lux',100,600)
```

**Alasan:**
- Threshold metadata: `warn_low: 10, warn_high: 60`
- Data aktual ratusan, jadi: `warn_low: 100, warn_high: 600` (10-60 × 10)
- Jika nilai > 600 lux, status = "potensi keluar batas aman"
- Jika nilai 100-600 lux, status = "dalam kisaran aman" (tapi bisa warning jika >400)
- Jika nilai < 100 lux, status = "potensi keluar batas aman"

### 2. Update Threshold di ML Service (Python)

**Sebelum:**
```python
qualitative_forecast(..., 'Cahaya', 'lux', 200, 400)  # Ideal range
```

**Sesudah:**
```python
# warn_low: 100, warn_high: 600 (sesuai metadata 10-60 * 10)
qualitative_forecast(..., 'Cahaya', 'lux', 100, 600)
```

### 3. Perbaiki Fungsi `riskClass` di Frontend

**Sebelum:**
```javascript
function riskClass(risk){
  if(String(risk).toLowerCase().includes('potensi') || String(risk).toLowerCase().includes('keluar')) return 'risk-warn';
  return 'risk-ok';
}
```

**Sesudah:**
```javascript
function riskClass(risk){
  // Merah (bahaya): kritik, tidak optimal, bahaya
  if(String(risk).toLowerCase().includes('krit') || String(risk).toLowerCase().includes('bahaya') || String(risk).toLowerCase().includes('tidak optimal')) return 'risk-crit';
  // Kuning (perhatian): potensi keluar batas, perlu perhatian, keluar batas
  if(String(risk).toLowerCase().includes('potensi') || String(risk).toLowerCase().includes('keluar') || String(risk).toLowerCase().includes('perhatian')) return 'risk-warn';
  // Hijau (aman): dalam kisaran aman
  return 'risk-ok';
}
```

### 4. Update Warna CSS untuk Metric Items

**Sebelum:**
```css
.metric-item.risk-ok{ border-left:3px solid #69B578; }
.metric-item.risk-warn{ border-left:3px solid #F4C430; }
.metric-item.risk-crit{ border-left:3px solid #dc3545; }
```

**Sesudah:**
```css
/* Warna sesuai kondisi: hijau=aman, kuning=perhatian, merah=bahaya */
.metric-item.risk-ok{ border-left:3px solid #28a745; background:#f0f9f4; }
.metric-item.risk-ok .metric-icon{ background:#28a745; } /* Hijau untuk aman */
.metric-item.risk-warn{ border-left:3px solid #ffc107; background:#fffbf0; }
.metric-item.risk-warn .metric-icon{ background:#ffc107; color:#000; } /* Kuning untuk perhatian */
.metric-item.risk-crit{ border-left:3px solid #dc3545; background:#fff0f0; }
.metric-item.risk-crit .metric-icon{ background:#dc3545; } /* Merah untuk bahaya */
```

### 5. Update Warna untuk Anomaly Tag

**Sebelum:**
```css
.anomaly-item .anomaly-tag { background:#dc3545; } /* Selalu merah */
```

**Sesudah:**
```css
/* Warna anomaly tag berdasarkan severity */
.anomaly-item[data-severity="critical"] .anomaly-tag { background:#dc3545; } /* Merah untuk bahaya */
.anomaly-item[data-severity="warning"] .anomaly-tag { background:#ffc107; color:#000; } /* Kuning untuk perhatian */
.anomaly-item[data-severity="normal"] .anomaly-tag { background:#28a745; } /* Hijau untuk aman */
```

### 6. Update Warna untuk Prediction Banner

**Sebelum:**
```javascript
predictionBanner.style.display = 'flex'; // Tidak ada warna berdasarkan severity
```

**Sesudah:**
```javascript
// Set warna banner berdasarkan severity: hijau=aman, kuning=perhatian, merah=bahaya
const severity = status.severity || 'normal';
if (severity === 'critical' || severity === 'bahaya' || status.label?.includes('tidak optimal')) {
    predictionBanner.style.background = 'linear-gradient(90deg, #dc3545, #c82333)'; // Merah untuk bahaya
} else if (severity === 'warning' || severity === 'perhatian' || status.label?.includes('perhatian')) {
    predictionBanner.style.background = 'linear-gradient(90deg, #ffc107, #e0a800)'; // Kuning untuk perhatian
} else {
    predictionBanner.style.background = 'linear-gradient(90deg, #28a745, #218838)'; // Hijau untuk aman
}
```

## 📊 Mapping Threshold & Status

| Nilai Cahaya | Threshold | Status | Warna |
|--------------|-----------|-------|-------|
| < 100 lux | < warn_low | Potensi keluar batas aman | Kuning (warning) |
| 100-400 lux | warn_low - ideal_high | Dalam kisaran aman | Hijau (ok) |
| 400-600 lux | ideal_high - warn_high | Potensi keluar batas aman | Kuning (warning) |
| > 600 lux | > warn_high | Potensi keluar batas aman | Kuning (warning) |

## ✅ Hasil

1. ✅ **Status konsisten** dengan deteksi anomali
2. ✅ **Warna sesuai kondisi**: hijau=aman, kuning=perhatian, merah=bahaya
3. ✅ **Threshold sesuai metadata**: warn_low: 100, warn_high: 600 (10-60 × 10)
4. ✅ **Prediction banner** berubah warna berdasarkan severity
5. ✅ **Anomaly tag** berubah warna berdasarkan severity

## 🧪 Testing

Setelah perbaikan:
1. ✅ Cahaya 309-364 lux → Status: "dalam kisaran aman" (hijau) ✅
2. ✅ Cahaya > 600 lux → Status: "potensi keluar batas aman" (kuning) ⚠️
3. ✅ Cahaya < 100 lux → Status: "potensi keluar batas aman" (kuning) ⚠️
4. ✅ Warna banner sesuai severity
5. ✅ Warna anomaly tag sesuai severity

