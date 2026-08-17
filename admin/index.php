<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('admin');

// Ambil data statistik total
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$keuangan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(CASE WHEN jenis_transaksi = 'masuk' THEN nominal ELSE 0 END) as total_masuk, SUM(CASE WHEN jenis_transaksi = 'keluar' THEN nominal ELSE 0 END) as total_keluar FROM transaksi_keuangan"));
$saldo_keuangan = ($keuangan['total_masuk'] ?? 0) - ($keuangan['total_keluar'] ?? 0);

// Ambil periode aktif
$periode_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT tahun_hijriah, tahun_masehi FROM periode_qurban WHERE is_active = TRUE LIMIT 1"));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Admin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
</head>
<body>
    <?php include_once '../src/components/header_admin.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Dashboard Admin</h1>
                <?php if ($periode_aktif): ?>
                    <span class="badge badge-primary">Periode Aktif: <?php echo htmlspecialchars($periode_aktif['tahun_hijriah']); ?>H / <?php echo htmlspecialchars($periode_aktif['tahun_masehi']); ?>M</span>
                <?php endif; ?>
            </div>

            <div class="alert alert-info">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>!</div>

            <div class="card animate-on-scroll">
                <div class="card-header"><h2 class="card-title">Statistik Sistem Keseluruhan</h2></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card shadow-sm h-100 text-center">
                                <div class="card-body">
                                    <h4 class="text-muted mb-3">Total Pengguna</h4>
                                    <p class="display-4 fw-bold text-primary mb-0"><?php echo $total_user; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card shadow-sm h-100 text-center">
                                <div class="card-body">
                                    <h4 class="text-muted mb-3">Total Pemasukan</h4>
                                    <p class="display-6 fw-bold text-success mb-0"><?php echo rupiah($keuangan['total_masuk'] ?? 0); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card shadow-sm h-100 text-center">
                                <div class="card-body">
                                    <h4 class="text-muted mb-3">Total Pengeluaran</h4>
                                    <p class="display-6 fw-bold text-danger mb-0"><?php echo rupiah($keuangan['total_keluar'] ?? 0); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card shadow-sm h-100 text-center">
                                <div class="card-body">
                                    <h4 class="text-muted mb-3">Saldo Akhir</h4>
                                    <p class="display-6 fw-bold mb-0"><?php echo rupiah($saldo_keuangan); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
    <script src="../src/js/main.js"></script>
</body>
</html>