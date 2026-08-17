<?php
// file: panitia/verifikasi_pembayaran.php

require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('panitia');

// Proses verifikasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id_pembayaran = intval($_POST['id_pembayaran']);
    $action = $_POST['action'];
    $status = '';

    if ($action === 'verify') {
        $status = 'terverifikasi';
    } elseif ($action === 'reject') {
        $status = 'ditolak';
    }

    $nik_verifikator = $_SESSION['nik'];

    if (!empty($status)) {
        // Update status pembayaran
        $query_update = "UPDATE pembayaran_iuran
                         SET status_verifikasi = '$status', nik_verifikator = '$nik_verifikator'
                         WHERE id_pembayaran = $id_pembayaran AND status_verifikasi = 'pending'";

        if (mysqli_query($conn, $query_update) && mysqli_affected_rows($conn) > 0) {
            // Jika terverifikasi, catat ke transaksi keuangan
            if ($status == 'terverifikasi') {
                $query_pembayaran_data = "SELECT p.*, u.nama_lengkap
                                          FROM pembayaran_iuran p
                                          JOIN users u ON p.nik_pembayar = u.nik
                                          WHERE p.id_pembayaran = $id_pembayaran";
                $result_pembayaran_data = mysqli_query($conn, $query_pembayaran_data);
                $pembayaran = mysqli_fetch_assoc($result_pembayaran_data);

                if ($pembayaran) {
                    $nama_pembayar = mysqli_real_escape_string($conn, $pembayaran['nama_lengkap']);
                    $jenis_iuran = mysqli_real_escape_string($conn, $pembayaran['jenis_iuran']);
                    $tanggal_bayar = mysqli_real_escape_string($conn, $pembayaran['tanggal_bayar']);
                    $nik_verifikator_db = mysqli_real_escape_string($conn, $nik_verifikator);
                    $keterangan_transaksi = "Pembayaran " . str_replace('_', ' ', $jenis_iuran) . " a/n " . $nama_pembayar . " (ID Bayar: $id_pembayaran)";
                    $keterangan_transaksi = mysqli_real_escape_string($conn, $keterangan_transaksi);

                    $query_transaksi = "INSERT INTO transaksi_keuangan (id_periode, jenis_transaksi, kategori, keterangan, nominal, tanggal_transaksi, nik_user_input)
                                        VALUES ({$pembayaran['id_periode']}, 'masuk', '$jenis_iuran', '$keterangan_transaksi', {$pembayaran['nominal']}, '$tanggal_bayar', '$nik_verifikator_db')";

                    if (mysqli_query($conn, $query_transaksi)) {
                        $id_transaksi_baru = mysqli_insert_id($conn);
                        mysqli_query($conn, "UPDATE pembayaran_iuran SET id_transaksi = $id_transaksi_baru WHERE id_pembayaran = $id_pembayaran");
                    } else {
                        error_log('Failed to create payment transaction: ' . mysqli_error($conn));
                    }
                }
            }
            $success = "Status pembayaran berhasil diubah menjadi " . ucfirst($status) . "!";
        } else {
            $error = "Gagal mengubah status: Pembayaran mungkin sudah diproses atau tidak ditemukan.";
        }
    } else {
        $error = "Aksi tidak valid.";
    }
}

// Ambil periode aktif
$query_periode_db = "SELECT * FROM periode_qurban WHERE is_active = TRUE ORDER BY id_periode DESC LIMIT 1";
$result_periode_db = mysqli_query($conn, $query_periode_db);
$periode_data = mysqli_fetch_assoc($result_periode_db);
$id_periode_aktif = $periode_data['id_periode'] ?? 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Verifikasi Pembayaran - Panitia</title>
    <!-- (Meta tags dan link CSS tidak berubah) -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
</head>
<body>
    <?php include_once '../src/components/header_panitia.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Verifikasi Pembayaran Iuran</h1>
                <a href="index.php" class="btn btn-outline-primary">Kembali ke Dashboard</a>
            </div>

            <?php if (isset($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <?php if ($periode_data): ?>
                <div class="card animate-on-scroll mb-4">
                    <div class="card-header"><h2 class="card-title">Daftar Pembayaran Pending (Periode: <?php echo htmlspecialchars($periode_data['tahun_hijriah']); ?>H)</h2></div>
                    <div class="card-body">
                        <?php
                        // Query diubah untuk JOIN ke users berdasarkan NIK
                        $query_pending = "SELECT p.*, u.nama_lengkap, u.no_hp
                                          FROM pembayaran_iuran p
                                          JOIN users u ON p.nik_pembayar = u.nik
                                          WHERE p.id_periode = $id_periode_aktif AND p.status_verifikasi = 'pending'
                                          ORDER BY p.created_at ASC";
                        $result_pending = mysqli_query($conn, $query_pending);
                        ?>
                        <?php if (mysqli_num_rows($result_pending) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th><th>Tanggal Bayar</th><th>Nama Pembayar</th><th>No HP</th><th>Jenis Iuran</th><th class="text-end">Nominal</th><th>Metode</th><th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no_pending = 1; while ($row = mysqli_fetch_assoc($result_pending)): ?>
                                    <tr>
                                        <td><?php echo $no_pending++; ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['tanggal_bayar'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                                        <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($row['jenis_iuran']))); ?></td>
                                        <td class="text-end"><?php echo rupiah($row['nominal']); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($row['metode_bayar'])); ?></td>
                                        <td class="text-center">
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Anda yakin ingin memverifikasi pembayaran ini?');">
                                                <input type="hidden" name="id_pembayaran" value="<?php echo $row['id_pembayaran']; ?>">
                                                <button type="submit" name="action" value="verify" class="btn btn-success btn-sm">Verifikasi</button>
                                            </form>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Anda yakin ingin menolak pembayaran ini?');">
                                                <input type="hidden" name="id_pembayaran" value="<?php echo $row['id_pembayaran']; ?>">
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Tolak</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <div class="alert alert-info">Tidak ada pembayaran yang menunggu verifikasi.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card animate-on-scroll">
                    <div class="card-header"><h2 class="card-title">Riwayat Verifikasi Pembayaran (20 Terbaru)</h2></div>
                    <div class="card-body">
                        <?php
                        // Query riwayat diubah untuk JOIN ke users berdasarkan NIK
                        $query_riwayat = "SELECT p.*, u.nama_lengkap, v.nama_lengkap as nama_verifikator
                                          FROM pembayaran_iuran p
                                          JOIN users u ON p.nik_pembayar = u.nik
                                          LEFT JOIN users v ON p.nik_verifikator = v.nik
                                          WHERE p.id_periode = $id_periode_aktif AND p.status_verifikasi != 'pending'
                                          ORDER BY p.id_pembayaran DESC
                                          LIMIT 20";
                        $result_riwayat = mysqli_query($conn, $query_riwayat);
                        ?>
                         <?php if (mysqli_num_rows($result_riwayat) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th><th>Tgl Bayar</th><th>Nama Pembayar</th><th>Jenis Iuran</th><th class="text-end">Nominal</th><th>Status</th><th>Verifikator</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no_riwayat = 1; while ($row = mysqli_fetch_assoc($result_riwayat)):
                                        $status_badge = ($row['status_verifikasi'] == 'terverifikasi') ? 'badge-success' : 'badge-danger';
                                    ?>
                                    <tr>
                                        <td><?php echo $no_riwayat++; ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['tanggal_bayar'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($row['jenis_iuran']))); ?></td>
                                        <td class="text-end"><?php echo rupiah($row['nominal']); ?></td>
                                        <td><span class="badge <?php echo $status_badge; ?>"><?php echo ucfirst(htmlspecialchars($row['status_verifikasi'])); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['nama_verifikator'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <div class="alert alert-info">Belum ada riwayat verifikasi untuk periode ini.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning"><p class="mb-0">Belum ada periode qurban aktif.</p></div>
            <?php endif; ?>
        </div>
    </main>

    <?php include_once '../src/components/footer.php'; ?>
    <script src="../src/js/main.js"></script>
</body>
</html>