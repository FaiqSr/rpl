# ML Service untuk Monitoring Kandang Ayam

Flask API service untuk integrasi model Machine Learning dengan sistem Laravel.

## Setup

### 1. Install Dependencies

```bash
pip install -r requirements.txt
```

### 2. Struktur Folder

Pastikan struktur folder seperti ini:

```
ml_service/
├── app.py
├── requirements.txt
├── models/
│   ├── model_lstm_kandang.h5
│   ├── model_random_forest.pkl
│   ├── model_isolation_forest.pkl
│   ├── scaler_rf.pkl
│   ├── scaler_lstm.pkl
│   ├── scaler_if.pkl
│   └── model_metadata.json
└── README.md
```

### 3. Copy Model Files

Copy semua file model ke folder `models/`:
- `model_lstm_kandang.h5`
- `model_random_forest.pkl`
- `model_isolation_forest.pkl`
- `scaler_rf.pkl`
- `scaler_lstm.pkl`
- `scaler_if.pkl`
- `model_metadata.json`

### 4. Update SEQUENCE_LENGTH

Jika model LSTM Anda menggunakan sequence length berbeda, edit di `app.py`:
```python
SEQUENCE_LENGTH = 30  # Sesuaikan dengan model Anda
```

### 5. Run Service

```bash
python app.py
```

Service akan berjalan di `http://localhost:5000`

## Endpoints

### 1. Health Check
```
GET /health
```

### 2. Main Prediction (untuk Laravel)
```
POST /predict
Body: {
  "history": [
    {
      "time": "2025-11-22 14:00",
      "temperature": 25.5,
      "humidity": 65.2,
      "ammonia": 12.3,
      "light": 750
    },
    // ... minimal 30 data points
  ]
}
```

### 3. Classify Status (optional)
```
POST /classify
Body: {
  "ammonia": 22.5,
  "temperature": 29.5,
  "humidity": 62.0,
  "light": 250.0
}
```

### 4. Detect Anomaly (optional)
```
POST /anomaly
Body: {
  "ammonia": 22.5,
  "temperature": 29.5,
  "humidity": 62.0,
  "light": 250.0
}
```

## Testing

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

## Integrasi dengan Laravel

1. Set di `.env` Laravel:
```env
ML_SERVICE_URL=http://localhost:5000
```

2. Laravel akan otomatis menggunakan service ini untuk prediksi

## Troubleshooting

1. **Model tidak dimuat:**
   - Cek path folder `models/`
   - Pastikan semua file ada
   - Cek permission file

2. **Error prediction:**
   - Pastikan history data minimal 30 points
   - Cek format data sesuai dokumentasi

3. **Port sudah digunakan:**
   - Ubah port di `app.py`: `app.run(port=5001)`
   - Update `ML_SERVICE_URL` di Laravel

