# QA Report

Tanggal pemeriksaan: 2026-08-17

## Status

Aplikasi PHP dapat dijalankan dengan database MariaDB sementara menggunakan konfigurasi environment variable.

## Pemeriksaan yang lulus

- Syntax check seluruh file PHP: lulus.
- Import database public-safe: lulus, terdiri dari 9 tabel dan 2 view.
- Smoke test route terautentikasi: 27 route, 0 runtime error.
- Login dan session role baru: lulus.
- Homepage setelah login: lulus.
- Input transaksi keuangan: lulus.
- Pencatatan pembayaran dengan enum database: lulus.
- Verifikasi pembayaran dan pembuatan transaksi: lulus.
- Pendaftaran peserta qurban: lulus.
- Update status pembayaran peserta: lulus.
- Generate distribusi dan verifikasi QR: lulus.
- Alur aktivasi akun: lulus.
- Docker Compose app + database dan homepage smoke test: lulus.
- Build dan lint scaffold frontend lama sebelum cleanup: lulus.

## Perbaikan yang sudah dilakukan

- Menghapus ketergantungan pada kredensial database yang tertanam di source code.
- Menyatukan pemeriksaan session dengan schema berbasis NIK dan role flags.
- Memperbaiki path file root yang salah.
- Memperbaiki query transaksi dan distribusi yang memakai nama kolom lama.
- Menyesuaikan pilihan pembayaran dengan enum database.
- Menambahkan validasi dasar pembayaran.
- Mengganti data dump lama dengan data demo anonim.
- Menghapus data kontak personal dari footer demo.
- Menambahkan README dan contoh konfigurasi environment.

## Pending sebelum public release

- Riwayat Git awal masih menyimpan versi lama yang pernah berisi kredensial dan data demo. Public release harus memakai riwayat bersih atau repository history baru.
- Scaffold React/Vite yang tidak digunakan sudah dihapus dari release branch dan disimpan di backup lokal terpisah.
- Kredensial demo hanya untuk lokal dan harus diganti atau dihapus sebelum deployment nyata.
- Lisensi repository belum dipilih.
