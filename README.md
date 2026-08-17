# Sistem Manajemen Qurban

Aplikasi web untuk membantu pengelolaan kegiatan qurban: periode, data hewan, peserta, pembayaran, transaksi keuangan, distribusi daging, dan verifikasi pengambilan paket.

## Fitur utama

- Login dengan NIK atau username.
- Role admin, panitia, warga, dan peserta qurban.
- Pengelolaan periode dan hewan qurban.
- Pendaftaran peserta serta pencatatan pembayaran.
- Verifikasi pembayaran oleh panitia.
- Pencatatan pemasukan dan pengeluaran.
- Generate paket distribusi dan verifikasi QR code.
- Dashboard dan laporan keuangan.

## Teknologi

- PHP 8.2+
- MySQL atau MariaDB
- HTML, CSS, dan JavaScript

## Menjalankan secara lokal

### Opsi A: Docker Compose

Cara paling mudah untuk mencoba aplikasi:

```bash
docker compose up --build
```

Jika port default sedang dipakai aplikasi lain, gunakan port alternatif:

```bash
APP_PORT=18080 docker compose up --build
```

Buka `http://localhost:8000` atau port alternatif yang dipilih. Untuk menghapus database demo dan mengulang dari awal:

```bash
docker compose down -v
```

### Opsi B: PHP dan database lokal

#### 1. Siapkan database

Buat database kosong, lalu import schema dan data demo:

```bash
mysql -u root -p -e "CREATE DATABASE db_qurban CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p db_qurban < db_qurban.sql
```

#### 2. Atur koneksi database

Aplikasi membaca konfigurasi dari environment variable. Contoh tersedia di [`.env.example`](.env.example):

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_DATABASE=db_qurban
export DB_USERNAME=qurban
export DB_PASSWORD='ganti-dengan-password-lokal'
```

Jangan commit file `.env` atau kredensial database.

#### 3. Jalankan server PHP

Dari root project:

```bash
php -S 127.0.0.1:8000 -t .
```

Buka `http://127.0.0.1:8000`.

## Akun demo lokal

Data demo di `db_qurban.sql` hanya untuk pengujian lokal:

| Username | Password | Role |
| --- | --- | --- |
| `demo_admin` | `demo-password` | Semua role |
| `demo_warga` | `demo-password` | Warga |

Ganti atau hapus akun demo sebelum deployment nyata.

## Catatan keamanan

- Kredensial database tidak disimpan di source code.
- Data demo tidak menggunakan data warga asli.
- Akun demo tidak boleh digunakan pada server produksi.
- QR code pada versi ini menggunakan layanan generator QR eksternal.
