<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_multi_role(['warga', 'berqurban']);

$periode_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM periode_qurban WHERE is_active = TRUE LIMIT 1"));
$id_periode_aktif = $periode_data['id_periode'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id_periode_aktif) {
    verify_csrf_request();
    $nik_pembayar = mysqli_real_escape_string($conn, (string) $_SESSION['nik']);
    $jenis_iuran_input = trim((string) ($_POST['jenis_iuran'] ?? ''));
    $nominal = (float) preg_replace('/[^\d]/', '', (string) ($_POST['nominal'] ?? ''));
    $metode_bayar_input = (string) ($_POST['metode_bayar'] ?? '');
    $allowed_jenis_iuran = ['qurban_sapi', 'qurban_kambing', 'administrasi_sapi', 'administrasi_kambing', 'lainnya'];
    $allowed_metode_bayar = ['tunai', 'transfer'];
    $nominal_tetap = [
        'qurban_sapi' => 3000000,
        'qurban_kambing' => 2700000,
        'administrasi_sapi' => 100000,
        'administrasi_kambing' => 50000,
    ];
    $nominal_valid = !array_key_exists($jenis_iuran_input, $nominal_tetap)
        || (int) $nominal === $nominal_tetap[$jenis_iuran_input];

    if (!in_array($jenis_iuran_input, $allowed_jenis_iuran, true) || !in_array($metode_bayar_input, $allowed_metode_bayar, true) || $nominal <= 0 || !$nominal_valid) {
        $error = 'Jenis iuran, metode pembayaran, dan nominal harus valid.';
    } else {
        $jenis_iuran = mysqli_real_escape_string($conn, $jenis_iuran_input);
        $metode_bayar = mysqli_real_escape_string($conn, $metode_bayar_input);
        $query_insert = "INSERT INTO pembayaran_iuran (nik_pembayar, id_periode, jenis_iuran, nominal, tanggal_bayar, metode_bayar)
                         VALUES ('$nik_pembayar', $id_periode_aktif, '$jenis_iuran', $nominal, NOW(), '$metode_bayar')";

        if (mysqli_query($conn, $query_insert)) {
            $success = 'Pembayaran berhasil dicatat! Mohon tunggu verifikasi dari panitia.';
        } else {
            $error = 'Error: ' . mysqli_error($conn);
        }
    }
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
    <title>Bayar Iuran Qurban</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
</head>
<body>
    <?php include_once '../src/components/header_warga.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Form Pembayaran Iuran</h1>
                <a href="index.php" class="btn btn-outline-primary">Kembali</a>
            </div>

            <?php if (isset($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <?php if ($periode_data): ?>
                <div class="card animate-on-scroll">
                    <div class="card-header"><h3 class="card-title">Detail Iuran Periode <?php echo htmlspecialchars($periode_data['tahun_hijriah']); ?>H</h3></div>
                    <div class="card-body">
                        <div class="alert alert-secondary">
                            <h4 class="alert-heading">Informasi Nominal Iuran:</h4>
                            <ul class="list-unstyled mb-0">
                                <li>Iuran Shodaqoh Sapi (1/7 bagian): <strong><?php echo rupiah(3000000); ?></strong></li>
                                <li>Iuran Shodaqoh Kambing: <strong><?php echo rupiah(2700000); ?></strong></li>
                                <li>Biaya Administrasi Sapi: <strong><?php echo rupiah(100000); ?></strong></li>
                                <li>Biaya Administrasi Kambing: <strong><?php echo rupiah(50000); ?></strong></li>
                            </ul>
                            <hr><p class="mb-0">Pilih jenis iuran di bawah ini dan nominal akan terisi otomatis.</p>
                        </div>

                        <form method="POST" class="needs-validation mt-4">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="jenis_iuran" class="form-label">Jenis Iuran:</label>
                                    <select name="jenis_iuran" id="jenis_iuran" class="form-select" onchange="updateNominal(this.value)" required>
                                        <option value="">-- Pilih Jenis Iuran --</option>
                                        <option value="qurban_sapi">Iuran Shodaqoh Sapi (1/7)</option>
                                        <option value="qurban_kambing">Iuran Shodaqoh Kambing</option>
                                        <option value="administrasi_sapi">Administrasi Sapi</option>
                                        <option value="administrasi_kambing">Administrasi Kambing</option>
                                        <option value="lainnya">Lainnya (Isi manual)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nominal" class="form-label">Nominal (Rp):</label>
                                    <input type="text" name="nominal" id="nominal" class="form-control" required readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="metode_bayar" class="form-label">Metode Pembayaran:</label>
                                <select name="metode_bayar" id="metode_bayar" class="form-select" required>
                                    <option value="tunai">Tunai (ke Panitia)</option>
                                    <option value="transfer">Transfer Bank</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Kirim Bukti Pembayaran</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">Belum ada periode qurban aktif. Form pembayaran belum dapat digunakan.</div>
            <?php endif; ?>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
    <script>
    function updateNominal(jenis) {
        const nominalInput = document.getElementById('nominal');
        let calculatedNominal = '';
        let isReadOnly = true;

        switch(jenis) {
            case 'qurban_sapi': calculatedNominal = 3000000; break;
            case 'qurban_kambing': calculatedNominal = 2700000; break;
            case 'administrasi_sapi': calculatedNominal = 100000; break;
            case 'administrasi_kambing': calculatedNominal = 50000; break;
            case 'lainnya': isReadOnly = false; calculatedNominal = ''; break;
            default: calculatedNominal = '';
        }
        nominalInput.value = new Intl.NumberFormat('id-ID').format(calculatedNominal);
        nominalInput.readOnly = isReadOnly;
        if (!isReadOnly) { nominalInput.focus(); }
    }

    document.getElementById('nominal').addEventListener('keyup', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value) {
            e.target.value = new Intl.NumberFormat('id-ID').format(value);
        }
    });
    </script>
</body>
</html>