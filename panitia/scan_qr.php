<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('panitia');

$periode_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM periode_qurban WHERE is_active = TRUE LIMIT 1"));
$id_periode_aktif = $periode_data['id_periode'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id_periode_aktif) {
    verify_csrf_request();
    $input_paket = trim((string) ($_POST['qr_code_input'] ?? ''));

    if ($input_paket !== '') {
        $query = "SELECT dd.*, u.nama_lengkap
                  FROM distribusi_daging dd
                  JOIN users u ON dd.nik_penerima = u.nik
                  WHERE dd.id_periode = ? AND (dd.qr_code = ? OR dd.nomor_paket = ?)";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'iss', $id_periode_aktif, $input_paket, $input_paket);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $paket = $result ? mysqli_fetch_assoc($result) : null;
            mysqli_stmt_close($stmt);
        } else {
            $paket = null;
        }

        if ($paket) {
            $query_update = "UPDATE distribusi_daging
                             SET status_ambil = 'sudah_ambil', tanggal_ambil = NOW(), nik_penyerah = ?
                             WHERE id_distribusi = ? AND status_ambil = 'belum_ambil'";
            $stmt_update = mysqli_prepare($conn, $query_update);
            $nik_penyerah = (string) $_SESSION['nik'];
            if ($stmt_update) {
                mysqli_stmt_bind_param($stmt_update, 'si', $nik_penyerah, $paket['id_distribusi']);
                mysqli_stmt_execute($stmt_update);
                $updated = mysqli_stmt_affected_rows($stmt_update) === 1;
                mysqli_stmt_close($stmt_update);
            } else {
                $updated = false;
            }

            if ($updated) {
                $_SESSION['success_message'] = "Paket {$paket['nomor_paket']} untuk {$paket['nama_lengkap']} berhasil diverifikasi!";
            } elseif ($paket['status_ambil'] === 'sudah_ambil') {
                $_SESSION['error_message'] = "Paket ini sudah diambil sebelumnya.";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui status.";
            }
        } else {
            $_SESSION['error_message'] = "QR Code atau nomor paket tidak valid!";
        }
    }
    header("Location: scan_qr.php");
    exit();
}
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Scan QR Code</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
</head>
<body>
    <?php include_once '../src/components/header_panitia.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Scan QR Code Pengambilan</h1>
                <a href="index.php" class="btn btn-outline-primary">Kembali ke Dashboard</a>
            </div>

            <?php if ($success_message): ?><div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
            <?php if ($error_message): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

            <div class="card animate-on-scroll">
                <div class="card-body text-center">
                    <p class="text-muted">Arahkan kamera ke QR Code atau ketik manual QR code maupun nomor paket di bawah ini.</p>
                    <div id="qr-reader" class="mb-3 mx-auto" style="width: 100%; max-width: 400px; border: 2px solid #dee2e6; border-radius: 0.75rem; overflow: hidden;"></div>
                    <form method="POST" id="form-scan">
                        <?php echo csrf_field(); ?>
                        <div class="input-group">
                            <input type="text" name="qr_code_input" class="form-control" placeholder="Hasil scan atau ketik manual..." required>
                            <button type="submit" class="btn btn-primary">Verifikasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrInput = document.querySelector('input[name="qr_code_input"]');
        const qrForm = document.getElementById('form-scan');
        function onScanSuccess(decodedText, decodedResult) {
            qrInput.value = decodedText;
            html5QrcodeScanner.clear().catch(error => console.error("Gagal stop scanner.", error));
            qrForm.submit();
        }
        let html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: { width: 250, height: 250 } }, false);
        html5QrcodeScanner.render(onScanSuccess, (error) => {});
    });
    </script>
</body>
</html>