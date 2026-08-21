<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('admin');

$error = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// Proses tambah periode
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'tambah') {
    verify_csrf_request();
    $tahun_hijriah = mysqli_real_escape_string($conn, $_POST['tahun_hijriah']);
    $tahun_masehi = intval($_POST['tahun_masehi']);
    $tanggal_pelaksanaan = mysqli_real_escape_string($conn, $_POST['tanggal_pelaksanaan']);

    $query_insert = "INSERT INTO periode_qurban (tahun_hijriah, tahun_masehi, tanggal_pelaksanaan, is_active) VALUES ('$tahun_hijriah', $tahun_masehi, '$tanggal_pelaksanaan', 0)";
    if (mysqli_query($conn, $query_insert)) {
        header("Location: kelola_periode.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Periode</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
</head>
<body>
    <?php include_once '../src/components/header_admin.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Kelola Periode Qurban</h1>
                <a href="index.php" class="btn btn-outline-primary">Kembali ke Dashboard</a>
            </div>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <div class="card animate-on-scroll mb-4">
                <div class="card-header">
                    <h2 class="card-title">Tambah Periode Baru</h2>
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="tambah">
                        <div class="col-md-4">
                            <label for="tahun_hijriah" class="form-label">Tahun Hijriah</label>
                            <input type="text" id="tahun_hijriah" name="tahun_hijriah" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="tahun_masehi" class="form-label">Tahun Masehi</label>
                            <input type="number" id="tahun_masehi" name="tahun_masehi" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal_pelaksanaan" class="form-label">Tanggal Pelaksanaan</label>
                            <input type="date" id="tanggal_pelaksanaan" name="tanggal_pelaksanaan" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Tambah Periode</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card animate-on-scroll">
                <div class="card-header">
                    <h2 class="card-title">Daftar Periode</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tahun</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($conn, "SELECT * FROM periode_qurban ORDER BY tahun_masehi DESC");
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $row['tahun_hijriah']; ?> H / <?php echo $row['tahun_masehi']; ?> M</td>
                                    <td><?php echo date('d F Y', strtotime($row['tanggal_pelaksanaan'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['is_active'] ? 'badge-success' : 'badge-secondary'; ?>">
                                            <?php echo $row['is_active'] ? 'AKTIF' : 'Non-aktif'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="edit_periode.php?id=<?php echo $row['id_periode']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
</body>
</html>