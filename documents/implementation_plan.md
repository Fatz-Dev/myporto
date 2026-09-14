# Rencana Implementasi: Sistem Login Personal (OTP, Biometrik & SSO)

Berdasarkan PRD yang diberikan, sistem login dirancang eksklusif untuk pemilik website (1 pengguna) dengan keamanan ekstra dan kemudahan akses. Sistem akan dibangun menggunakan Laravel 12 sebagai backend dan Vue.js (Inertia) sebagai frontend.

## Status Persetujuan
- **Pilihan Biometrik:** Diputuskan menggunakan **face-api.js (Webcam)**.
- **Fitur Tambahan:** Sistem harus mendukung *toggle On/Off* untuk fitur Face Recognition. Ke depannya fitur ini akan dikontrol melalui Dashboard, namun untuk sekarang akan dipersiapkan menggunakan variabel `.env` atau *flag* di database.
- **Database:** Menggunakan 1 *dummy user* (Pemilik) di tabel `users` untuk mempermudah integrasi dengan sesi bawaan Laravel Auth.

## Proposed Changes

---

### Backend (Laravel)

#### [NEW] `database/seeders/AdminUserSeeder.php`
- Membuat satu-satunya *user* (pemilik web) di dalam *database* beserta kolom penanda (opsional) atau *settings* yang menentukan status `is_face_recognition_enabled`.

#### [NEW] `app/Http/Controllers/Auth/PersonalLoginController.php`
- Method `sendOtp()`: Men-generate 6 digit angka acak, menyimpannya di Cache/Session selama 5 menit, lalu mengirimkan email OTP.
- Method `verifyOtp()`: Memvalidasi kode OTP. Jika fitur *face recognition* dimatikan (Off), pengguna akan langsung di-login-kan ke Dashboard tanpa lanjut ke langkah pemindaian wajah.
- Method `verifyBiometric()`: Memvalidasi hasil dari `face-api.js` (tingkat kecocokan/confidence score) lalu melakukan `Auth::login()`.

#### [NEW] `app/Mail/LoginOtpMail.php`
- Kelas *Mailable* beserta *blade view* untuk mengirimkan pesan kode OTP ke email pemilik.

#### [MODIFY] `routes/web.php`
- Menambahkan *routing* untuk proses login:
  - `GET /login` -> Menampilkan halaman Login Vue.
  - `POST /login/otp/send` -> Kirim OTP.
  - `POST /login/otp/verify` -> Validasi OTP.
  - `POST /login/biometric` -> Validasi Wajah.
  - `GET /auth/google` -> SSO Google.
  - `GET /auth/google/callback` -> SSO Callback.

#### [MODIFY] `composer.json`
- Menambahkan *dependency*:
  - `laravel/socialite` (Untuk SSO Google).
  - *(Opsional)* Package WebAuthn jika memilih metode Passkeys.

---

### Frontend (Vue + Inertia)

#### [MODIFY / NEW] `resources/js/Pages/Auth/Login.vue`
- Merombak halaman login yang ada menjadi alur (*Multi-step UI*):
  1. Tampilan utama berisi tombol "Kirim OTP ke Email" & "Login with Google".
  2. Input 6 kotak untuk memasukkan OTP. Jika fitur Face Recognition statusnya 'Off', alur login langsung selesai di sini.
  3. UI untuk Biometrik: Jika statusnya 'On', layar akan menampilkan antarmuka pemindaian kamera menggunakan `face-api.js` yang akan mencocokkan wajah dari *webcam* dengan foto referensi milik *Admin*.

---

## Environment Setup & Penjelasan

Nantinya, Anda wajib menambahkan dan mengatur beberapa kunci rahasia (*secret keys*) di dalam file `.env` Anda. Saya akan memastikan kode mengambil nilai dari variabel-variabel berikut:

```env
# --- PENGATURAN LOGIN PERSONAL ---
# Satu-satunya email yang diizinkan untuk login
ADMIN_ALLOWED_EMAIL=email_anda@gmail.com

# --- PENGATURAN EMAIL SMTP (Untuk kirim OTP) ---
# Bisa menggunakan Gmail SMTP, Mailtrap, atau layanan email lainnya
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=app_password_gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@portofolio.com"
MAIL_FROM_NAME="${APP_NAME}"

# --- PENGATURAN GOOGLE SSO ---
# Didapatkan dari Google Cloud Console
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# --- PENGATURAN KEAMANAN WAJAH ---
# Toggle yang akan bisa dimatikan via Dashboard di kemudian hari
FACE_RECOGNITION_ENABLED=true
```

## Verification Plan

### Manual Verification
1. Mengakses halaman `/login`.
2. Mencoba "Login with Google" menggunakan email yang benar & email yang salah (harus ditolak jika salah).
3. Menguji alur OTP: Memastikan email terkirim ke *Mailtrap/SMTP*, memasukkan OTP yang benar, lalu beralih ke validasi biometrik.
4. Menguji alur Biometrik: Memastikan kamera aktif (`face-api.js`) atau prompt *Windows Hello* muncul (Passkey), lalu diarahkan masuk ke Dashboard setelah sukses.
