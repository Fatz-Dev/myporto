# Product Requirements Document (PRD): Sistem Login Personal

## 1. Pendahuluan
Dokumen ini menguraikan kebutuhan fungsional dan alur kerja (workflow) untuk sistem login pada platform portofolio/blog ini. Mengingat sistem ini ditujukan hanya untuk **satu pengguna utama (pemilik website)**, alur otentikasi didesain dengan tingkat keamanan tinggi (Multi-Factor Authentication & Biometrics) sekaligus memberikan kemudahan akses via SSO (Single Sign-On).

## 2. Fitur Utama
1. **Otentikasi 2-Langkah Utama (OTP + Biometrics):** Login dengan keamanan ekstra berbasis OTP ke email spesifik dan pemindaian wajah.
2. **SSO Google (Bypass):** Alternatif login cepat menggunakan akun Google yang sudah terdaftar.

---

## 3. Alur Pengguna (User Flows)

### 3.1. Alur Login Utama (OTP + Face Biometrics)
1. **Masuk Halaman Login:** Pengguna mengakses halaman login utama.
2. **Kirim OTP:** Sistem otomatis / melalui tombol akan mengirimkan kode OTP ke satu-satunya email yang telah diatur (hardcoded di `.env`).
3. **Validasi OTP:** Pengguna memasukkan kode OTP. 
4. **Scan Biometrik Wajah:** Jika OTP benar, antarmuka akan memicu kamera atau FaceID/Windows Hello untuk verifikasi wajah pengguna (Face Biometrics).
5. **Akses Dashboard:** Setelah wajah tervalidasi, sesi pengguna dibuat dan pengguna diarahkan ke halaman Dashboard.

### 3.2. Alur Login Google (Google Auth)
1. **Masuk Halaman Login:** Pengguna memilih opsi "Login with Google".
2. **Otorisasi Google:** Pengguna memilih akun Google mereka di popup Google.
3. **Pengecekan Kredensial:** Sistem mencocokkan email Google tersebut dengan daftar email yang diizinkan (berada di `.env`).
4. **Akses Dashboard:** Jika cocok, pengguna langsung masuk ke Dashboard tanpa perlu verifikasi OTP maupun Biometrik (Bypass).

---

## 4. Persyaratan Teknis
- **Email OTP:** Membutuhkan integrasi SMTP Mailer (contoh: Mailtrap, Brevo, atau Gmail SMTP).
- **Face Biometrics:** Direkomendasikan menggunakan standar **WebAuthn (Passkeys)** untuk memanfaatkan FaceID/Windows Hello yang jauh lebih aman, ATAU menggunakan pustaka pihak ketiga seperti `face-api.js` untuk pengenalan wajah via webcam.
- **Google Auth:** Menggunakan modul pihak ketiga seperti `Laravel Socialite` untuk menangani proses OAuth 2.0.
- **Environment Variables:** Semua pengaturan rahasia (Daftar Akun Google, Email Tujuan OTP) harus diatur di dalam file `.env`.

