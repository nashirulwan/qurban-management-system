<?php
// file: panitia/kelola_pembayaran_qurban.php

require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('panitia');

$periode_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM periode_qurban WHERE is_active = TRUE LIMIT 1"));
$id_periode_aktif = $periode_data['id_periode'] ?? 0;

$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

$result_peserta = null;
if ($id_periode_aktif) {
    $query_peserta = "SELECT pq.*, u.nama_lengkap, u.no_hp, h.nomor_hewan, h.jenis_hewan
                      FROM peserta_qurban pq
                      JOIN users u ON pq.nik_peserta = u.nik
                      JOIN hewan_qurban h ON pq.id_hewan = h.id_hewan
                      WHERE pq.id_periode = $id_periode_aktif
                      ORDER BY pq.status_bayar ASC, pq.tanggal_daftar DESC";
    $result_peserta = mysqli_query($conn, $query_peserta);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Pembayaran Qurban</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
</head>
<body>
    <?php include_once '../src/components/header_panitia.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Kelola Pembayaran Peserta Qurban</h1>
                <a href="index.php" class="btn btn-outline-primary">Kembali ke Dashboard</a>
            </div>

            <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
            <?php if ($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

            <div class="card animate-on-scroll">
                <div class="card-header"><h2 class="card-title">Daftar Peserta Qurban (Periode <?php echo htmlspecialchars($periode_data['tahun_hijriah'] ?? '-'); ?>H)</h2></div>
                <div class="card-body">
                    <?php if ($result_peserta && mysqli_num_rows($result_peserta) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr><th>No</th><th>Nama Peserta</th><th>Hewan</th><th class="text-end">Nominal</th><th>Status</th><th class="text-center" style="width: 220px;">Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while($row = mysqli_fetch_assoc($result_peserta)): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?><br><small class="text-muted"><?php echo htmlspecialchars($row['no_hp']); ?></small></td>
                                    <td><?php echo htmlspecialchars($row['nomor_hewan']) . ' - ' . ucfirst(htmlspecialchars($row['jenis_hewan'])); ?></td>
                                    <td class="text-end"><?php echo rupiah($row['nominal_bayar']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['status_bayar'] == 'lunas' ? 'badge-success' : ($row['status_bayar'] == 'dp' ? 'badge-primary' : 'badge-danger'); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $row['status_bayar'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="proses_update_pembayaran_qurban.php" class="d-inline-flex">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="id_peserta" value="<?php echo $row['id_peserta']; ?>">
                                            <div class="input-group">
                                                <select name="status_baru" class="form-select form-select-sm" <?php if($row['status_bayar'] == 'lunas') echo 'disabled'; ?>>
                                                    <option value="belum_bayar" <?php if($row['status_bayar'] == 'belum_bayar') echo 'selected'; ?>>Belum Bayar</option>
                                                    <option value="dp" <?php if($row['status_bayar'] == 'dp') echo 'selected'; ?>>DP</option>
                                                    <option value="lunas" <?php if($row['status_bayar'] == 'lunas') echo 'selected'; ?>>Lunas</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-primary" <?php if($row['status_bayar'] == 'lunas') echo 'disabled'; ?>>Update</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-info">Belum ada peserta qurban yang terdaftar untuk periode aktif ini.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
</body>
</html>