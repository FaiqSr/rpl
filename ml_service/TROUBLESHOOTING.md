# 🔧 Troubleshooting ML Service

## ✅ Service Masih Running!

Dari test, service **masih berjalan** dan merespons dengan baik:
- ✅ Port 5000: LISTENING
- ✅ Health endpoint: OK (200)
- ✅ Service aktif dan siap

## 🔍 Jika Masih Error di Browser

### 1. Pastikan URL Benar

**✅ Benar:**
```
http://localhost:5000/health
http://127.0.0.1:5000/health
```

**❌ Salah:**
```
https://localhost:5000/health  (HTTPS tidak didukung)
localhost:5000/health          (tanpa http://)
```

### 2. Test dari Browser

1. Buka browser baru (Chrome/Firefox/Edge)
2. Ketik di address bar: `http://localhost:5000/health`
3. Harus return JSON:
   ```json
   {
     "status": "ok",
     "models_loaded": true
   }
   ```

### 3. Cek Firewall/Antivirus

- Windows Firewall mungkin memblokir port 5000
- Antivirus mungkin memblokir koneksi
- Coba disable sementara untuk test

### 4. Test dari Terminal

```bash
# Test health
curl http://localhost:5000/health

# Test root
curl http://localhost:5000/
```

### 5. Cek Service Status

Double-click: `check_service.bat`

Ini akan:
- Cek apakah port 5000 digunakan
- Test health endpoint
- Test root endpoint

## 🚀 Quick Fix

### Jika Service Tidak Running:

1. **Jalankan service:**
   ```
   Double-click: START_ML_SERVICE.bat
   ```

2. **Biarkan terminal tetap terbuka**

3. **Test di browser:**
   ```
   http://localhost:5000/health
   ```

### Jika Service Running Tapi Browser Error:

1. **Cek URL:** Pastikan menggunakan `http://` (bukan `https://`)
2. **Cek browser:** Coba browser lain atau incognito mode
3. **Cek firewall:** Allow Python/Flask di Windows Firewall
4. **Restart browser:** Close dan buka lagi

## 📊 Status Service

### Cek Service Running:
```bash
netstat -ano | findstr :5000
```

Jika ada output, service running.

### Test Service:
```bash
curl http://localhost:5000/health
```

Harus return JSON dengan status "ok".

## ✅ Checklist

- [ ] Service running (cek dengan `netstat` atau `check_service.bat`)
- [ ] Test health: `http://localhost:5000/health` → OK
- [ ] URL benar: `http://localhost:5000` (bukan https)
- [ ] Browser tidak block (coba incognito)
- [ ] Firewall allow port 5000
- [ ] `.env` Laravel: `ML_SERVICE_URL=http://localhost:5000`

## 🆘 Masih Error?

1. **Restart service:**
   - Stop: Tekan Ctrl+C di terminal service
   - Start: Double-click `START_ML_SERVICE.bat`

2. **Cek log:**
   - Lihat terminal service untuk error message
   - Cek Laravel log: `storage/logs/laravel.log`

3. **Test manual:**
   ```bash
   cd ml_service
   python test_service.py
   ```

**Service sudah running dan OK! Coba refresh browser atau gunakan URL yang benar.** 🚀

