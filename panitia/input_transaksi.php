<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('panitia');

// Ambil periode aktif
$query_periode_db = "SELECT * FROM periode_qurban WHERE is_active = TRUE ORDER BY id_periode DESC LIMIT 1";
$result_periode_db = mysqli_query($conn, $query_periode_db);
$periode_data = mysqli_fetch_assoc($result_periode_db);
$id_periode_aktif = $periode_data['id_periode'] ?? 0;

// Proses input transaksi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id_periode_aktif) { // Pastikan ada periode aktif
    $jenis_transaksi = mysqli_real_escape_string($conn, $_POST['jenis_transaksi']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $nominal = floatval(str_replace('.', '', $_POST['nominal'])); // Hapus titik pemisah ribuan sebelum konversi
    $tanggal_transaksi = mysqli_real_escape_string($conn, $_POST['tanggal_transaksi']);
    $nik_user_input = mysqli_real_escape_string($conn, $_SESSION['nik']);

    if (empty($jenis_transaksi) || empty($kategori) || empty($keterangan) || empty($nominal) || empty($tanggal_transaksi)) {
        $error = "Semua field wajib diisi.";
    } else {
        $query_insert = "INSERT INTO transaksi_keuangan
                         (id_periode, jenis_transaksi, kategori, keterangan, nominal, tanggal_transaksi, nik_user_input)
                         VALUES
                         ($id_periode_aktif, '$jenis_transaksi', '$kategori', '$keterangan', $nominal, '$tanggal_transaksi', '$nik_user_input')";

        if (mysqli_query($conn, $query_insert)) {
            $success = "Transaksi berhasil dicatat!";
        } else {
            $error = "Error saat mencatat transaksi: " . mysqli_error($conn);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !$id_periode_aktif) {
    $error = "Tidak ada periode aktif untuk mencatat transaksi.";
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
    <title>Input Transaksi - Panitia</title>
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
                <h1>Input Transaksi Keuangan</h1>
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

            <?php if ($periode_data): ?>
                <div class="card animate-on-scroll mb-4">
                    <div class="card-header">
                        <h2 class="card-title">
                            Form Input Transaksi (Periode: <?php echo htmlspecialchars($periode_data['tahun_hijriah']); ?>H / <?php echo htmlspecialchars($periode_data['tahun_masehi']); ?>M)
                        </h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="jenis_transaksi" class="form-label">Jenis Transaksi:</label>
                                    <select name="jenis_transaksi" id="jenis_transaksi" class="form-select" onchange="updateKategori(this.value)" required>
                                        <option value="">-- Pilih Jenis --</option>
                                        <option value="masuk">Pemasukan</option>
                                        <option value="keluar">Pengeluaran</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kategori" class="form-label">Kategori Transaksi:</label>
                                    <select name="kategori" id="kategori" class="form-select" required>
                                        <option value="">-- Pilih Jenis Transaksi Terlebih Dahulu --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan:</label>
                                <textarea name="keterangan" id="keterangan" rows="3" class="form-control" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nominal" class="form-label">Nominal (Rp):</label>
                                    <input type="text" name="nominal" id="nominal" class="form-control" inputmode="numeric" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_transaksi" class="form-label">Tanggal Transaksi:</label>
                                    <input type="date" name="tanggal_transaksi" id="tanggal_transaksi" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1 align-middle">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                    Simpan Transaksi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <p class="mb-0">Belum ada periode qurban aktif. Fitur input transaksi belum dapat digunakan.</p>
                </div>
            <?php endif; ?>

            <div class="card animate-on-scroll">
                <div class="card-header">
                    <h2 class="card-title">10 Transaksi Terakhir (Periode Aktif)</h2>
                </div>
                <div class="card-body">
                    <?php if ($id_periode_aktif): ?>
                        <?php
                        $query_transaksi = "SELECT t.*, u.nama_lengkap
                                          FROM transaksi_keuangan t
                                          JOIN users u ON t.nik_user_input = u.nik
                                          WHERE t.id_periode = $id_periode_aktif
                                          ORDER BY t.tanggal_transaksi DESC, t.created_at DESC
                                          LIMIT 10";
                        $result_transaksi = mysqli_query($conn, $query_transaksi);
                        ?>
                        <?php if (mysqli_num_rows($result_transaksi) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Kategori</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Nominal</th>
                                        <th>Diinput Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no_transaksi = 1;
                                    while ($row = mysqli_fetch_assoc($result_transaksi)):
                                    ?>
                                    <tr>
                                        <td><?php echo $no_transaksi++; ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['tanggal_transaksi'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $row['jenis_transaksi'] == 'masuk' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($row['jenis_transaksi'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($row['kategori']))); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($row['keterangan'])); ?></td>
                                        <td class="text-end"><?php echo rupiah($row['nominal']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <div class="alert alert-info">Belum ada transaksi yang dicatat untuk periode aktif ini.</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">Silakan aktifkan periode qurban terlebih dahulu untuk melihat transaksi.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include_once '../src/components/footer.php'; ?>
    <script src="../src/js/main.js"></script>
    <script>
    function updateKategori(jenis) {
        const kategoriSelect = document.getElementById('kategori');
        kategoriSelect.innerHTML = '<option value="">-- Pilih Kategori --</option>'; // Default option

        let options = [];
        if (jenis === 'masuk') {
            options = [
                { value: 'iuran_qurban', text: 'Iuran Qurban' },
                { value: 'pembayaran_sapi', text: 'Pembayaran Qurban Sapi' },
                { value: 'pembayaran_kambing', text: 'Pembayaran Qurban Kambing/Domba' },
                { value: 'administrasi', text: 'Biaya Administrasi' },
                { value: 'donasi', text: 'Donasi' },
                { value: 'lainnya_masuk', text: 'Lainnya (Pemasukan)' }
            ];
        } else if (jenis === 'keluar') {
            options = [
                { value: 'pembelian_hewan', text: 'Pembelian Hewan' },
                { value: 'operasional_kandang', text: 'Operasional Kandang (Pakan, dll)' },
                { value: 'perlengkapan_penyembelihan', text: 'Perlengkapan Penyembelihan (Pisau, dll)' },
                { value: 'perlengkapan_distribusi', text: 'Perlengkapan Distribusi (Tas, Tali, dll)' },
                { value: 'konsumsi_panitia', text: 'Konsumsi Panitia' },
                { value: 'transportasi', text: 'Transportasi' },
                { value: 'biaya_jagal', text: 'Biaya Juru Sembelih (Jagal)' },
                { value: 'lainnya_keluar', text: 'Lainnya (Pengeluaran)' }
            ];
        }

        options.forEach(opt => {
            const optionElement = document.createElement('option');
            optionElement.value = opt.value;
            optionElement.textContent = opt.text;
            kategoriSelect.appendChild(optionElement);
        });
    }

    // Fungsi untuk format input nominal dengan pemisah ribuan
    document.addEventListener('DOMContentLoaded', function() {
        const nominalInput = document.getElementById('nominal');
        if (nominalInput) {
            nominalInput.addEventListener('keyup', function(e) {
                let value = e.target.value;
                value = value.replace(/[^0-9]/g, ''); // Hapus semua selain angka

                // Format dengan titik sebagai pemisah ribuan
                if (value) {
                    value = parseInt(value, 10).toLocaleString('id-ID');
                }
                e.target.value = value;
            });
        }
    });
    </script>
</body>
</html>