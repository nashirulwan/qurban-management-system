<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('panitia');

$id_pembagian = 0;
if (isset($_GET['id'])) {
    $id_pembagian = intval($_GET['id']);
}

if ($id_pembagian <= 0) {
    // Redirect atau tampilkan error jika ID tidak valid
    header("Location: distribusi_daging.php?error=invalid_id");
    exit();
}

// Ambil data pembagian
$query_pembagian_detail = "SELECT pd.*, h.nomor_hewan, h.jenis_hewan, p.tahun_hijriah, p.tahun_masehi
                           FROM pembagian_daging pd
                           JOIN hewan_qurban h ON pd.id_hewan = h.id_hewan
                           JOIN periode_qurban p ON pd.id_periode = p.id_periode
                           WHERE pd.id_pembagian = $id_pembagian";
$result_pembagian_detail = mysqli_query($conn, $query_pembagian_detail);
$pembagian = mysqli_fetch_assoc($result_pembagian_detail);

if (!$pembagian) {
    // Redirect atau tampilkan error jika data pembagian tidak ditemukan
    header("Location: distribusi_daging.php?error=not_found");
    exit();
}

// Function to format currency (jika belum ada di helper.php)
if (!function_exists('rupiah')) {
    function rupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Distribusi Daging - Panitia</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
</head>
<body>
    <?php include_once '../src/components/header_panitia.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Detail Distribusi Daging</h1>
                <a href="distribusi_daging.php" class="btn btn-outline-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali ke Daftar Distribusi
                </a>
            </div>

            <div class="card animate-on-scroll mb-4">
                <div class="card-header">
                    <h2 class="card-title">Informasi Pembagian</h2>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless info-table">
                        <tbody>
                            <tr>
                                <td width="30%"><strong>Periode Qurban</strong></td>
                                <td width="5%">:</td>
                                <td><?php echo htmlspecialchars($pembagian['tahun_hijriah']); ?>H / <?php echo htmlspecialchars($pembagian['tahun_masehi']); ?>M</td>
                            </tr>
                            <tr>
                                <td><strong>Hewan Qurban</strong></td>
                                <td>:</td>
                                <td><?php echo htmlspecialchars($pembagian['nomor_hewan']); ?> - <?php echo ucfirst(htmlspecialchars($pembagian['jenis_hewan'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Kategori Penerima</strong></td>
                                <td>:</td>
                                <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($pembagian['kategori_penerima']))); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total Berat Daging</strong></td>
                                <td>:</td>
                                <td><?php echo number_format($pembagian['total_berat'], 1); ?> kg</td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah Paket Dibuat</strong></td>
                                <td>:</td>
                                <td><?php echo $pembagian['jumlah_paket']; ?> paket</td>
                            </tr>
                            <tr>
                                <td><strong>Estimasi Berat per Paket</strong></td>
                                <td>:</td>
                                <td><?php echo number_format($pembagian['berat_per_paket'], 2); ?> kg</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Generate</strong></td>
                                <td>:</td>
                                <td><?php echo date('d M Y H:i', strtotime($pembagian['created_at'])); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card animate-on-scroll">
                <div class="card-header">
                    <h2 class="card-title">Detail Paket Distribusi</h2>
                </div>
                <div class="card-body">
                    <?php
                    $query_detail_paket = "SELECT d.*, u.nama_lengkap as nama_penerima, u_penyerah.nama_lengkap as nama_penyerah
                                           FROM distribusi_daging d
                                           JOIN users u ON d.nik_penerima = u.nik
                                           LEFT JOIN users u_penyerah ON d.nik_penyerah = u_penyerah.nik
                                           WHERE d.id_pembagian = $id_pembagian
                                           ORDER BY CAST(SUBSTRING_INDEX(d.nomor_paket, '-', -1) AS UNSIGNED) ASC, d.nomor_paket ASC";
                    $result_detail_paket = mysqli_query($conn, $query_detail_paket);
                    ?>
                    <?php if (mysqli_num_rows($result_detail_paket) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nomor Paket</th>
                                    <th>Nama Penerima</th>
                                    <th class="text-center">Status Pengambilan</th>
                                    <th>Tanggal Ambil</th>
                                    <th>Diserahkan Oleh</th>
                                    </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no_paket = 1;
                                while ($row_paket = mysqli_fetch_assoc($result_detail_paket)):
                                    $status_badge = $row_paket['status_ambil'] == 'sudah_ambil' ? 'badge-success' : 'badge-warning';
                                    $status_text = $row_paket['status_ambil'] == 'sudah_ambil' ? 'Sudah Diambil' : 'Belum Diambil';
                                ?>
                                <tr>
                                    <td><?php echo $no_paket++; ?></td>
                                    <td><?php echo htmlspecialchars($row_paket['nomor_paket']); ?></td>
                                    <td><?php echo htmlspecialchars($row_paket['nama_penerima']); ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $status_badge; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td><?php echo $row_paket['tanggal_ambil'] ? date('d M Y H:i', strtotime($row_paket['tanggal_ambil'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($row_paket['nama_penyerah'] ?? '-'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-info">Belum ada paket yang didistribusikan untuk sesi pembagian ini.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include_once '../src/components/footer.php'; ?>
    <script src="../src/js/main.js"></script>
</body>
</html>