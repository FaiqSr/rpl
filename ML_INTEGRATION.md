# Integrasi Machine Learning untuk Monitoring Kandang Ayam

Dokumentasi lengkap untuk mengintegrasikan model ML Anda dengan sistem monitoring ChickPatrol.

## 📋 File Model yang Diperlukan

Pastikan Anda memiliki file-file berikut di folder `ml_service/models/`:

- ✅ `model_lstm_kandang.h5` - Model LSTM untuk prediksi tren
- ✅ `model_random_forest.pkl` - Model Random Forest untuk klasifikasi status
- ✅ `model_isolation_forest.pkl` - Model Isolation Forest untuk deteksi anomali
- ✅ `scaler_rf.pkl` - Scaler untuk Random Forest
- ✅ `scaler_lstm.pkl` - Scaler untuk LSTM
- ✅ `scaler_if.pkl` - Scaler untuk Isolation Forest
- ✅ `model_metadata.json` - Metadata model (nama, versi, akurasi)

## 🚀 Setup Cepat

### 1. Setup ML Service

```bash
cd ml_service
# Windows
setup.bat

# Linux/Mac
chmod +x setup.sh
./setup.sh
```

Atau manual:
```bash
cd ml_service
mkdir models
pip install -r requirements.txt
```

### 2. Copy Model Files

Copy semua file model ke folder `ml_service/models/`:
- `model_lstm_kandang.h5`
- `model_random_forest.pkl`
- `model_isolation_forest.pkl`
- `scaler_rf.pkl`
- `scaler_lstm.pkl`
- `scaler_if.pkl`
- `model_metadata.json`

### 3. Update SEQUENCE_LENGTH (Jika Perlu)

Edit `ml_service/app.py` jika model LSTM Anda menggunakan sequence length berbeda:
```python
SEQUENCE_LENGTH = 30  # Sesuaikan dengan model Anda
```

### 4. Jalankan ML Service

```bash
cd ml_service
python app.py
```

Service akan berjalan di `http://localhost:5000`

### 5. Konfigurasi Laravel

Tambahkan di file `.env` Laravel:
```env
ML_SERVICE_URL=http://localhost:5000
```

## 📡 Format API

### Endpoint: `POST /predict`

**Request Body:**
```json
{
  "history": [
    {
      "time": "2025-11-22 14:00",
      "temperature": 25.5,
      "humidity": 65.2,
      "ammonia": 12.3,
      "light": 750
    },
    // ... minimal 30 data points (sesuai SEQUENCE_LENGTH)
  ]
}
```

**Response Body:**
```json
{
  "prediction_6h": {
    "temperature": [26.1, 26.5, 26.8, 27.0, 27.2, 27.3],
    "humidity": [64.5, 64.0, 63.5, 63.0, 62.5, 62.0],
    "ammonia": [13.0, 13.5, 14.0, 14.5, 15.0, 15.5],
    "light": [720, 680, 640, 600, 560, 520]
  },
  "prediction_24h": {
    "temperature": [26.1, 26.5, ..., 28.0],
    "humidity": [64.5, 64.0, ..., 60.0],
    "ammonia": [13.0, 13.5, ..., 18.0],
    "light": [720, 680, ..., 200]
  },
  "anomalies": [
    {
      "type": "temperature",
      "value": 32.5,
      "time": "2025-11-22 15:00",
      "message": "Suhu di luar rentang optimal (20-30°C): 32.5°C",
      "severity": "critical"
    }
  ],
  "status": {
    "label": "baik",
    "severity": "normal",
    "message": "Status kandang: BAIK (Keyakinan: 92.5%)"
  },
  "model_name": "LSTM_Poultry_Environment",
  "model_version": "2.1",
  "accuracy": 0.92,
  "prediction_time": 145,
  "confidence": "high"
}
```

### Endpoint: `GET /health`

Test koneksi:
```json
{
  "status": "ok",
  "models_loaded": true,
  "timestamp": "2025-11-22T15:30:00"
}
```

### Endpoint: `POST /classify` (Optional)

Klasifikasi status real-time:
```json
{
  "ammonia": 22.5,
  "temperature": 29.5,
  "humidity": 62.0,
  "light": 250.0
}
```

### Endpoint: `POST /anomaly` (Optional)

Deteksi anomali real-time:
```json
{
  "ammonia": 22.5,
  "temperature": 29.5,
  "humidity": 62.0,
  "light": 250.0
}
```

## 🧪 Testing

### Test Health
```bash
curl http://localhost:5000/health
```

### Test Prediction
```bash
curl -X POST http://localhost:5000/predict \
  -H "Content-Type: application/json" \
  -d @test_data.json
```

Atau gunakan file `test_data.json` yang sudah disediakan.

## 📊 Model yang Digunakan

### 1. LSTM (Long Short-Term Memory)
- **Fungsi**: Prediksi tren sensor 6 jam dan 24 jam ke depan
- **Input**: 30 data point terakhir (sequence)
- **Output**: Prediksi nilai sensor berikutnya

### 2. Random Forest
- **Fungsi**: Klasifikasi status kandang
- **Output**: BAIK / PERHATIAN / BURUK
- **Confidence**: Probabilitas untuk setiap kelas

### 3. Isolation Forest
- **Fungsi**: Deteksi anomali pada data sensor
- **Output**: Normal / Anomali
- **Score**: Anomaly score untuk tingkat keparahan

## 🎯 Tampilan di Dashboard

Hasil ML akan ditampilkan di dashboard monitoring dengan:

1. **Card Informasi ML**: 
   - Status koneksi (terhubung/tidak)
   - Nama model & versi
   - Akurasi model
   - Tingkat keyakinan
   - Waktu prediksi

2. **Prediksi 6 & 24 Jam**: 
   - Grafik tren dengan prediksi
   - Ringkasan prediksi per parameter

3. **Deteksi Anomali**: 
   - Daftar anomali yang terdeteksi
   - Severity (warning/critical)
   - Pesan rekomendasi

4. **Status Kandang**: 
   - Label status (baik/perlu perhatian/tidak optimal)
   - Probabilitas untuk setiap status
   - Confidence level

## 🔧 Troubleshooting

### 1. ML Service tidak terhubung
- ✅ Cek apakah ML service berjalan: `python app.py`
- ✅ Cek URL di `.env` sudah benar: `ML_SERVICE_URL=http://localhost:5000`
- ✅ Cek firewall/network
- ✅ Test dengan: `curl http://localhost:5000/health`

### 2. Error saat prediksi
- ✅ Sistem akan otomatis fallback ke prediksi sederhana
- ✅ Cek log Laravel untuk detail error
- ✅ Pastikan history data minimal 30 points
- ✅ Cek format data sesuai dokumentasi

### 3. Model tidak dimuat
- ✅ Cek semua file model ada di `ml_service/models/`
- ✅ Cek permission file
- ✅ Cek format file (h5 untuk LSTM, pkl untuk lainnya)
- ✅ Lihat error di console saat `python app.py`

### 4. Format response tidak sesuai
- ✅ Pastikan response mengikuti format yang dijelaskan
- ✅ Cek semua field required ada
- ✅ Pastikan tipe data sesuai (array untuk prediction, object untuk status)

### 5. SEQUENCE_LENGTH error
- ✅ Edit `SEQUENCE_LENGTH` di `app.py` sesuai model Anda
- ✅ Pastikan history data minimal sama dengan SEQUENCE_LENGTH

## 📝 Format model_metadata.json

Contoh isi `model_metadata.json`:
```json
{
  "model_name": "LSTM_Poultry_Environment_v2",
  "model_version": "2.1",
  "accuracy": 0.92,
  "created_at": "2025-11-22",
  "description": "Model untuk prediksi sensor kandang ayam"
}
```

## 🔄 Workflow Integrasi

1. **Laravel** mengirim request ke `/api/monitoring/tools`
2. **Laravel ML Service** (`MachineLearningService.php`) memanggil ML service
3. **ML Service** (Flask) memproses dengan model:
   - LSTM untuk prediksi
   - Random Forest untuk klasifikasi
   - Isolation Forest untuk anomali
4. **ML Service** mengembalikan hasil ke Laravel
5. **Laravel** menampilkan hasil di dashboard

## 🎉 Selesai!

Setelah setup, dashboard monitoring akan otomatis menggunakan hasil ML Anda untuk:
- ✅ Prediksi tren sensor
- ✅ Klasifikasi status kandang
- ✅ Deteksi anomali
- ✅ Rekomendasi tindakan

Selamat menggunakan! 🚀
