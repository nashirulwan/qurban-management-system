<?php
// file: admin/edit_periode.php (File Baru dengan Tombol Aksi Lengkap)

require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('admin');

$id_periode = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_periode == 0) { header("Location: kelola_periode.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_request();
    if (isset($_POST['update'])) {
        $tahun_hijriah = mysqli_real_escape_string($conn, $_POST['tahun_hijriah']);
        $tahun_masehi = intval($_POST['tahun_masehi']);
        $tanggal_pelaksanaan = mysqli_real_escape_string($conn, $_POST['tanggal_pelaksanaan']);
        $query_update = "UPDATE periode_qurban SET tahun_hijriah = '$tahun_hijriah', tahun_masehi = $tahun_masehi, tanggal_pelaksanaan = '$tanggal_pelaksanaan' WHERE id_periode = $id_periode";
        if (mysqli_query($conn, $query_update)) {
            $success = "Data periode berhasil diupdate!";
        } else { $error = "Error: " . mysqli_error($conn); }
    } elseif (isset($_POST['activate'])) {
        mysqli_query($conn, "UPDATE periode_qurban SET is_active = FALSE");
        mysqli_query($conn, "UPDATE periode_qurban SET is_active = TRUE WHERE id_periode = $id_periode");
        header("Location: kelola_periode.php"); exit();
    } elseif (isset($_POST['delete'])) {
        $cek_hewan = mysqli_query($conn, "SELECT id_hewan FROM hewan_qurban WHERE id_periode = $id_periode");
        if(mysqli_num_rows($cek_hewan) > 0){
            $error = "Tidak bisa menghapus periode karena sudah digunakan di data hewan qurban.";
        } else {
            mysqli_query($conn, "DELETE FROM periode_qurban WHERE id_periode = $id_periode AND is_active = 0");
            header("Location: kelola_periode.php"); exit();
        }
    }
}

$periode = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM periode_qurban WHERE id_periode = $id_periode"));
if (!$periode) { header("Location: kelola_periode.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Periode</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
</head>
<body>
    <?php include_once '../src/components/header_admin.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Edit Periode Qurban</h1>
                <a href="kelola_periode.php" class="btn btn-outline-primary">Kembali ke Dashboard</a>
            </div>
            <?php if (isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="tahun_hijriah" class="form-label">Tahun Hijriah</label>
                            <input type="text" id="tahun_hijriah" name="tahun_hijriah" class="form-control" value="<?php echo htmlspecialchars($periode['tahun_hijriah']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="tahun_masehi" class="form-label">Tahun Masehi</label>
                            <input type="number" id="tahun_masehi" name="tahun_masehi" class="form-control" value="<?php echo htmlspecialchars($periode['tahun_masehi']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_pelaksanaan" class="form-label">Tanggal Pelaksanaan</label>
                            <input type="date" id="tanggal_pelaksanaan" name="tanggal_pelaksanaan" class="form-control" value="<?php echo htmlspecialchars($periode['tanggal_pelaksanaan']); ?>" required>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <?php if (!$periode['is_active']): ?>
                                <button type="submit" name="activate" class="btn btn-success" onclick="return confirm('Aktifkan periode ini?')">Aktifkan Periode</button>
                                <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Hapus periode ini?')">Hapus Periode</button>
                                <?php endif; ?>
                            </div>
                            <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
</body>
</html>