# Ruang Rapat - Sistem Manajemen Reservasi Ruang Rapat

## Deskripsi

Sistem manajemen reservasi ruang rapat yang dibangun menggunakan PHP dan MySQL. Sistem ini menyediakan fitur untuk pengguna dan admin dalam mengelola pemesanan ruang rapat secara efisien melalui antarmuka web.

## Fitur Utama

- Registrasi dan autentikasi pengguna
- Manajemen ruang rapat oleh admin
- Pemesanan ruang rapat oleh pengguna
- Riwayat pemesanan untuk pengguna dan admin
- Export data dan cetak laporan dalam format PDF dan Excel
- Sistem notifikasi dan status pemesanan
- Panel admin dan panel pengguna dengan navigasi yang berbeda

## Struktur Direktori

```
ruang-rapat/
c:/xampp/htdocs/ruangrapat/
├── components/         # Komponen UI seperti Navbar dan Sidebar untuk admin dan user
├── config/             # Konfigurasi koneksi database dan pengaturan sistem
├── styles/             # File CSS dan styling khusus (global.css, excel_styles.php)
├── views/              # Halaman tampilan untuk admin dan user
│   ├── admin/          # Halaman khusus admin (manajemen user, ruang, laporan, dll)
│   ├── user/           # Halaman khusus pengguna (pemesanan, riwayat, dll)
│   └── register.php    # Halaman registrasi pengguna
├── index.php           # Halaman utama / entry point aplikasi
├── logout.php          # Script logout pengguna
└── composer.json       # Dependensi PHP (jika ada)
```

## Teknologi yang Digunakan

- PHP 8.1.25
- MySQL
- HTML5, CSS3
- JavaScript
- Tailwind CSS
- Library tambahan untuk export Excel dan PDF

## Instalasi

1. Pastikan Anda memiliki web server dengan PHP dan MySQL (misal XAMPP, Laragon)
2. Clone atau salin folder proyek ke direktori web server Anda (misal `htdocs/ruangrapat`)
3. Import database yang sesuai ke MySQL Anda
4. Konfigurasi koneksi database di file `config/koneksi.php`
5. Jalankan server web Anda (Apache)
6. Akses aplikasi melalui browser di alamat `http://localhost/ruangrapat` atau sesuai konfigurasi Anda

## Penggunaan

- Gunakan halaman registrasi untuk membuat akun pengguna baru
- Login sebagai pengguna atau admin untuk mengakses fitur sesuai peran
- Admin dapat mengelola data ruang rapat, pengguna, dan melihat laporan
- Pengguna dapat melakukan pemesanan ruang dan melihat riwayat pemesanan

## Lisensi

Hak Cipta © Bambang Harsono. Semua hak dilindungi.
# ruanglestari-lhkabtegal
