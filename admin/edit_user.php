<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('admin');

// Ambil NIK dari URL
$nik_user = isset($_GET['nik']) ? mysqli_real_escape_string($conn, $_GET['nik']) : '';
if (empty($nik_user)) {
    header("Location: kelola_user.php");
    exit();
}

// Proses update user saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_kk = mysqli_real_escape_string($conn, $_POST['no_kk']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Ambil data role dari checkbox
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $is_panitia = isset($_POST['is_panitia']) ? 1 : 0;
    $is_warga = isset($_POST['is_warga']) ? 1 : 0;
    $is_berqurban = isset($_POST['is_berqurban']) ? 1 : 0;

    $query_update = "UPDATE users SET
                        nama_lengkap = '$nama_lengkap',
                        no_kk = '$no_kk',
                        alamat = '$alamat',
                        no_hp = '$no_hp',
                        email = '$email',
                        is_active = $is_active,
                        is_admin = $is_admin,
                        is_panitia = $is_panitia,
                        is_warga = $is_warga,
                        is_berqurban = $is_berqurban";

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query_update .= ", password = '$password'";
    }

    $query_update .= " WHERE nik = '$nik_user'";

    if (mysqli_query($conn, $query_update)) {
        $success = "Data user berhasil diupdate!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Ambil data user terbaru untuk ditampilkan di form
$query_get_user = "SELECT * FROM users WHERE nik = '$nik_user'";
$result_get_user = mysqli_query($conn, $query_get_user);
$user = mysqli_fetch_assoc($result_get_user);

if (!$user) {
    header("Location: kelola_user.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit User - <?php echo htmlspecialchars($user['nama_lengkap']); ?></title>
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
                <h1>Edit User: <?php echo htmlspecialchars($user['username']); ?></h1>
                <a href="kelola_user.php" class="btn btn-outline-primary">Kembali ke Kelola User</a>
            </div>

            <?php if (isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <div class="card animate-on-scroll">
                <div class="card-header"><h2 class="card-title">Edit Data User</h2></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group mb-3">
                            <label for="nik_disabled" class="form-label">NIK</label>
                            <input type="text" id="nik_disabled" name="nik_disabled" class="form-control" value="<?php echo htmlspecialchars($user['nik']); ?>" disabled>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                        </div>

                        <div class="d-flex flex-wrap gap-4 mb-3">
                            <div class="form-group" style="flex: 1; min-width: 250px;"><label for="nama_lengkap">Nama Lengkap</label><input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required></div>
                            <div class="form-group" style="flex: 1; min-width: 250px;"><label for="email">Email</label><input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>"></div>
                        </div>
                        <div class="d-flex flex-wrap gap-4 mb-3">
                            <div class="form-group" style="flex: 1; min-width: 250px;"><label for="no_kk">No. KK</label><input type="text" id="no_kk" name="no_kk" class="form-control" value="<?php echo htmlspecialchars($user['no_kk']); ?>"></div>
                             <div class="form-group" style="flex: 1; min-width: 250px;"><label for="no_hp">No HP</label><input type="text" id="no_hp" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($user['no_hp']); ?>"></div>
                        </div>
                         <div class="d-flex flex-wrap gap-4 mb-3">
                           <div class="form-group" style="flex: 1; min-width: 250px;"><label for="alamat">Alamat</label><textarea id="alamat" name="alamat" class="form-control"><?php echo htmlspecialchars($user['alamat']); ?></textarea></div>
                        </div>

                        <!-- Form Role dengan Checkbox -->
                        <div class="form-group mb-4">
                            <label class="form-label">Peran</label>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_admin" value="1" id="is_admin_edit" <?php if($user['is_admin']) echo 'checked'; ?>><label class="form-check-label" for="is_admin_edit">Admin</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_panitia" value="1" id="is_panitia_edit" <?php if($user['is_panitia']) echo 'checked'; ?>><label class="form-check-label" for="is_panitia_edit">Panitia</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_berqurban" value="1" id="is_berqurban_edit" <?php if($user['is_berqurban']) echo 'checked'; ?>><label class="form-check-label" for="is_berqurban_edit">Berqurban</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_warga" value="1" id="is_warga_edit" <?php if($user['is_warga']) echo 'checked'; ?>><label class="form-check-label" for="is_warga_edit">Warga</label></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" <?php if ($user['is_active']) echo 'checked'; ?>>
                                <label class="form-check-label" for="is_active">User Aktif</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
</body>
</html>
