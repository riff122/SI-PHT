# 🐄 TernakApp — Sistem Informasi Penjualan Hewan Ternak (SI-PHT)

Platform marketplace dan manajemen ternak berbasis web yang menghubungkan peternak lokal secara langsung dengan pembeli tanpa perantara di wilayah Kemalang, Klaten, dan sekitarnya.

---

## 🌟 Fitur Utama

### 🛒 1. Marketplace & Publik
- **Katalog Hewan Ternak**: Menampilkan daftar sapi, kambing, domba, ayam, dan unggas yang tersedia lengkap dengan foto, ras, berat, umur, dan harga.
- **Multi-Filter & Pencarian**: Filter pencarian berdasarkan Dusun/Lokasi, Kategori Hewan, Ras, dan Nama Peternak.
- **Detail Hewan & Galeri**: Pratinjau galeri foto interaktif, informasi spesifikasi ternak, dan profil peternak.
- **Integrasi WhatsApp & GPS**: Tautan langsung pesan WhatsApp ke peternak dengan format otomatis dan navigasi peta Google Maps.

### 👨‍🌾 2. Dashboard Peternak
- **Manajemen Hewan**: Tambah, edit, dan hapus data hewan ternak.
- **Multi-Upload Foto**: Upload 4 hingga 6 foto sekaligus dengan validasi ekstensi aman dan pratinjau instan.
- **Pencatatan Finansial & Pengeluaran**: Catat biaya pakan, vaksin, obat-obatan, dan operasional per ekor hewan.
- **Kalkulator Profit / Loss**: Perhitungan otomatis margin keuntungan/kerugian secara akurat (`Harga Jual - Total Pengeluaran`).
- **Statistik & Grafik Interaktif**: Visualisasi tren penjualan bulanan dan performa finansial menggunakan Chart.js.

### 🛡️ 3. Dashboard Administrator
- **Manajemen Pengguna**: Pantau, aktifkan, nonaktifkan, atau hapus akun pengguna (dengan proteksi anti self-deletion admin).
- **Manajemen Data Ternak**: Monitoring seluruh postingan ternak dari semua peternak terdaftar.
- **Sistem Pengarsipan**: Fitur arsip data hewan yang sudah terjual untuk menjaga kebersihan data aktif serta kemampuan pemulihan (unarchive).

---

## 🔒 Keamanan & Standar Kode

- **Prepared Statements**: Seluruh kueri SQL menggunakan `mysqli_prepare` untuk mencegah celah SQL Injection.
- **Proteksi IDOR**: Validasi hak kepemilikan data (`WHERE id = ? AND user_id = ?`) pada seluruh endpoint manipulasi data peternak.
- **Validasi Unggahan Berkas**: Pemeriksaan ekstensi whitelist (`jpg`, `jpeg`, `png`, `webp`), pengacakan nama file via `bin2hex(random_bytes(4))`, dan pembersihan berkas fisik saat data dihapus.
- **Standar PSR-12**: Seluruh berkas PHP diformat dan tervalidasi menggunakan standar pengodean PSR-12.

---

## 🛠️ Persyaratan Sistem

- **PHP**: Versi 8.0 atau lebih baru (ekstensi `mysqli`, `fileinfo`, `json` aktif).
- **Web Server**: Apache / Nginx (XAMPP, Laragon, atau native).
- **Database**: MySQL 5.7+ / MariaDB 10.4+.
- **Web Browser**: Chrome, Firefox, Edge, atau Safari modern.

---

## 🚀 Panduan Instalasi

### 1. Clone Repositori
Tempatkan folder proyek ke dalam direktori web server Anda (`htdocs` pada XAMPP atau `www` pada Laragon):
```bash
git clone https://github.com/riff122/SI-PHT.git
```

### 2. Import Basis Data
1. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`) atau MySQL CLI.
2. Buat database baru dengan nama `ternakapp`.
3. Import file `database.sql` yang berada di direktori utama proyek.

### 3. Konfigurasi Koneksi Database
Sesuaikan kredensial basis data pada berkas `config/koneksi.php`:
```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ternakapp";
```

### 4. Jalankan Aplikasi
Buka peramban (browser) dan akses:
```
http://localhost/SI-PHT/home.php
```

---

## 📂 Struktur Direktori

```text
SI-PHT/
├── auth/                 # Modul autentikasi (login & register)
│   ├── login.php
│   └── register.php
├── config/               # Konfigurasi aplikasi & database
│   └── koneksi.php
├── dashboard/            # Halaman dashboard Admin & Peternak
│   ├── admin.php         # Dashboard utama Administrator
│   ├── user.php          # Dashboard utama Peternak
│   ├── data_hewan/       # Modul kelola hewan untuk Admin
│   ├── data_user/        # Modul kelola pengguna untuk Admin
│   ├── hewan/            # Modul CRUD hewan untuk Peternak
│   ├── pengeluaran/      # Modul pencatatan pengeluaran
│   ├── keuntungan.php    # Laporan profit/loss peternak
│   ├── lihat_profil.php  # Pratinjau profil peternak
│   ├── logout.php        # Proses logout
│   └── profil.php        # Pengaturan profil pengguna
├── images/               # Direktori aset gambar statis & upload
│   ├── hewan/
│   └── profil/
├── database.sql          # Skema database & tabel relasi
├── detail.php            # Halaman rincian hewan publik
├── home.php              # Halaman beranda & marketplace utama
├── reset.php             # Redirect reset sesi
└── style.css             # Desain antarmuka & stylesheet utama
```

---

## 📄 Lisensi

Proyek ini dikembangkan untuk kebutuhan digitalisasi peternakan lokal Klaten &copy; TernakApp. Seluruh hak cipta dilindungi.
