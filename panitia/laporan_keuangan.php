<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('panitia');

// Ambil periode yang dipilih atau periode aktif jika tidak ada yang dipilih
$periode_id_selected = isset($_GET['periode']) ? intval($_GET['periode']) : 0;

if ($periode_id_selected == 0) {
    $query_periode_aktif = "SELECT id_periode FROM periode_qurban WHERE is_active = TRUE LIMIT 1";
    $result_periode_aktif = mysqli_query($conn, $query_periode_aktif);
    if ($periode_aktif_data = mysqli_fetch_assoc($result_periode_aktif)) {
        $periode_id_selected = $periode_aktif_data['id_periode'];
    }
}

// Ambil detail periode yang dipilih (jika ada)
$periode_info = null;
if ($periode_id_selected > 0) {
    $query_periode_info = "SELECT * FROM periode_qurban WHERE id_periode = $periode_id_selected";
    $result_periode_info = mysqli_query($conn, $query_periode_info);
    $periode_info = mysqli_fetch_assoc($result_periode_info);
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
    <title>Laporan Keuangan - Panitia</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
    <style>
        @media print {
            body {
                font-size: 10pt;
            }
            .no-print, .main-header, .main-footer, .btn-outline-primary, .form-select-periode {
                display: none !important;
            }
            .main-content {
                padding: 0 !important;
            }
            .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
                margin-bottom: 1rem !important;
            }
            .card-header {
                background-color: #f8f9fa !important;
                padding: 0.5rem 1rem !important;
            }
            .card-title {
                font-size: 14pt !important;
            }
            .table {
                font-size: 9pt !important;
            }
            .table th, .table td {
                padding: 0.3rem 0.5rem !important;
            }
            .laporan-title {
                text-align: center;
                margin-bottom: 1.5rem;
            }
            .laporan-title h1 {
                font-size: 16pt !important;
                margin-bottom: 0.25rem;
            }
             .laporan-title p {
                font-size: 11pt !important;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include_once '../src/components/header_panitia.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h1>Laporan Keuangan Qurban</h1>
                <div>
                    <a href="index.php" class="btn btn-outline-primary me-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        Kembali ke Dashboard
                    </a>
                    <button class="btn btn-info" onclick="window.print()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        Cetak Laporan
                    </button>
                </div>
            </div>

            <div class="card animate-on-scroll mb-4 form-select-periode no-print">
                <div class="card-body">
                    <form method="GET" class="d-flex align-items-center">
                        <label for="periode" class="form-label me-2 mb-0 fw-bold">Pilih Periode Laporan:</label>
                        <select name="periode" id="periode" class="form-select w-auto" onchange="this.form.submit()">
                            <option value="0">-- Pilih Periode --</option>
                            <?php
                            $query_list_periode = "SELECT * FROM periode_qurban ORDER BY tahun_masehi DESC";
                            $result_list_periode = mysqli_query($conn, $query_list_periode);
                            while ($row_p = mysqli_fetch_assoc($result_list_periode)):
                            ?>
                            <option value="<?php echo $row_p['id_periode']; ?>"
                                    <?php echo $row_p['id_periode'] == $periode_id_selected ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row_p['tahun_hijriah']); ?> H / <?php echo htmlspecialchars($row_p['tahun_masehi']); ?> M
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>
            </div>

            <?php if ($periode_id_selected > 0 && $periode_info): ?>
                <div class="laporan-title">
                    <h1>LAPORAN KEUANGAN QURBAN</h1>
                    <p>Periode: <strong><?php echo htmlspecialchars($periode_info['tahun_hijriah']); ?> H / <?php echo htmlspecialchars($periode_info['tahun_masehi']); ?> M</strong></p>
                </div>

                <div class="card animate-on-scroll mb-4">
                    <div class="card-header">
                        <h3 class="card-title">A. PEMASUKAN</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $query_pemasukan = "SELECT kategori, SUM(nominal) as total
                                          FROM transaksi_keuangan
                                          WHERE id_periode = $periode_id_selected AND jenis_transaksi = 'masuk'
                                          GROUP BY kategori ORDER BY kategori";
                        $result_pemasukan = mysqli_query($conn, $query_pemasukan);
                        $total_masuk = 0;
                        ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Kategori Pemasukan</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no_masuk = 1;
                                    if (mysqli_num_rows($result_pemasukan) > 0) {
                                        while ($row = mysqli_fetch_assoc($result_pemasukan)):
                                            $total_masuk += $row['total'];
                                    ?>
                                    <tr>
                                        <td><?php echo $no_masuk++; ?>.</td>
                                        <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($row['kategori']))); ?></td>
                                        <td class="text-end"><?php echo rupiah($row['total']); ?></td>
                                    </tr>
                                    <?php
                                        endwhile;
                                    } else {
                                        echo '<tr><td colspan="3" class="text-center">Tidak ada data pemasukan.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold table-group-divider">
                                        <td colspan="2" class="text-uppercase">TOTAL PEMASUKAN</td>
                                        <td class="text-end"><?php echo rupiah($total_masuk); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card animate-on-scroll mb-4">
                    <div class="card-header">
                        <h3 class="card-title">B. PENGELUARAN</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $query_pengeluaran = "SELECT kategori, SUM(nominal) as total
                                            FROM transaksi_keuangan
                                            WHERE id_periode = $periode_id_selected AND jenis_transaksi = 'keluar'
                                            GROUP BY kategori ORDER BY kategori";
                        $result_pengeluaran = mysqli_query($conn, $query_pengeluaran);
                        $total_keluar = 0;
                        ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Kategori Pengeluaran</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no_keluar = 1;
                                    if (mysqli_num_rows($result_pengeluaran) > 0) {
                                        while ($row = mysqli_fetch_assoc($result_pengeluaran)):
                                            $total_keluar += $row['total'];
                                    ?>
                                    <tr>
                                        <td><?php echo $no_keluar++; ?>.</td>
                                        <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($row['kategori']))); ?></td>
                                        <td class="text-end"><?php echo rupiah($row['total']); ?></td>
                                    </tr>
                                    <?php
                                        endwhile;
                                    } else {
                                        echo '<tr><td colspan="3" class="text-center">Tidak ada data pengeluaran.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold table-group-divider">
                                        <td colspan="2" class="text-uppercase">TOTAL PENGELUARAN</td>
                                        <td class="text-end"><?php echo rupiah($total_keluar); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card animate-on-scroll mb-4">
                    <div class="card-header">
                        <h3 class="card-title">C. SALDO AKHIR</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td width="200">Total Pemasukan</td>
                                        <td class="text-end fw-bold text-success"><?php echo rupiah($total_masuk); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Total Pengeluaran</td>
                                        <td class="text-end fw-bold text-danger"><?php echo rupiah($total_keluar); ?></td>
                                    </tr>
                                    <tr class="table-group-divider">
                                        <td class="fw-bold text-uppercase">SALDO AKHIR</td>
                                        <td class="text-end fw-bold fs-5"><?php echo rupiah($total_masuk - $total_keluar); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card animate-on-scroll">
                    <div class="card-header">
                        <h3 class="card-title">D. DETAIL SEMUA TRANSAKSI</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $query_detail = "SELECT t.*, u.nama_lengkap
                                       FROM transaksi_keuangan t
                                       JOIN users u ON t.nik_user_input = u.nik
                                       WHERE t.id_periode = $periode_id_selected
                                       ORDER BY t.tanggal_transaksi ASC, t.created_at ASC";
                        $result_detail = mysqli_query($conn, $query_detail);
                        ?>
                        <?php if (mysqli_num_rows($result_detail) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Masuk</th>
                                        <th class="text-end">Keluar</th>
                                        <th class="text-end">Saldo Berjalan</th>
                                        <th>Diinput Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no_detail = 1;
                                    $saldo_berjalan = 0;
                                    while ($row = mysqli_fetch_assoc($result_detail)):
                                        $masuk_val = 0;
                                        $keluar_val = 0;
                                        if ($row['jenis_transaksi'] == 'masuk') {
                                            $saldo_berjalan += $row['nominal'];
                                            $masuk_val = $row['nominal'];
                                        } else {
                                            $saldo_berjalan -= $row['nominal'];
                                            $keluar_val = $row['nominal'];
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $no_detail++; ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['tanggal_transaksi'])); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($row['keterangan'])); ?> <br><small class="text-muted">(Kategori: <?php echo ucwords(str_replace('_',' ',htmlspecialchars($row['kategori']))); ?>)</small></td>
                                        <td class="text-end text-success"><?php echo $masuk_val > 0 ? rupiah($masuk_val) : '-'; ?></td>
                                        <td class="text-end text-danger"><?php echo $keluar_val > 0 ? rupiah($keluar_val) : '-'; ?></td>
                                        <td class="text-end fw-bold"><?php echo rupiah($saldo_berjalan); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <div class="alert alert-info">Tidak ada detail transaksi untuk periode ini.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif (!$periode_info && $periode_id_selected == 0): ?>
                 <div class="alert alert-warning">
                    <p class="mb-0">Belum ada periode qurban aktif atau periode belum dipilih. Silakan pilih periode untuk melihat laporan.</p>
                </div>
            <?php elseif (!$periode_info && $periode_id_selected > 0): ?>
                 <div class="alert alert-danger">
                    <p class="mb-0">Data untuk periode yang dipilih tidak ditemukan.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include_once '../src/components/footer.php'; ?>
    <script src="../src/js/main.js"></script>
</body>
</html>