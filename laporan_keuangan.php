<?php
require_once 'config/database.php';
require_once 'function/helper.php';

cek_login();
cek_role('panitia');

// Ambil periode
$periode_id = isset($_GET['periode']) ? intval($_GET['periode']) : 0;
if ($periode_id == 0) {
    $query = "SELECT * FROM periode_qurban WHERE is_active = TRUE LIMIT 1";
    $result = mysqli_query($conn, $query);
    $periode = mysqli_fetch_assoc($result);
    $periode_id = $periode['id_periode'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Laporan Keuangan - Sistem Qurban</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/styles/main.css">
    <!-- Add favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 20px;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
                margin-bottom: 20px !important;
            }
            .main-content {
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Include the header with no-print class -->
    <div class="no-print">
        <?php include_once 'src/components/header.php'; ?>
    </div>

    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h1>Laporan Keuangan Qurban</h1>

                <div class="d-flex gap-3">
                    <a href="index.php" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali ke Dashboard
                    </a>

                    <button onclick="window.print()" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        Cetak Laporan
                    </button>
                </div>
            </div>

            <!-- Periode Selector -->
            <div class="card mb-5 no-print">
                <div class="card-body">
                    <form method="GET" class="d-flex align-items-center gap-3">
                        <label for="periode" class="form-label mb-0 text-secondary-dark"><strong>Pilih Periode:</strong></label>
                        <select name="periode" id="periode" class="form-select" style="max-width: 300px;" onchange="this.form.submit()">
                            <?php
                            $query = "SELECT * FROM periode_qurban ORDER BY tahun_masehi DESC";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <option value="<?php echo $row['id_periode']; ?>"
                                    <?php echo $row['id_periode'] == $periode_id ? 'selected' : ''; ?>>
                                <?php echo $row['tahun_hijriah'] . ' H / ' . $row['tahun_masehi'] . ' M'; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Print Header -->
            <div class="text-center mb-5 d-none d-print-block">
                <h1 style="margin-bottom: 0.5rem;">LAPORAN KEUANGAN QURBAN</h1>
                <h3 style="margin-bottom: 1.5rem;">
                    <?php
                    $query = "SELECT * FROM periode_qurban WHERE id_periode = $periode_id";
                    $result = mysqli_query($conn, $query);
                    $periode_print = mysqli_fetch_assoc($result);
                    echo $periode_print ? $periode_print['tahun_hijriah'] . ' H / ' . $periode_print['tahun_masehi'] . ' M' : '';
                    ?>
                </h3>
                <p>Tanggal Cetak: <?php echo date('d F Y'); ?></p>
                <hr style="margin: 1.5rem 0;">
            </div>

            <!-- Report Content -->
            <div class="card animate-on-scroll mb-5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title mb-0">A. PEMASUKAN</h2>
                </div>

                <div class="card-body">
                    <?php
                    $query = "SELECT kategori, SUM(nominal) as total
                            FROM transaksi_keuangan
                            WHERE id_periode = $periode_id AND jenis_transaksi = 'masuk'
                            GROUP BY kategori";
                    $result = mysqli_query($conn, $query);
                    $total_masuk = 0;
                    ?>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>Kategori</th>
                                    <th width="200" class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($result) > 0):
                                    while ($row = mysqli_fetch_assoc($result)):
                                        $total_masuk += $row['total'];
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo str_replace('_', ' ', ucfirst($row['kategori'])); ?></td>
                                    <td class="text-right"><?php echo rupiah($row['total']); ?></td>
                                </tr>
                                <?php
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="3" class="text-center">Belum ada data pemasukan</td>
                                </tr>
                                <?php endif; ?>

                                <tr class="bg-light">
                                    <td colspan="2"><strong>TOTAL PEMASUKAN</strong></td>
                                    <td class="text-right"><strong><?php echo rupiah($total_masuk); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card animate-on-scroll mb-5">
                <div class="card-header">
                    <h2 class="card-title mb-0">B. PENGELUARAN</h2>
                </div>

                <div class="card-body">
                    <?php
                    $query = "SELECT kategori, SUM(nominal) as total
                            FROM transaksi_keuangan
                            WHERE id_periode = $periode_id AND jenis_transaksi = 'keluar'
                            GROUP BY kategori";
                    $result = mysqli_query($conn, $query);
                    $total_keluar = 0;
                    ?>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>Kategori</th>
                                    <th width="200" class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($result) > 0):
                                    while ($row = mysqli_fetch_assoc($result)):
                                        $total_keluar += $row['total'];
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo str_replace('_', ' ', ucfirst($row['kategori'])); ?></td>
                                    <td class="text-right"><?php echo rupiah($row['total']); ?></td>
                                </tr>
                                <?php
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="3" class="text-center">Belum ada data pengeluaran</td>
                                </tr>
                                <?php endif; ?>

                                <tr class="bg-light">
                                    <td colspan="2"><strong>TOTAL PENGELUARAN</strong></td>
                                    <td class="text-right"><strong><?php echo rupiah($total_keluar); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card animate-on-scroll mb-5">
                <div class="card-header">
                    <h2 class="card-title mb-0">C. SALDO AKHIR</h2>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 50%;"><strong>Total Pemasukan</strong></td>
                                    <td class="text-right"><?php echo rupiah($total_masuk); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Pengeluaran</strong></td>
                                    <td class="text-right"><?php echo rupiah($total_keluar); ?></td>
                                </tr>
                                <tr class="bg-<?php echo ($total_masuk - $total_keluar) >= 0 ? 'success-light' : 'danger-light'; ?>">
                                    <td><strong>SALDO AKHIR</strong></td>
                                    <td class="text-right"><strong><?php echo rupiah($total_masuk - $total_keluar); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card animate-on-scroll">
                <div class="card-header">
                    <h2 class="card-title mb-0">D. DETAIL TRANSAKSI</h2>
                </div>

                <div class="card-body">
                    <?php
                    $query = "SELECT t.*, u.nama_lengkap
                            FROM transaksi_keuangan t
                            JOIN users u ON t.nik_user_input = u.nik
                            WHERE t.id_periode = $periode_id
                            ORDER BY t.tanggal_transaksi, t.created_at";
                    $result = mysqli_query($conn, $query);
                    ?>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th width="120">Tanggal</th>
                                    <th>Keterangan</th>
                                    <th width="130" class="text-right">Masuk</th>
                                    <th width="130" class="text-right">Keluar</th>
                                    <th width="130" class="text-right">Saldo</th>
                                    <th>Input By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $saldo = 0;

                                if (mysqli_num_rows($result) > 0):
                                    while ($row = mysqli_fetch_assoc($result)):
                                        if ($row['jenis_transaksi'] == 'masuk') {
                                            $saldo += $row['nominal'];
                                            $masuk = $row['nominal'];
                                            $keluar = 0;
                                        } else {
                                            $saldo -= $row['nominal'];
                                            $masuk = 0;
                                            $keluar = $row['nominal'];
                                        }
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($row['tanggal_transaksi'])); ?></td>
                                    <td><?php echo $row['keterangan']; ?></td>
                                    <td class="text-right"><?php echo $masuk > 0 ? rupiah($masuk) : '-'; ?></td>
                                    <td class="text-right"><?php echo $keluar > 0 ? rupiah($keluar) : '-'; ?></td>
                                    <td class="text-right"><?php echo rupiah($saldo); ?></td>
                                    <td><?php echo $row['nama_lengkap']; ?></td>
                                </tr>
                                <?php
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data transaksi</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Print Footer -->
            <div class="d-none d-print-block mt-5">
                <div class="d-flex justify-content-end">
                    <div class="text-center" style="width: 200px;">
                        <p>Desa AAAA, <?php echo date('d F Y'); ?></p>
                        <p>Ketua Panitia Qurban</p>
                        <br><br><br>
                        <p><strong>(..........................)</strong></p>
                    </div>
                </div>
            </div>

            <!-- Print Button -->
            <div class="d-flex justify-content-center mt-5 no-print">
                <button onclick="window.print()" class="btn btn-primary btn-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Cetak Laporan
                </button>
            </div>
        </div>
    </main>

    <!-- Include the footer with no-print class -->
    <div class="no-print">
        <?php include_once 'src/components/footer.php'; ?>
    </div>

    <!-- Include main JavaScript file -->
    <script src="src/js/main.js"></script>
</body>
</html>