<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
// Memastikan hanya user dengan role warga atau berqurban yang bisa akses
cek_multi_role(['warga', 'berqurban']);

$nik_user = $_SESSION['nik'];

$periode = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM periode_qurban WHERE is_active = TRUE LIMIT 1"));
$id_periode_aktif = $periode['id_periode'] ?? 0;

$kartu = null;
if ($id_periode_aktif) {
    $query_kartu = "SELECT d.*, p.tahun_masehi, p.tahun_hijriah
                    FROM distribusi_daging d
                    JOIN periode_qurban p ON d.id_periode = p.id_periode
                    WHERE d.nik_penerima = '$nik_user' AND d.status_ambil = 'belum_ambil' AND p.is_active = TRUE
                    ORDER BY d.created_at DESC LIMIT 1";
    $kartu = mysqli_fetch_assoc(mysqli_query($conn, $query_kartu));
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
    <title>Dashboard Warga - Sistem Qurban</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
    <style>
        /* Pastikan style ini konsisten atau pindahkan ke main.css */
        .menu-button-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--space-4, 1.5rem);
            text-align: center;
            background-color: var(--color-background-light, #f8f9fa);
            border-radius: var(--radius-md, 0.5rem);
            box-shadow: var(--shadow-sm, 0 2px 4px rgba(0,0,0,0.05));
            color: var(--color-text-primary);
            text-decoration: none;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid var(--color-border-muted, #dee2e6);
        }
        .menu-button-card:hover {
            background-color: var(--color-background-muted, #e9ecef);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md, 0 4px 8px rgba(0,0,0,0.1));
        }
        .menu-button-card svg {
            margin-bottom: var(--space-2, 0.5rem);
            width: 32px;
            height: 32px;
            stroke-width: 1.5;
        }
        .kartu-qurban { border: 2px solid var(--primary); border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem; background-color: white; box-shadow: 0 8px 16px rgba(0,0,0,0.1); max-width: 450px; margin-left: auto; margin-right: auto; }
        .kartu-qurban-header { text-align: center; margin-bottom: 1rem; }
        .kartu-qurban-header h2 { font-size: 1.3rem; font-weight: 600; color: var(--primary); margin-bottom: 0.25rem; border-bottom: none; }
        .info-item { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 1rem; }
        .info-item strong { font-weight: 600; }
        @media print {
            body { background-color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print, .main-header, .main-footer, .alert, .card-header, .card:not(.kartu-qurban-container) { display: none !important; }
            .main-content { padding: 0 !important; }
            .container { max-width: 100% !important; padding: 0 !important; margin:0; }
            .kartu-qurban { box-shadow: none !important; border: 1px solid #333 !important; width: 100%; max-width: 100%; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <?php include_once '../src/components/header_warga.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h1>Dashboard Warga</h1>
                <?php if ($periode): ?>
                    <span class="badge badge-primary">Periode: <?php echo htmlspecialchars($periode['tahun_hijriah']); ?>H</span>
                <?php endif; ?>
            </div>

            <div class="alert alert-info no-print">
                Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>!
            </div>

            <div class="card animate-on-scroll mb-4 kartu-qurban-container">
                <div class="card-header no-print"><h2 class="card-title">Kartu Pengambilan Daging</h2></div>
                <div class="card-body">
                    <?php if ($kartu): ?>
                        <div class="kartu-qurban" id="kartu-print-area">
                            <div class="text-center mb-3">
                                <h2>KARTU PENGAMBILAN DAGING QURBAN</h2>
                                <p class="text-muted mb-0">Panitia Qurban RT 001</p>
                            </div>
                            <hr>
                            <div class="info-item"><span>Nama:</span><strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong></div>
                            <div class="info-item"><span>Nomor Paket:</span><strong><?php echo htmlspecialchars($kartu['nomor_paket']); ?></strong></div>
                            <hr>
                            <div class="text-center my-3">
                                <img src="<?php echo tampilkan_qr_code($kartu['qr_code'], 200); ?>" alt="QR Code" style="border: 4px solid #eee; padding: 4px; border-radius: 8px;">
                                <p class="mt-2 mb-0">Token:</p>
                                <p><strong><?php echo htmlspecialchars($kartu['qr_code']); ?></strong></p>
                            </div>
                        </div>
                        <div class="text-center mt-4 no-print">
                            <button onclick="window.print()" class="btn btn-success">Cetak Kartu</button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">Belum ada kartu pengambilan daging yang tersedia untuk Anda.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card animate-on-scroll no-print">
                <div class="card-header"><h2 class="card-title">Riwayat Pembayaran Iuran</h2></div>
                <div class="card-body">
                    <?php $result_pembayaran = mysqli_query($conn, "SELECT * FROM pembayaran_iuran WHERE nik_pembayar = '$nik_user' ORDER BY tanggal_bayar DESC"); ?>
                    <?php if (mysqli_num_rows($result_pembayaran) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Tanggal</th><th>Jenis Iuran</th><th class="text-end">Nominal</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_pembayaran)): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($row['tanggal_bayar'])); ?></td>
                                    <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($row['jenis_iuran']))); ?></td>
                                    <td class="text-end"><?php echo rupiah($row['nominal']); ?></td>
                                    <td><span class="badge <?php echo $row['status_verifikasi'] == 'terverifikasi' ? 'badge-success' : 'badge-warning'; ?>"><?php echo ucfirst($row['status_verifikasi']); ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?><div class="alert alert-light">Belum ada riwayat pembayaran.</div><?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <?php include_once '../src/components/footer.php'; ?>

    <script src="../src/js/main.js"></script>
</body>
</html>