# Setup Data Sensor Real-time

Dokumentasi untuk setup data sensor yang disimpan di MySQL dengan variasi random yang realistic sesuai threshold boiler.

## Struktur Database

### Tabel: `sensor_readings`

- `id` - Primary key (auto increment)
- `amonia_ppm` - Ammonia dalam ppm (decimal 5,2)
- `suhu_c` - Suhu dalam Celsius (decimal 4,1)
- `kelembaban_rh` - Kelembaban dalam persen (decimal 5,1)
- `cahaya_lux` - Cahaya dalam lux (decimal 6,1)
- `recorded_at` - Waktu pencatatan sensor (timestamp)
- `created_at`, `updated_at` - Timestamps

## Setup Awal

### 1. Run Migration

```bash
php artisan migrate
```

### 2. Generate Data Awal (30 data terakhir)

```bash
php artisan db:seed --class=SensorReadingSeeder
```

Seeder ini akan generate 30 data dengan distribusi status merata:
- **33% BAIK** - Semua sensor dalam range ideal
- **33% PERHATIAN** - 1-2 sensor di luar range ideal tapi belum danger
- **33% BURUK** - Ada sensor di range danger

## Generate Data Real-time

### Manual (Testing)

```bash
php artisan sensor:generate
```

Command ini akan:
- Generate 1 data sensor untuk jam saat ini
- Cek apakah data untuk jam ini sudah ada (skip jika sudah ada)
- Generate data dengan variasi random yang realistic sesuai threshold boiler

### Otomatis (Scheduler)

Data akan otomatis di-generate setiap jam melalui Laravel Scheduler.

#### Untuk Development (Windows)

Jalankan scheduler worker:

```bash
php artisan schedule:work
```

#### Untuk Production (Linux/Mac)

Tambahkan cron job:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Threshold Boiler Standards

Data di-generate sesuai dengan threshold boiler standards:

### Amonia
- **Ideal**: 5-20 ppm
- **Warning**: 21-35 ppm
- **Danger**: >35 ppm

### Suhu
- **Ideal**: 23-34°C
- **Warning**: 20-23°C atau 35-37°C
- **Danger**: <20°C atau >37°C

### Kelembaban
- **Ideal**: 50-70%
- **Warning**: 40-50% atau 71-80%
- **Danger**: <40% atau >80%

### Cahaya
- **Ideal**: 10-60 lux (sesuai threshold boiler)
- **Warning**: 5-10 lux atau 61-70 lux (sedikit keluar threshold, tidak ekstrem)
- **Danger**: 1-5 lux atau 71-85 lux (keluar threshold tapi tidak terlalu ekstrem)
- **Note**: Nilai random berada di rentang threshold (10-60 lux), jika keluar threshold tidak terlalu besar

## Distribusi Status

Model ML akan menghasilkan distribusi status yang merata:
- **BAIK**: ~33% dari data
- **PERHATIAN**: ~33% dari data
- **BURUK**: ~33% dari data

Ini memastikan model ML dapat menghasilkan output status yang bervariasi dan realistis.

## API Endpoint

Data sensor digunakan oleh endpoint:

```
GET /api/monitoring/tools
```

Endpoint ini akan:
1. Ambil 30 data terakhir dari database `sensor_readings`
2. Jika kurang dari 30, generate data default untuk melengkapi
3. Kirim ke ML Service untuk prediksi dan klasifikasi
4. Return hasil monitoring dengan prediksi 6 jam dan 24 jam

## Troubleshooting

### Data tidak ter-generate otomatis

1. Pastikan scheduler worker berjalan:
   ```bash
   php artisan schedule:work
   ```

2. Cek log untuk error:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Data tidak muncul di dashboard

1. Pastikan seeder sudah dijalankan:
   ```bash
   php artisan db:seed --class=SensorReadingSeeder
   ```

2. Cek apakah data ada di database:
   ```bash
   php artisan tinker
   >>> \App\Models\SensorReading::count()
   ```

3. Pastikan route `/api/monitoring/tools` mengakses database dengan benar

### Status selalu sama

Jika status selalu sama (misalnya selalu "PERHATIAN"), pastikan:
1. Data di-generate dengan variasi yang cukup
2. ML Service berjalan dan dapat mengakses model
3. Threshold sudah sesuai dengan data yang di-generate

## Catatan

- Data di-generate dengan variasi random yang realistic, tidak terlalu ekstrem
- Nilai cahaya dalam ratusan (sesuai dataset training), tapi threshold tetap 10-60 lux
- Data setiap jam sekali (real-time)
- Scheduler akan skip jika data untuk jam tersebut sudah ada

