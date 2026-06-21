# GARAGEZKA - Sistem Booking Servis Motor

Website booking servis motor berbasis PHP dengan desain dark mode sesuai Figma.

## 🚀 Cara Setup

### Prasyarat
- XAMPP / Laragon / WAMP (PHP 8.0+ dan MySQL)
- Browser modern

### Langkah Instalasi

1. **Clone/Copy ke folder htdocs**
   ```
   Salin folder "App Garagezka" ke:
   - XAMPP: C:/xampp/htdocs/
   - Laragon: C:/laragon/www/
   ```

2. **Buat database**
   - Buka phpMyAdmin: http://localhost/phpmyadmin
   - Klik "Import" dan upload file `database/garagezka.sql`
   - Atau jalankan SQL query dari file tersebut

3. **Konfigurasi database**
   - Edit file `config/database.php`
   - Sesuaikan `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`

4. **Akses website**
   ```
   http://localhost/App%20Garagezka/
   ```

## 👤 Akun Demo

| Role  | Email                 | Password |
|-------|-----------------------|----------|
| Admin | admin@garagezka.com   | password |
| User  | azka@email.com        | password |

> **Catatan:** Password di database adalah hash dari "password" (default Laravel hash). Ganti dengan hash yang benar menggunakan script berikut.

### Reset Password Admin
Buat file `setup_password.php` di root folder:
```php
<?php
require_once 'config/database.php';
$conn = getConnection();
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$hash' WHERE email = 'admin@garagezka.com'");
$hash2 = password_hash('user123', PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$hash2' WHERE email = 'azka@email.com'");
echo "Password berhasil direset! Admin: admin123, User: user123";
?>
```
Akses: `http://localhost/App%20Garagezka/setup_password.php`
Lalu hapus file tersebut.

## 📁 Struktur File

```
App Garagezka/
├── config/
│   └── database.php        # Konfigurasi database
├── includes/
│   └── helpers.php         # Helper functions (navbar, sidebar, dll)
├── assets/
│   └── css/
│       └── style.css       # Global stylesheet
├── database/
│   └── garagezka.sql       # Schema database
├── index.php               # Beranda
├── tentang.php             # Tentang Kami
├── layanan.php             # Layanan
├── kontak.php              # Kontak
├── login.php               # Login
├── daftar.php              # Daftar akun
├── lupa-password.php       # Lupa password
├── logout.php              # Logout
├── dashboard.php           # Dashboard user
├── kendaraan.php           # Manajemen kendaraan
├── booking.php             # Booking servis
├── booking-konfirmasi.php  # Konfirmasi booking
├── riwayat.php             # Riwayat servis
├── notifikasi.php          # Notifikasi
├── profil.php              # Profil user
└── .htaccess               # Apache config
```

## 🎨 Design System

- **Background**: #0a0a0a (gelap)
- **Card**: #161616
- **Accent**: #e53535 (merah)
- **Font**: Inter (Google Fonts)

## ✨ Fitur

### Public
- ✅ Beranda dengan hero section dan fitur
- ✅ Tentang Kami dengan info bengkel
- ✅ Halaman Layanan dengan harga
- ✅ Form Kontak

### Autentikasi
- ✅ Login (email/no HP + password)
- ✅ Registrasi dengan validasi
- ✅ Lupa Password
- ✅ Session management

### Dashboard User
- ✅ Dashboard dengan statistik
- ✅ Manajemen Kendaraan (CRUD)
- ✅ Booking Servis (pilih kendaraan, layanan, jadwal)
- ✅ Konfirmasi Booking
- ✅ Riwayat Servis
- ✅ Notifikasi
- ✅ Edit Profil & Ganti Password
