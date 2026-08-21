<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('admin');

// Ambil periode aktif
$query = "SELECT * FROM periode_qurban WHERE is_active = TRUE ORDER BY id_periode DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$periode = mysqli_fetch_assoc($result);
$id_periode = $periode['id_periode'] ?? 0;

// Proses tambah hewan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    verify_csrf_request();
    if ($_POST['action'] == 'tambah') {
        $jenis_hewan = mysqli_real_escape_string($conn, $_POST['jenis_hewan']);
        $nomor_hewan = mysqli_real_escape_string($conn, $_POST['nomor_hewan']);
        $harga_hewan = (int)$_POST['harga_hewan'];
        $biaya_admin = (int)$_POST['biaya_admin'];
        $estimasi_daging = (float)$_POST['estimasi_daging'];

        $query = "INSERT INTO hewan_qurban (id_periode, jenis_hewan, nomor_hewan, harga_hewan, biaya_admin, estimasi_daging)
                  VALUES ($id_periode, '$jenis_hewan', '$nomor_hewan', $harga_hewan, $biaya_admin, $estimasi_daging)";

        if (mysqli_query($conn, $query)) {
            $success = "Hewan qurban berhasil ditambahkan!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Hewan Qurban - Admin</title>
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
                <h1>Kelola Hewan Qurban</h1>

                <a href="index.php" class="btn btn-outline-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali ke Dashboard
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

            <?php if ($periode): ?>
            <div class="card animate-on-scroll">
                <div class="card-header">
                    <h2 class="card-title">Tambah Hewan Qurban</h2>
                </div>

                <div class="card-body">
                    <form method="POST" class="needs-validation">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="tambah">

                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label for="jenis_hewan" class="form-label">Jenis Hewan</label>
                                <select id="jenis_hewan" name="jenis_hewan" class="form-select" required>
                                    <option value="sapi">Sapi</option>
                                    <option value="kambing">Kambing</option>
                                    <option value="domba">Domba</option>
                                </select>
                            </div>

                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label for="nomor_hewan" class="form-label">Nomor Hewan</label>
                                <input type="text" id="nomor_hewan" name="nomor_hewan" class="form-control" required>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label for="harga_hewan" class="form-label">Harga Hewan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="harga_hewan" name="harga_hewan" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label for="biaya_admin" class="form-label">Biaya Admin</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="biaya_admin" name="biaya_admin" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label for="estimasi_daging" class="form-label">Estimasi Daging (kg)</label>
                                <input type="number" step="0.1" id="estimasi_daging" name="estimasi_daging" class="form-control" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Tambah Hewan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card animate-on-scroll">
                <div class="card-header">
                    <h2 class="card-title">Daftar Hewan Qurban Periode <?php echo $periode['tahun_hijriah']; ?></h2>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    <th>Jenis Hewan</th>
                                    <th>Nomor</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Biaya Admin</th>
                                    <th class="text-right">Estimasi Daging</th>
                                    <th>Status</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM hewan_qurban WHERE id_periode = $id_periode ORDER BY id_hewan DESC";
                                $result = mysqli_query($conn, $query);
                                $no = 1;

                                if (mysqli_num_rows($result) > 0):
                                    while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['jenis_hewan'] == 'sapi' ? 'badge-primary' : 'badge-secondary'; ?>">
                                            <?php echo ucfirst($row['jenis_hewan']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['nomor_hewan']; ?></td>
                                    <td class="text-right"><?php echo rupiah($row['harga_hewan']); ?></td>
                                    <td class="text-right"><?php echo rupiah($row['biaya_admin']); ?></td>
                                    <td class="text-right"><?php echo $row['estimasi_daging']; ?> kg</td>
                                    <td>
                                        <span class="badge <?php echo $row['status'] == 'tersedia' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_hewan.php?id=<?php echo $row['id_hewan']; ?>" class="btn btn-sm btn-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada data hewan qurban</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">
                <p>Belum ada periode qurban aktif. Silakan aktifkan periode qurban terlebih dahulu.</p>
                <a href="kelola_periode.php" class="btn btn-warning mt-3">Kelola Periode Qurban</a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include_once '../src/components/footer.php'; ?>

    <script src="../src/js/main.js"></script>
</body>
</html>