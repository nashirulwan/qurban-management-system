<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('admin');

$id_hewan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data hewan
$query = "SELECT * FROM hewan_qurban WHERE id_hewan = $id_hewan";
$result = mysqli_query($conn, $query);
$hewan = mysqli_fetch_assoc($result);

if (!$hewan) {
    header("Location: kelola_hewan.php");
    exit();
}

// Proses update hewan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_hewan = mysqli_real_escape_string($conn, $_POST['jenis_hewan']);
    $nomor_hewan = mysqli_real_escape_string($conn, $_POST['nomor_hewan']);
    $harga_hewan = (int)$_POST['harga_hewan'];
    $biaya_admin = (int)$_POST['biaya_admin'];
    $estimasi_daging = (float)$_POST['estimasi_daging'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $query = "UPDATE hewan_qurban SET
              jenis_hewan = '$jenis_hewan',
              nomor_hewan = '$nomor_hewan',
              harga_hewan = $harga_hewan,
              biaya_admin = $biaya_admin,
              estimasi_daging = $estimasi_daging,
              status = '$status'
              WHERE id_hewan = $id_hewan";

    if (mysqli_query($conn, $query)) {
        $success = "Data hewan qurban berhasil diupdate!";
        // Refresh data hewan
        $result = mysqli_query($conn, "SELECT * FROM hewan_qurban WHERE id_hewan = $id_hewan");
        $hewan = mysqli_fetch_assoc($result);
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Hewan Qurban - Admin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
</head>
<body>
    <?php include_once '../src/components/header_admin.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Edit Hewan Qurban</h1>

                <a href="kelola_hewan.php" class="btn btn-outline-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali ke Kelola Hewan
                </a>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="card animate-on-scroll">
                <div class="card-header">
                    <h2 class="card-title">Edit Data Hewan</h2>
                </div>

                <div class="card-body">
                    <form method="POST" class="needs-validation">
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label for="jenis_hewan" class="form-label">Jenis Hewan</label>
                                <select id="jenis_hewan" name="jenis_hewan" class="form-select" required>
                                    <option value="sapi" <?php echo $hewan['jenis_hewan'] == 'sapi' ? 'selected' : ''; ?>>Sapi</option>
                                    <option value="kambing" <?php echo $hewan['jenis_hewan'] == 'kambing' ? 'selected' : ''; ?>>Kambing</option>
                                    <option value="domba" <?php echo $hewan['jenis_hewan'] == 'domba' ? 'selected' : ''; ?>>Domba</option>
                                </select>
                            </div>

                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label for="nomor_hewan" class="form-label">Nomor Hewan</label>
                                <input type="text" id="nomor_hewan" name="nomor_hewan" class="form-control" value="<?php echo $hewan['nomor_hewan']; ?>" required>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label for="harga_hewan" class="form-label">Harga Hewan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="harga_hewan" name="harga_hewan" class="form-control" value="<?php echo $hewan['harga_hewan']; ?>" required>
                                </div>
                            </div>

                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label for="biaya_admin" class="form-label">Biaya Admin</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="biaya_admin" name="biaya_admin" class="form-control" value="<?php echo $hewan['biaya_admin']; ?>" required>
                                </div>
                            </div>

                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label for="estimasi_daging" class="form-label">Estimasi Daging (kg)</label>
                                <input type="number" step="0.1" id="estimasi_daging" name="estimasi_daging" class="form-control" value="<?php echo $hewan['estimasi_daging']; ?>" required>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="tersedia" <?php echo $hewan['status'] == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                                <option value="terpesan" <?php echo $hewan['status'] == 'terpesan' ? 'selected' : ''; ?>>Terpesan</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include_once '../src/components/footer.php'; ?>

    <script src="../src/js/main.js"></script>
</body>
</html>