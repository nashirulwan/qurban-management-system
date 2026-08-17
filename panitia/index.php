<?php

require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('panitia');

$periode_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM periode_qurban WHERE is_active = TRUE LIMIT 1"));
$id_periode_aktif = $periode_data['id_periode'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Panitia</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
    <style>.display-4, .display-6 { font-family: 'Poppins', sans-serif; font-weight: 600; } .display-4 { font-size: 2.5rem; } .display-6 { font-size: 1.75rem; }</style>
</head>
<body>
    <?php include_once '../src/components/header_panitia.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Dashboard Panitia</h1>
                <?php if ($periode_data): ?>
                    <span class="badge badge-primary">Periode Aktif: <?php echo htmlspecialchars($periode_data['tahun_hijriah']); ?>H</span>
                <?php endif; ?>
            </div>

            <div class="alert alert-info">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>!</div>

            <div class="card animate-on-scroll">
                <div class="card-header"><h2 class="card-title">Statistik Periode Ini</h2></div>
                <div class="card-body">
                <?php if ($id_periode_aktif): ?>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card shadow-sm h-100 text-center">
                                <div class="card-body">
                                    <h4 class="text-muted mb-3">Verifikasi Iuran</h4>
                                    <?php
                                    $pending_iuran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran_iuran WHERE id_periode = $id_periode_aktif AND status_verifikasi = 'pending'"))['total'];
                                    ?>
                                    <p class="display-4 fw-bold text-warning mb-0"><?php echo $pending_iuran; ?></p>
                                    <small class="d-block text-muted">Pembayaran Pending</small>
                                </div>
                            </div>
                        </div>
                         <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card shadow-sm h-100 text-center">
                                <div class="card-body">
                                    <h4 class="text-muted mb-3">Peserta Qurban</h4>
                                    <?php
                                    $query_peserta = "SELECT status_bayar, COUNT(*) as jumlah FROM peserta_qurban WHERE id_periode = $id_periode_aktif GROUP BY status_bayar";
                                    $result_peserta = mysqli_query($conn, $query_peserta);
                                    $status_peserta = ['lunas' => 0, 'dp' => 0, 'belum_bayar' => 0];
                                    while($row = mysqli_fetch_assoc($result_peserta)) {
                                        $status_peserta[$row['status_bayar']] = $row['jumlah'];
                                    }
                                    $total_peserta = array_sum($status_peserta);
                                    ?>
                                    <p class="display-4 fw-bold text-info mb-0"><?php echo $total_peserta; ?></p>
                                    <small class="d-block text-muted">Total Pendaftar</small>
                                    <div class="mt-2 small">
                                        <span class="badge bg-success me-1">Lunas: <?php echo $status_peserta['lunas']; ?></span>
                                        <span class="badge bg-primary me-1">DP: <?php echo $status_peserta['dp']; ?></span>
                                        <span class="badge bg-danger">Belum: <?php echo $status_peserta['belum_bayar']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                             <div class="card shadow-sm h-100 text-center">
                                <div class="card-body">
                                    <h4 class="text-muted mb-3">Distribusi Daging</h4>
                                    <?php
                                    $distribusi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(CASE WHEN status_ambil = 'sudah_ambil' THEN 1 END) as sudah, COUNT(*) as total FROM distribusi_daging WHERE id_periode = $id_periode_aktif"));
                                    ?>
                                    <p class="display-4 fw-bold text-success mb-0"><?php echo ($distribusi['sudah'] ?? 0) . '/' . ($distribusi['total'] ?? 0); ?></p>
                                    <small class="d-block text-muted">Diambil / Total Paket</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                             <div class="card shadow-sm h-100 text-center">
                                <div class="card-body">
                                    <h4 class="text-muted mb-3">Saldo Periode</h4>
                                    <?php
                                    $saldo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(CASE WHEN jenis_transaksi = 'masuk' THEN nominal ELSE -nominal END) as saldo FROM transaksi_keuangan WHERE id_periode = $id_periode_aktif"))['saldo'];
                                    ?>
                                    <p class="display-6 fw-bold text-primary mb-0"><?php echo rupiah($saldo ?? 0); ?></p>
                                    <a href="laporan_keuangan.php" class="btn btn-sm btn-outline-primary mt-2">Lihat Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">Belum ada periode qurban aktif. Statistik tidak tersedia.</div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
    <script src="../src/js/main.js"></script>
</body>
</html>
<?php