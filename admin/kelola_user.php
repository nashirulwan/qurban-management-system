<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('admin');

// Proses tambah user saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'tambah') {
    verify_csrf_request();
    // Ambil dan amankan semua data dari form
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_kk = mysqli_real_escape_string($conn, $_POST['no_kk']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Ambil data role dari checkbox
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $is_panitia = isset($_POST['is_panitia']) ? 1 : 0;
    $is_warga = isset($_POST['is_warga']) ? 1 : 0;
    $is_berqurban = isset($_POST['is_berqurban']) ? 1 : 0;

    // Cek apakah NIK atau Username sudah ada
    $cek_query = "SELECT nik FROM users WHERE nik = '$nik' OR username = '$username'";
    $cek_result = mysqli_query($conn, $cek_query);
    if (mysqli_num_rows($cek_result) > 0) {
        $error = "NIK atau Username sudah digunakan!";
    } else {
        // Query INSERT yang lengkap sesuai struktur DB baru
        $query_insert = "INSERT INTO users (nik, username, password, nama_lengkap, no_kk, alamat, no_hp, email, is_active, is_admin, is_panitia, is_warga, is_berqurban)
                         VALUES ('$nik', '$username', '$password', '$nama_lengkap', '$no_kk', '$alamat', '$no_hp', '$email', 1, $is_admin, $is_panitia, $is_warga, $is_berqurban)";

        if (mysqli_query($conn, $query_insert)) {
            $success = "User berhasil ditambahkan!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola User - Admin</title>
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
                <h1>Kelola User</h1>
                <a href="index.php" class="btn btn-outline-primary">Kembali ke Dashboard</a>
            </div>

            <?php if (isset($success)): ?><div class="alert alert-success" role="alert"><?php echo $success; ?></div><?php endif; ?>
            <?php if (isset($error)): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>

            <div class="card animate-on-scroll mb-4">
                <div class="card-header"><h2 class="card-title">Tambah User Baru</h2></div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="tambah">
                        <div class="d-flex flex-wrap gap-4 mb-3">
                            <div class="form-group" style="flex: 1; min-width: 250px;"><label for="nik" class="form-label">NIK</label><input type="text" id="nik" name="nik" class="form-control" required></div>
                            <div class="form-group" style="flex: 1; min-width: 250px;"><label for="username" class="form-label">Username</label><input type="text" id="username" name="username" class="form-control" required></div>
                        </div>
                         <div class="d-flex flex-wrap gap-4 mb-3">
                            <div class="form-group" style="flex: 1; min-width: 250px;"><label for="password" class="form-label">Password</label><input type="password" id="password" name="password" class="form-control" required></div>
                            <div class="form-group" style="flex: 1; min-width: 250px;"><label for="nama_lengkap" class="form-label">Nama Lengkap</label><input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" required></div>
                        </div>
                        <div class="d-flex flex-wrap gap-4 mb-3">
                            <div class="form-group" style="flex: 1; min-width: 250px;"><label for="no_kk" class="form-label">No. KK</label><input type="text" id="no_kk" name="no_kk" class="form-control"></div>
                            <div class="form-group" style="flex: 1; min-width: 250px;"><label for="no_hp" class="form-label">No HP</label><input type="text" id="no_hp" name="no_hp" class="form-control"></div>
                        </div>
                        <div class="d-flex flex-wrap gap-4 mb-3">
                             <div class="form-group" style="flex: 1; min-width: 250px;"><label for="email" class="form-label">Email</label><input type="email" id="email" name="email" class="form-control"></div>
                             <div class="form-group" style="flex: 1; min-width: 250px;"><label for="alamat" class="form-label">Alamat</label><textarea id="alamat" name="alamat" class="form-control"></textarea></div>
                        </div>

                        <!-- Form Role Baru dengan Checkbox -->
                        <div class="form-group mb-4">
                            <label class="form-label">Peran (Bisa lebih dari satu)</label>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_admin" value="1" id="is_admin"><label class="form-check-label" for="is_admin">Admin</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_panitia" value="1" id="is_panitia"><label class="form-check-label" for="is_panitia">Panitia</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_berqurban" value="1" id="is_berqurban"><label class="form-check-label" for="is_berqurban">Berqurban</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_warga" value="1" id="is_warga" checked><label class="form-check-label" for="is_warga">Warga</label></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Tambah User</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card animate-on-scroll">
                <div class="card-header"><h2 class="card-title">Daftar User</h2></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>No HP</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query_list = "SELECT * FROM users ORDER BY created_at DESC";
                                $result = mysqli_query($conn, $query_list);
                                $no = 1;
                                if (mysqli_num_rows($result) > 0):
                                    while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nik']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                                    <td>
                                        <!-- Menampilkan semua role yang dimiliki user -->
                                        <?php if($row['is_admin']) echo '<span class="badge badge-primary me-1">Admin</span>'; ?>
                                        <?php if($row['is_panitia']) echo '<span class="badge badge-secondary me-1">Panitia</span>'; ?>
                                        <?php if($row['is_berqurban']) echo '<span class="badge badge-success me-1">Berqurban</span>'; ?>
                                        <?php if($row['is_warga']) echo '<span class="badge badge-warning me-1">Warga</span>'; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $row['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $row['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Link edit sekarang menggunakan NIK -->
                                        <a href="edit_user.php?nik=<?php echo $row['nik']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    </td>
                                </tr>
                                <?php
                                    endwhile;
                                else:
                                ?>
                                <tr><td colspan="8" class="text-center">Belum ada data user</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once '../src/components/footer.php'; ?>
    <script src="../src/js/main.js"></script>
</body>
</html>
