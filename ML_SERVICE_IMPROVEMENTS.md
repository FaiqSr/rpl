# ML Service Improvements

## Ringkasan Perbaikan

Tiga perbaikan utama telah diterapkan pada `ml_service/app.py`:

### 1. ✅ Logika Threshold Random Forest yang Lebih Robust

**Fungsi Baru: `predict_status_robust()`**
- Logika prioritas yang jelas:
  1. Jika probabilitas BURUK >= threshold → BURUK (prioritas tertinggi)
  2. Jika probabilitas PERHATIAN > BURUK dan PERHATIAN > BAIK → PERHATIAN
  3. Jika probabilitas BAIK tertinggi → BAIK
- Mendukung model yang dilatih dengan classes [1, 2] atau [0, 1, 2]
- Menggunakan threshold optimal dari `threshold_config.json` (default: 0.55)

**Fungsi yang Diperbaiki: `predict_sensor_classification()`**
- Sekarang menggunakan `predict_status_robust()` untuk logika yang lebih jelas
- Menambahkan decorator `@log_performance` untuk tracking waktu eksekusi
- Return format yang lebih konsisten dengan field `label`, `status`, `severity`, `message`, `confidence`, `probability`

### 2. ✅ Error Handling dan Validasi Input yang Komprehensif

**Endpoint `/predict` dengan Validasi Lengkap:**
1. ✅ Validasi Content-Type (harus application/json)
2. ✅ Validasi request body tidak kosong
3. ✅ Validasi field 'history' ada
4. ✅ Validasi history adalah list/array
5. ✅ Validasi jumlah data history (minimal 30 data points)
6. ✅ Validasi format setiap data point (dict atau list)
7. ✅ Validasi tipe data numeric untuk semua sensor values
8. ✅ Validasi konversi ke numpy array
9. ✅ Error handling untuk setiap tahap prediksi (LSTM, Random Forest, Isolation Forest)
10. ✅ Logging error yang detail dengan stack trace

**Error Response Format:**
```json
{
  "error": "Error description",
  "details": "Detailed error message (only in debug mode)"
}
```

### 3. ✅ Monitoring dan Logging

**Setup Logging:**
- Rotating file handler (max 10MB, 5 backup files)
- Log file: `ml_service/logs/ml_service.log`
- Console output untuk development
- Log level: INFO

**Metrics Tracking:**
- `total_requests`: Total jumlah request
- `successful_predictions`: Jumlah prediksi sukses
- `failed_predictions`: Jumlah prediksi gagal
- `avg_processing_time`: Rata-rata waktu processing
- `model_performance`: Metrics per model (LSTM, Random Forest, Isolation Forest)
  - `count`: Jumlah penggunaan
  - `avg_time`: Rata-rata waktu eksekusi

**Decorator `@log_performance`:**
- Otomatis track waktu eksekusi setiap fungsi prediksi
- Update metrics per model
- Log info/error dengan timestamp

**Endpoint Baru:**
- `GET /health`: Health check dengan informasi detail model
- `GET /metrics`: Endpoint untuk melihat metrics

## Perubahan File

### `ml_service/app.py`

**Import Baru:**
```python
import logging
import time
from functools import wraps
from logging.handlers import RotatingFileHandler
```

**Fungsi Baru:**
- `log_performance()`: Decorator untuk logging performance
- `predict_status_robust()`: Logika threshold yang lebih robust
- `get_metrics()`: Endpoint untuk metrics

**Fungsi yang Diperbaiki:**
- `predict_sensor_classification()`: Menggunakan `predict_status_robust()`
- `predict()`: Validasi dan error handling lengkap
- `health()`: Informasi model yang lebih detail

## Cara Menggunakan

### 1. Logging
Log otomatis tersimpan di `ml_service/logs/ml_service.log`:
```bash
tail -f ml_service/logs/ml_service.log
```

### 2. Metrics
Akses metrics endpoint:
```bash
curl http://localhost:5000/metrics
```

### 3. Health Check
Cek status service dan model:
```bash
curl http://localhost:5000/health
```

## Testing

### Test Validasi Input
```bash
# Test dengan history kurang dari 30
curl -X POST http://localhost:5000/predict \
  -H "Content-Type: application/json" \
  -d '{"history": [{"ammonia": 10, "temperature": 25, "humidity": 60, "light": 30}]}'

# Test dengan format salah
curl -X POST http://localhost:5000/predict \
  -H "Content-Type: application/json" \
  -d '{"history": "invalid"}'
```

### Test Metrics
```bash
# Setelah beberapa prediksi, cek metrics
curl http://localhost:5000/metrics
```

## Catatan Penting

1. **Log Directory**: Pastikan directory `ml_service/logs/` ada atau akan dibuat otomatis
2. **Performance**: Decorator `@log_performance` menambahkan overhead minimal (~0.001s)
3. **Error Messages**: Detail error hanya ditampilkan jika `app.debug = True`
4. **Metrics**: Metrics disimpan di memory, akan reset saat service restart

## Next Steps

1. ✅ Logika threshold Random Forest yang lebih robust
2. ✅ Error handling dan validasi input
3. ✅ Monitoring dan logging
4. ⏳ (Optional) Persist metrics ke database
5. ⏳ (Optional) Alerting untuk error rate tinggi
6. ⏳ (Optional) Rate limiting untuk endpoint

