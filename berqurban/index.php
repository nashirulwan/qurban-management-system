<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('berqurban');

$nik_user = $_SESSION['nik'];

$periode = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM periode_qurban WHERE is_active = TRUE LIMIT 1"));
$id_periode_aktif = $periode['id_periode'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Peserta Qurban</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
    <style>
        /* Pastikan style ini konsisten dengan yang ada di admin/index.php atau pindahkan ke main.css */
        .menu-button-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--space-4, 1.5rem); /* Sesuaikan dengan padding di index utama/admin */
            text-align: center;
            background-color: var(--color-background-light, #f8f9fa); /* Warna dari index utama/admin */
            border-radius: var(--radius-md, 0.5rem); /* Radius dari index utama/admin */
            box-shadow: var(--shadow-sm, 0 2px 4px rgba(0,0,0,0.05)); /* Shadow dari index utama/admin */
            color: var(--color-text-primary);
            text-decoration: none;
            transition: all 0.3s ease;
            height: 100%; /* Membuat semua card tombol sama tinggi */
            border: 1px solid var(--color-border-muted, #dee2e6); /* Tambahkan border jika diinginkan */
        }
        .menu-button-card:hover {
            background-color: var(--color-background-muted, #e9ecef);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md, 0 4px 8px rgba(0,0,0,0.1));
        }
        .menu-button-card svg {
            margin-bottom: var(--space-2, 0.5rem);
            width: 32px; /* Ukuran ikon bisa disesuaikan */
            height: 32px; /* Ukuran ikon bisa disesuaikan */
            stroke-width: 1.5; /* Ketebalan ikon */
        }
    </style>
</head>
<body>
    <?php include_once '../src/components/header_berqurban.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Dashboard Peserta Qurban</h1>
                <?php if ($periode): ?><span class="badge badge-primary">Periode: <?php echo htmlspecialchars($periode['tahun_hijriah']); ?>H</span><?php endif; ?>
            </div>
            <div class="alert alert-info">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>!</div>

            <div class="card animate-on-scroll mb-4">
                <div class="card-header"><h2 class="card-title">Status Pendaftaran Qurban Anda</h2></div>
                <div class="card-body">
                    <?php
                    $query_pendaftaran = "SELECT pq.*, h.jenis_hewan, h.nomor_hewan, h.status as status_hewan
                                          FROM peserta_qurban pq
                                          JOIN hewan_qurban h ON pq.id_hewan = h.id_hewan
                                          WHERE pq.nik_peserta = '$nik_user' AND pq.id_periode = $id_periode_aktif";
                    $result_pendaftaran = mysqli_query($conn, $query_pendaftaran);
                    ?>
                    <?php if (mysqli_num_rows($result_pendaftaran) > 0): $pendaftaran = mysqli_fetch_assoc($result_pendaftaran); ?>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">Hewan Pilihan: <strong><?php echo htmlspecialchars($pendaftaran['nomor_hewan']) . ' (' . ucfirst($pendaftaran['jenis_hewan']) . ')'; ?></strong></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Nominal Bayar: <strong><?php echo rupiah($pendaftaran['nominal_bayar']); ?></strong></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Status Pembayaran: <span class="badge <?php echo $pendaftaran['status_bayar'] == 'lunas' ? 'badge-success' : 'badge-warning'; ?>"><?php echo ucfirst(str_replace('_', ' ', $pendaftaran['status_bayar'])); ?></span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Tanggal Daftar: <strong><?php echo date('d F Y', strtotime($pendaftaran['tanggal_daftar'])); ?></strong></li>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-light text-center">
                            <p>Anda belum terdaftar sebagai peserta qurban pada periode ini.</p>
                            <a href="daftar_qurban.php" class="btn btn-primary">Daftar Qurban Sekarang</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card animate-on-scroll">
                <div class="card-header"><h2 class="card-title">Riwayat Qurban Tahun Lalu</h2></div>
                <div class="card-body">
                     <?php
                    $query_riwayat = "SELECT pq.*, h.jenis_hewan, p.tahun_hijriah, p.tahun_masehi
                                      FROM peserta_qurban pq
                                      JOIN hewan_qurban h ON pq.id_hewan = h.id_hewan
                                      JOIN periode_qurban p ON pq.id_periode = p.id_periode
                                      WHERE pq.nik_peserta = '$nik_user' AND p.is_active = 0
                                      ORDER BY p.tahun_masehi DESC";
                    $result_riwayat = mysqli_query($conn, $query_riwayat);
                    ?>
                    <?php if (mysqli_num_rows($result_riwayat) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Tahun</th><th>Hewan</th><th class="text-end">Nominal</th><th>Status Bayar</th></tr></thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_riwayat)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['tahun_hijriah']) . 'H'; ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($row['jenis_hewan'])); ?></td>
                                    <td class="text-end"><?php echo rupiah($row['nominal_bayar']); ?></td>
                                    <td><span class="badge <?php echo $row['status_bayar'] == 'lunas' ? 'badge-success' : 'badge-warning'; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['status_bayar'])); ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-light">Belum ada riwayat qurban dari tahun-tahun sebelumnya.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
</body>
</html>