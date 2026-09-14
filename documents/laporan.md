# Laporan Persiapan & Setup Sistem Login Personal

Sistem login personal (berbasis OTP, Biometrik Wajah `face-api.js`, dan SSO Google) telah selesai diimplementasikan secara struktural di dalam kode Anda. Namun, agar fitur ini dapat berjalan dengan sempurna, ada beberapa langkah penyiapan (*setup*) mandiri yang wajib Anda lakukan.

## 1. Konfigurasi Environment (`.env`)
Buka file `.env` di root project Anda, lalu pastikan variabel-variabel berikut telah terisi dengan benar:

```env
# --- PENGATURAN LOGIN PERSONAL ---
# Email yang menjadi satu-satunya akses untuk login (OTP & SSO)
ADMIN_ALLOWED_EMAIL=admin@example.com

# --- PENGATURAN GOOGLE SSO ---
# Dapatkan kredensial ini dari Google Cloud Console
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# --- PENGATURAN KEAMANAN WAJAH ---
# Set ke 'false' jika sedang tidak ingin menggunakan deteksi wajah
FACE_RECOGNITION_ENABLED=true

# --- PENGATURAN EMAIL SMTP (Untuk OTP) ---
# Contoh jika menggunakan Gmail SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=app_password_gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@portofolio.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 2. Setup Database User (Seeder)
Agar sesi otentikasi (Auth) Laravel dapat berjalan, Anda perlu membuat *dummy user* di dalam database.
Jalankan perintah berikut di terminal:
```bash
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
```
*Catatan: Pastikan database Anda sudah terkonfigurasi di `.env` (misal: MySQL/SQLite).*

## 3. Setup Model `face-api.js`
Fitur pengenalan wajah membutuhkan *pre-trained models* dari `face-api.js` agar bisa beroperasi.
1. Download model dari repositori resmi `face-api.js`: [https://github.com/justadudewhohacks/face-api.js/tree/master/weights](https://github.com/justadudewhohacks/face-api.js/tree/master/weights)
2. Anda wajib mendownload model berikut:
   - `ssd_mobilenetv1_model-weights_manifest.json` & `.shard1` dsb.
   - `face_landmark_68_model-weights_manifest.json` & `.shard1` dsb.
   - `face_recognition_model-weights_manifest.json` & `.shard1` dsb.
3. Buat folder bernama `models` di dalam folder `public/` milik Laravel Anda (`public/models/`).
4. Pindahkan semua file yang didownload tadi ke dalam folder `public/models/`.

## 4. Setup Foto Referensi Wajah
Sistem membutuhkan foto wajah asli Anda (Admin) untuk dicocokkan dengan kamera saat login.
1. Siapkan foto wajah Anda yang menghadap depan dengan pencahayaan yang baik.
2. Ganti nama file tersebut menjadi `admin-face.jpg`.
3. Letakkan file tersebut di dalam folder `public/` (sehingga dapat diakses di `public/admin-face.jpg`).

---

Jika keempat tahap di atas sudah selesai dilakukan, Anda bisa menjalankan *development server* seperti biasa (`php artisan serve` & `npm run dev`) lalu mengakses `/login` untuk menguji cobanya secara langsung!
