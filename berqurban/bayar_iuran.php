<?php
// file: berqurban/bayar_iuran.php (File Baru)
// Dibuat agar tidak redirect ke dashboard warga

require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_multi_role(['warga', 'berqurban']);

$periode_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM periode_qurban WHERE is_active = TRUE LIMIT 1"));
$id_periode_aktif = $periode_data['id_periode'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id_periode_aktif) {
    $nik_pembayar = mysqli_real_escape_string($conn, (string) $_SESSION['nik']);
    $jenis_iuran_input = trim((string) ($_POST['jenis_iuran'] ?? ''));
    $nominal = (float) preg_replace('/[^\d]/', '', (string) ($_POST['nominal'] ?? ''));
    $metode_bayar_input = (string) ($_POST['metode_bayar'] ?? '');
    $allowed_jenis_iuran = ['qurban_sapi', 'qurban_kambing', 'administrasi_sapi', 'administrasi_kambing', 'lainnya'];
    $allowed_metode_bayar = ['tunai', 'transfer'];

    if (!in_array($jenis_iuran_input, $allowed_jenis_iuran, true) || !in_array($metode_bayar_input, $allowed_metode_bayar, true) || $nominal <= 0) {
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Bayar Iuran Qurban</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
</head>
<body>
    <?php include_once '../src/components/header_berqurban.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Form Pembayaran Iuran</h1>
                <a href="index.php" class="btn btn-outline-primary">Kembali ke Dashboard</a>
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
                        </div>
                        <form method="POST" class="needs-validation mt-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="jenis_iuran" class="form-label">Jenis Iuran:</label>
                                    <select name="jenis_iuran" id="jenis_iuran" class="form-select" onchange="updateNominal(this.value)" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="qurban_sapi">Iuran Shodaqoh Sapi (1/7)</option>
                                        <option value="qurban_kambing">Iuran Shodaqoh Kambing</option>
                                        <option value="administrasi_sapi">Administrasi Sapi</option>
                                        <option value="administrasi_kambing">Administrasi Kambing</option>
                                        <option value="lainnya">Lainnya</option>
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
                            <div class="d-flex justify-content-end mt-4"><button type="submit" class="btn btn-primary btn-lg">Kirim</button></div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">Form pembayaran belum dapat digunakan.</div>
            <?php endif; ?>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
    <script>
    function updateNominal(jenis) {
        const nominalInput = document.getElementById('nominal');
        let nominal = 0;

        switch(jenis) {
            case 'qurban_sapi':
                nominal = 3000000;
                break;
            case 'qurban_kambing':
                nominal = 2700000;
                break;
            case 'administrasi_sapi':
                nominal = 100000;
                break;
            case 'administrasi_kambing':
                nominal = 50000;
                break;
            case 'lainnya':
                nominalInput.readOnly = false;
                nominalInput.value = '';
                return;
        }

        nominalInput.readOnly = true;
        nominalInput.value = nominal.toLocaleString('id-ID');
    }
    </script>
</body>
</html>