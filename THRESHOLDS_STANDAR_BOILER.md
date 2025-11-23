# 📊 Thresholds Standar Boiler (dari model_metadata.json)

## Thresholds yang Digunakan di Sistem

### 1. Amonia (amonia_ppm)
- **Ideal Max**: ≤ 20 ppm
- **Warning Max**: > 35 ppm
- **Danger Max**: > 35 ppm

### 2. Suhu (suhu_c)
- **Ideal Min**: 23 °C
- **Ideal Max**: 34 °C
- **Danger Low**: < 23 °C
- **Danger High**: > 34 °C

### 3. Kelembaban (kelembaban_rh)
- **Ideal Min**: 50%
- **Ideal Max**: 70%
- **Warning High**: > 80%
- **Danger High**: > 80%

### 4. Cahaya (cahaya_lux)
- **Ideal Low**: 20 lux
- **Ideal High**: 40 lux
- **Warning Low**: < 10 lux
- **Warning High**: > 60 lux

## ⚠️ Catatan Penting untuk Cahaya

1. **Nilai Aktual**: Data aktual dalam ratusan (308.8-369.4 lux)
2. **Threshold**: Tetap 10-60 lux sesuai aturan boiler (TIDAK dikonversi)
3. **Pengecekan**: Nilai ratusan langsung dibandingkan dengan threshold 10-60
4. **Status**:
   - Jika nilai > 60 lux → "di luar batas aman" (kuning/warning)
   - Jika nilai 20-40 lux → "dalam kisaran aman" (hijau/ok)
   - Jika nilai 10-60 lux tapi di luar 20-40 → "potensi keluar batas aman" (kuning/warning)

## 📋 Mapping Status & Warna

| Status | Warna | Kondisi |
|--------|-------|---------|
| **dalam kisaran aman** | Hijau (#28a745) | Semua nilai dalam ideal range |
| **potensi keluar batas aman** | Kuning (#ffc107) | Nilai dalam warn range tapi di luar ideal |
| **di luar batas aman** | Kuning (#ffc107) | Nilai di luar warn range |
| **kritik/bahaya** | Merah (#dc3545) | Nilai di luar danger range |

## ✅ Verifikasi

Thresholds sekarang diambil langsung dari `model_metadata.json` untuk memastikan konsistensi dengan standar boiler.

