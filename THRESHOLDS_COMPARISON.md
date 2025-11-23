# 📊 Perbandingan Thresholds: Kode vs Standar Boiler (model_metadata.json)

## Thresholds Standar Boiler (dari model_metadata.json)

### 1. Amonia (amonia_ppm)
```json
{
  "ideal_max": 20,
  "warn_max": 35,
  "danger_max": 35
}
```
- **Ideal**: ≤ 20 ppm
- **Warning**: > 35 ppm
- **Danger**: > 35 ppm

### 2. Suhu (suhu_c)
```json
{
  "ideal_min": 23,
  "ideal_max": 34,
  "danger_low": 23,
  "danger_high": 34
}
```
- **Ideal**: 23-34 °C
- **Danger Low**: < 23 °C
- **Danger High**: > 34 °C

### 3. Kelembaban (kelembaban_rh)
```json
{
  "ideal_min": 50,
  "ideal_max": 70,
  "warn_high": 80,
  "danger_high": 80
}
```
- **Ideal**: 50-70%
- **Warning High**: > 80%
- **Danger High**: > 80%

### 4. Cahaya (cahaya_lux)
```json
{
  "ideal_low": 20,
  "ideal_high": 40,
  "warn_low": 10,
  "warn_high": 60
}
```
- **Ideal**: 20-40 lux
- **Warning Low**: < 10 lux
- **Warning High**: > 60 lux

## Thresholds yang Digunakan di Kode (Sebelum Perbaikan)

### 1. Amonia (amonia_ppm)
```python
{
  'ideal_max': 20,      # ✅ Sama
  'warn_max': 25,       # ❌ Berbeda (metadata: 35)
  'danger_max': 35      # ✅ Sama
}
```

### 2. Suhu (suhu_c)
```python
{
  'ideal_min': 23,      # ✅ Sama
  'ideal_max': 34,      # ✅ Sama
  'danger_low': 20,     # ❌ Berbeda (metadata: 23)
  'danger_high': 35     # ❌ Berbeda (metadata: 34)
}
```

### 3. Kelembaban (kelembaban_rh)
```python
{
  'ideal_min': 50,      # ✅ Sama
  'ideal_max': 70,      # ✅ Sama
  'warn_high': 75,      # ❌ Berbeda (metadata: 80)
  'danger_high': 80     # ✅ Sama
}
```

### 4. Cahaya (cahaya_lux)
```python
{
  'ideal_low': 20,      # ✅ Sama
  'ideal_high': 40,     # ✅ Sama
  'warn_low': 10,       # ✅ Sama
  'warn_high': 60       # ✅ Sama
}
```

## ✅ Perbaikan yang Dilakukan

1. **Update `ml_service/app.py`**: Thresholds sekarang diambil langsung dari `model_metadata.json` untuk konsistensi
2. **Perbaiki Warna Card**: "di luar batas aman" sekarang menggunakan warna kuning (warning), bukan hijau

## 📋 Ringkasan Thresholds (Setelah Perbaikan)

| Parameter | Ideal Range | Warning Range | Danger Range |
|-----------|-------------|---------------|--------------|
| **Amonia** | ≤ 20 ppm | > 35 ppm | > 35 ppm |
| **Suhu** | 23-34 °C | < 23 atau > 34 °C | < 23 atau > 34 °C |
| **Kelembaban** | 50-70% | > 80% | > 80% |
| **Cahaya** | 20-40 lux | < 10 atau > 60 lux | < 10 atau > 60 lux |

## ⚠️ Catatan Penting

1. **Cahaya**: Nilai aktual dalam ratusan (308.8-369.4 lux), tapi threshold tetap 10-60 lux sesuai aturan boiler
2. **Perbandingan**: Nilai ratusan langsung dibandingkan dengan threshold 10-60 (tanpa konversi)
3. **Status**: Jika nilai > 60 lux, status = "di luar batas aman" (kuning/warning)

