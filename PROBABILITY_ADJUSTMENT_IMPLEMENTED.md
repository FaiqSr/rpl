# ✅ Probability Adjustment Based on New Thresholds - IMPLEMENTED

## 🎯 Masalah yang Diselesaikan

Model Random Forest dilatih dengan threshold lama (dari `model_metadata.json`), tetapi threshold sekarang bisa diubah di database. Model masih menghasilkan probabilitas berdasarkan threshold lama, sehingga probabilitas tidak sesuai dengan threshold baru di database.

## ✅ Solusi yang Diimplementasikan

**Post-processing probability adjustment**: Menyesuaikan probabilitas dari model ML dengan threshold baru dari database tanpa perlu retrain model.

## 📋 Implementasi

### 1. Fungsi `calculateThresholdScore()`
Menghitung probabilitas berdasarkan validasi threshold baru dari database.

**Lokasi**: `routes/web.php` (dalam route `/api/monitoring/tools`)

**Logika**:
- Validasi setiap sensor (suhu, kelembaban, amonia, cahaya) dengan threshold baru
- Hitung jumlah `criticalIssues` dan `warnings`
- Generate probabilitas berdasarkan jumlah issues:
  - 3+ critical issues → BURUK: 90%, PERHATIAN: 10%, BAIK: 0%
  - 2 critical issues → BURUK: 70%, PERHATIAN: 30%, BAIK: 0%
  - 1 critical issue atau 2+ warnings → BURUK: 30%, PERHATIAN: 60%, BAIK: 10%
  - 1 warning → PERHATIAN: 80%, BAIK: 20%, BURUK: 0%
  - 0 issues → BAIK: 95%, PERHATIAN: 5%, BURUK: 0%

### 2. Fungsi `adjustProbabilitiesBasedOnThreshold()`
Menggabungkan probabilitas ML (original) dengan threshold score menggunakan weighted average.

**Formula**:
```
Adjusted = (ML Probability × 0.6) + (Threshold Score × 0.4)
```

**Weight**:
- ML: 60% (dari model yang sudah dilatih)
- Threshold: 40% (dari validasi threshold baru)

**Normalisasi**: Pastikan total probabilitas = 1.0

### 3. Integrasi ke Route `/api/monitoring/tools`

**Flow**:
1. Get ML predictions dari model (original probabilities)
2. Get threshold baru dari database
3. Calculate threshold score berdasarkan threshold baru
4. Adjust probabilities (combine ML + Threshold)
5. Determine final status dari adjusted probabilities
6. Update ML status dengan adjusted probabilities

### 4. Final Status Determination

**Prioritas**:
1. **Safety Override**: Jika 3+ critical issues → HARUS BURUK
2. **Safety Override**: Jika 0 issues → HARUS BAIK
3. **Primary Decision**: Gunakan adjusted probabilities

**Confidence Calculation**:
- Base: confidence dari adjusted probabilities
- Boost: +0.1 jika adjusted probability sesuai dengan threshold validation
- Penalty: -0.2 jika berbeda dengan threshold validation (untuk critical cases)

## 📊 Struktur Data Response

### Status Object
```php
[
    'label' => 'BAIK|PERHATIAN|BURUK',  // Dari adjusted probabilities
    'confidence' => 0.85,              // Confidence dari adjusted probabilities
    'probability' => [                 // ✅ ADJUSTED probabilities
        'BAIK' => 0.52,
        'PERHATIAN' => 0.42,
        'BURUK' => 0.06
    ],
    'ml_prediction' => [                // Original dari model
        'status' => 'PERHATIAN',
        'probabilities' => [            // Original probabilities
            'BAIK' => 0.236,
            'PERHATIAN' => 0.672,
            'BURUK' => 0.091
        ],
        'confidence' => 0.7
    ],
    'adjusted_probabilities' => [       // Adjusted probabilities
        'BAIK' => 0.52,
        'PERHATIAN' => 0.42,
        'BURUK' => 0.06
    ],
    'threshold_score' => [              // Threshold validation score
        'BAIK' => 0.95,
        'PERHATIAN' => 0.05,
        'BURUK' => 0.0
    ],
    'threshold_validation' => [
        'status' => 'BAIK',
        'issues_count' => 0,
        'critical_issues' => 0,
        'warning_issues' => 0
    ]
]
```

## 🔍 Logging

Semua proses di-log untuk debugging:
- `=== PROBABILITY ADJUSTMENT ===`: ML probabilities original, threshold score, adjusted probabilities
- `=== HYBRID ML + THRESHOLD DECISION ===`: Final decision dengan reasoning
- `=== USING ADJUSTED PROBABILITIES (NO THRESHOLD VALIDATION) ===`: Jika threshold tidak tersedia

## 🧪 Testing

### Test 1: Cek Adjusted Probabilities
```bash
curl http://localhost:8000/api/monitoring/tools | jq '.status.probability'
```

Harus return adjusted probabilities (bukan original dari model).

### Test 2: Cek Original ML Probabilities
```bash
curl http://localhost:8000/api/monitoring/tools | jq '.status.ml_prediction.probabilities'
```

Harus return original probabilities dari model.

### Test 3: Cek Threshold Score
```bash
curl http://localhost:8000/api/monitoring/tools | jq '.status.threshold_score'
```

Harus return threshold score berdasarkan threshold baru dari database.

### Test 4: Ubah Threshold di Database
1. Ubah threshold di database (misalnya: amonia danger_max dari 35 menjadi 25)
2. Refresh API
3. Cek apakah adjusted probabilities berubah sesuai threshold baru

## ✅ Keuntungan Pendekatan Ini

1. ✅ **Model tetap digunakan** (tidak perlu retrain)
2. ✅ **Probabilitas menyesuaikan** dengan threshold baru
3. ✅ **Kombinasi ML + threshold validation** (60/40 weight)
4. ✅ **Fleksibel**: bisa ubah weight (60/40, 70/30, dll)
5. ✅ **Safety overrides**: Tetap prioritaskan kondisi kritis (3+ issues → BURUK)

## 📝 Catatan

- **Weight bisa diubah**: Saat ini menggunakan 60% ML + 40% Threshold. Bisa disesuaikan di fungsi `adjustProbabilitiesBasedOnThreshold()`.
- **Threshold kosong**: Jika threshold tidak tersedia, menggunakan ML probabilities saja (100% ML).
- **Original probabilities**: Tetap disimpan di `ml_prediction.probabilities` untuk reference.

## 🚀 Next Steps

1. **Monitor hasil**: Cek apakah adjusted probabilities sesuai dengan ekspektasi
2. **Tune weight**: Jika perlu, sesuaikan weight (60/40) berdasarkan hasil
3. **UI Update**: Pastikan frontend menampilkan adjusted probabilities (bukan original)

**Probability adjustment sudah diimplementasikan!** 🎉

