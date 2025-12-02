# Setup Firebase Authentication untuk Login/Register dengan Google

## Langkah-langkah Setup:

### 1. Buat Project Firebase
1. Buka [Firebase Console](https://console.firebase.google.com/)
2. Klik "Add project" atau pilih project yang sudah ada
3. Ikuti langkah-langkah untuk membuat project baru

### 2. Enable Google Authentication
1. Di Firebase Console, pilih project Anda
2. Buka **Authentication** di menu sebelah kiri
3. Klik **Get started** jika belum diaktifkan
4. Pilih tab **Sign-in method**
5. Klik **Google** dan aktifkan
6. Pilih email support (bisa menggunakan email Anda)
7. Klik **Save**

### 3. Dapatkan Firebase Configuration
1. Di Firebase Console, klik ikon **Settings** (gear) di sebelah "Project Overview"
2. Scroll ke bawah ke bagian "Your apps"
3. Klik ikon **Web** (`</>`) untuk menambahkan web app
4. Isi nama app (contoh: "ChickPatrol")
5. Copy konfigurasi Firebase yang muncul

### 4. Install Firebase via NPM
Firebase sudah diinstall via NPM. Jika perlu reinstall:
```bash
npm install firebase
```

### 5. Setup Environment Variables
Tambahkan konfigurasi Firebase ke file `.env` dengan prefix `VITE_` (untuk Vite):

```env
VITE_FIREBASE_API_KEY=your-api-key-here
VITE_FIREBASE_AUTH_DOMAIN=your-project-id.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your-project-id
VITE_FIREBASE_STORAGE_BUCKET=your-project-id.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=your-sender-id
VITE_FIREBASE_APP_ID=your-app-id
```

**Contoh:**
```env
VITE_FIREBASE_API_KEY=AIzaSyCP4Ro5DqqCPsjhQwoKVBjp_peucxFZmWM
VITE_FIREBASE_AUTH_DOMAIN=apps-768d1.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=apps-768d1
VITE_FIREBASE_STORAGE_BUCKET=apps-768d1.firebasestorage.app
VITE_FIREBASE_MESSAGING_SENDER_ID=993952133870
VITE_FIREBASE_APP_ID=1:993952133870:web:1bbb4a6205b67afb1139ff
VITE_FIREBASE_MEASUREMENT_ID=G-GSQ22C7RL4
```

**Catatan:** Konfigurasi Firebase sudah di-hardcode sebagai fallback di `firebase-config.js`. Untuk production, disarankan menggunakan environment variables.

**Penting:** Gunakan prefix `VITE_` agar variabel bisa diakses oleh Vite di frontend.

### 6. Build Assets
Setelah menambahkan environment variables, jalankan:
```bash
npm run build
```
atau untuk development:
```bash
npm run dev
```

### 7. Authorized Domains
1. Di Firebase Console, buka **Authentication** > **Settings** > **Authorized domains**
2. Pastikan domain Anda sudah ditambahkan:
   - `localhost` (untuk development)
   - Domain production Anda (jika sudah ada)

### 8. Testing
1. Buka halaman login atau register
2. Klik tombol "Masuk dengan Google" atau "Lanjutkan dengan Google"
3. Pilih akun Google Anda
4. Setelah berhasil, Anda akan di-redirect ke halaman home atau dashboard

## Catatan Penting:

- **Security**: Pastikan file `.env` tidak di-commit ke repository
- **HTTPS**: Untuk production, pastikan menggunakan HTTPS
- **OAuth Consent Screen**: Jika menggunakan Google OAuth, pastikan OAuth consent screen sudah dikonfigurasi di Google Cloud Console
- **Vite Environment Variables**: Gunakan prefix `VITE_` untuk semua variabel yang digunakan di frontend
- **Build Required**: Setelah mengubah environment variables, jalankan `npm run build` untuk production atau `npm run dev` untuk development
- **NPM Package**: Firebase diinstall via NPM dan di-bundle dengan Vite untuk optimasi production

## Troubleshooting:

### Error: "Firebase belum diinisialisasi"
- Pastikan semua environment variables sudah diisi dengan benar
- Pastikan Firebase SDK sudah ter-load (cek di browser console)

### Error: "auth/popup-blocked"
- Pastikan popup tidak di-block oleh browser
- Cek pengaturan browser untuk allow popup dari domain Anda

### Error: "auth/unauthorized-domain"
- Pastikan domain Anda sudah ditambahkan di Firebase Console > Authentication > Settings > Authorized domains

