<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('panitia');

$id_periode_aktif = 0;
$query_periode_db = "SELECT * FROM periode_qurban WHERE is_active = TRUE ORDER BY id_periode DESC LIMIT 1";
$result_periode_db = mysqli_query($conn, $query_periode_db);
if ($periode_data = mysqli_fetch_assoc($result_periode_db)) {
    $id_periode_aktif = $periode_data['id_periode'];
}

// Fungsi placeholder jika generate_qr_code belum ada di helper.php
if (!function_exists('generate_qr_code')) {
    function generate_qr_code($data) {
        // Placeholder: Mengembalikan data input sebagai "QR Code"
        // Nantinya ini akan diganti dengan library pembuatan QR code
        return "QR_" . hash('sha256', $data . time()); // Contoh QR code unik sederhana
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_distribusi']) && $id_periode_aktif) {
    $id_hewan = intval($_POST['id_hewan']);
    $kategori_penerima = mysqli_real_escape_string($conn, $_POST['kategori_penerima']);
    $total_berat = floatval($_POST['total_berat']);
    $jumlah_paket = intval($_POST['jumlah_paket']);

    if (empty($id_hewan) || empty($kategori_penerima) || $total_berat <= 0 || $jumlah_paket <= 0) {
        $error = "Semua field wajib diisi dan harus valid.";
    } else {
        $berat_per_paket = $total_berat / $jumlah_paket;

        $query_insert_pembagian = "INSERT INTO pembagian_daging (id_periode, id_hewan, kategori_penerima, total_berat, jumlah_paket, berat_per_paket) VALUES ($id_periode_aktif, $id_hewan, '$kategori_penerima', $total_berat, $jumlah_paket, $berat_per_paket)";

        if (mysqli_query($conn, $query_insert_pembagian)) {
            $id_pembagian_baru = mysqli_insert_id($conn);
            $query_user_target = "";
            $role_field = 'is_' . $kategori_penerima; // Cth: is_warga, is_berqurban

            $query_user_target = "SELECT nik FROM users WHERE $role_field = 1 AND is_active = TRUE";

            $result_user_target = mysqli_query($conn, $query_user_target);
            $users_target = [];
            while ($row_user = mysqli_fetch_assoc($result_user_target)) {
                $users_target[] = $row_user['nik'];
            }

            if (!empty($users_target)) {
                $jumlah_user_target = count($users_target);
                $paket_per_user = floor($jumlah_paket / $jumlah_user_target);
                $sisa_paket = $jumlah_paket % $jumlah_user_target;

                $nomor_urut_paket = 1;
                foreach ($users_target as $index_user => $nik_target) {
                    $jumlah_paket_user = $paket_per_user + ($index_user < $sisa_paket ? 1 : 0);

                    for ($i = 0; $i < $jumlah_paket_user; $i++) {
                        if ($nomor_urut_paket > $jumlah_paket) break;

                        $nomor_paket_generated = strtoupper(substr($kategori_penerima, 0, 1)) . "-" . str_pad($nomor_urut_paket++, 3, '0', STR_PAD_LEFT);
                        $qr_code_generated = generate_qr_code($nomor_paket_generated . "-" . $id_periode_aktif . "-" . $id_pembagian_baru . "-" . $nik_target);

                        $query_insert_distribusi = "INSERT INTO distribusi_daging (nik_penerima, id_pembagian, id_periode, nomor_paket, qr_code, berat_daging, status_ambil) VALUES ('$nik_target', $id_pembagian_baru, $id_periode_aktif, '$nomor_paket_generated', '$qr_code_generated', $berat_per_paket, 'belum_ambil')";
                        mysqli_query($conn, $query_insert_distribusi);
                    }
                }
                $success = "Distribusi daging berhasil di-generate untuk $jumlah_paket paket!";
            } else {
                $error = "Tidak ada user yang ditemukan untuk kategori '$kategori_penerima'.";
                mysqli_query($conn, "DELETE FROM pembagian_daging WHERE id_pembagian = $id_pembagian_baru");
            }
        } else {
            $error = "Error saat menyimpan data pembagian: " . mysqli_error($conn);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_distribusi']) && !$id_periode_aktif) {
    $error = "Tidak ada periode aktif untuk generate distribusi.";
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
    <title>Distribusi Daging - Panitia</title>
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
                <h1>Distribusi Daging Qurban</h1>
                <a href="index.php" class="btn btn-outline-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <div><?php echo htmlspecialchars($success); ?></div>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($id_periode_aktif): ?>
                <div class="card animate-on-scroll mb-4">
                    <div class="card-header">
                        <h2 class="card-title">
                            Generate Distribusi Baru (Periode: <?php echo htmlspecialchars($periode_data['tahun_hijriah']); ?>H / <?php echo htmlspecialchars($periode_data['tahun_masehi']); ?>M)
                        </h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="id_hewan" class="form-label">Pilih Hewan:</label>
                                    <select name="id_hewan" id="id_hewan" class="form-select" required>
                                        <option value="">-- Pilih Hewan --</option>
                                        <?php
                                        $query_hewan = "SELECT h.*, pq.nik_peserta, u.nama_lengkap
                                                      FROM hewan_qurban h
                                                      JOIN peserta_qurban pq ON h.id_hewan = pq.id_hewan
                                                      JOIN users u ON pq.nik_peserta = u.nik
                                                      WHERE h.id_periode = $id_periode_aktif";
                                        $result_hewan = mysqli_query($conn, $query_hewan);
                                        while ($hewan = mysqli_fetch_assoc($result_hewan)):
                                        ?>
                                        <option value="<?php echo $hewan['id_hewan']; ?>">
                                            <?php echo htmlspecialchars($hewan['jenis_hewan']) . ' - ' . htmlspecialchars($hewan['nomor_hewan']) . ' (Peserta: ' . htmlspecialchars($hewan['nama_lengkap']) . ')'; ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kategori_penerima" class="form-label">Kategori Penerima:</label>
                                    <select name="kategori_penerima" id="kategori_penerima" class="form-select" required>
                                        <option value="warga">Warga (Umum)</option>
                                        <option value="berqurban">Peserta Qurban (Shohibul Qurban)</option>
                                        <option value="panitia">Panitia</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="total_berat" class="form-label">Total Berat Daging (kg):</label>
                                    <input type="number" name="total_berat" id="total_berat" class="form-control" step="0.1" min="0.1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="jumlah_paket" class="form-label">Jumlah Paket Dibuat:</label>
                                    <input type="number" name="jumlah_paket" id="jumlah_paket" class="form-control" min="1" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" name="generate_distribusi" class="btn btn-primary btn-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1 align-middle">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                    </svg>
                                    Generate Distribusi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <p class="mb-0">Belum ada periode qurban aktif. Fitur generate distribusi belum dapat digunakan.</p>
                </div>
            <?php endif; ?>

            <div class="card animate-on-scroll">
                <div class="card-header">
                    <h2 class="card-title">Data Pembagian Daging (Periode Aktif)</h2>
                </div>
                <div class="card-body">
                    <?php if ($id_periode_aktif): ?>
                        <?php
                        $query_pembagian_list = "SELECT pd.*, h.nomor_hewan, h.jenis_hewan
                                               FROM pembagian_daging pd
                                               JOIN hewan_qurban h ON pd.id_hewan = h.id_hewan
                                               WHERE pd.id_periode = $id_periode_aktif
                                               ORDER BY pd.created_at DESC";
                        $result_pembagian_list = mysqli_query($conn, $query_pembagian_list);
                        ?>
                        <?php if (mysqli_num_rows($result_pembagian_list) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Hewan</th>
                                        <th>Kategori Penerima</th>
                                        <th class="text-end">Total Berat (kg)</th>
                                        <th class="text-center">Jumlah Paket</th>
                                        <th class="text-end">Berat/Paket (kg)</th>
                                        <th>Tanggal Generate</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no_pembagian = 1;
                                    while ($row = mysqli_fetch_assoc($result_pembagian_list)):
                                    ?>
                                    <tr>
                                        <td><?php echo $no_pembagian++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nomor_hewan']) . ' - ' . ucfirst(htmlspecialchars($row['jenis_hewan'])); ?></td>
                                        <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($row['kategori_penerima']))); ?></td>
                                        <td class="text-end"><?php echo number_format($row['total_berat'], 1); ?></td>
                                        <td class="text-center"><?php echo $row['jumlah_paket']; ?></td>
                                        <td class="text-end"><?php echo number_format($row['berat_per_paket'], 2); ?></td>
                                        <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                                        <td class="text-center">
                                            <a href="detail_distribusi.php?id=<?php echo $row['id_pembagian']; ?>" class="btn btn-info btn-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <div class="alert alert-info">Belum ada data pembagian daging untuk periode aktif ini.</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">Silakan aktifkan periode qurban terlebih dahulu untuk melihat data pembagian.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include_once '../src/components/footer.php'; ?>
    <script src="../src/js/main.js"></script>
</body>
</html>