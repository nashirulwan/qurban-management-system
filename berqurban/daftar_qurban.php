<?php
require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_multi_role(['warga', 'berqurban']);

$periode = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM periode_qurban WHERE is_active = TRUE LIMIT 1"));
$id_periode = $periode['id_periode'] ?? 0;
$nik_user = $_SESSION['nik'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_hewan']) && $id_periode > 0) {
    verify_csrf_request();
    $id_hewan = intval($_POST['id_hewan']);

    $hewan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hewan_qurban WHERE id_hewan = $id_hewan AND id_periode = $id_periode AND (status = 'rencana' OR status = 'tersedia')"));

    if ($hewan) {
        $data_jumlah_peserta = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM peserta_qurban WHERE id_hewan = $id_hewan"));
        $jumlah_peserta_saat_ini = $data_jumlah_peserta['jumlah'];
        $max_peserta = ($hewan['jenis_hewan'] == 'sapi') ? 7 : 1;

        if ($jumlah_peserta_saat_ini < $max_peserta) {
            $nominal = ($hewan['jenis_hewan'] == 'sapi') ? 3000000 : ($hewan['harga_hewan'] + $hewan['biaya_admin']);

            $result_cek_daftar = mysqli_query($conn, "SELECT id_peserta FROM peserta_qurban WHERE nik_peserta = '$nik_user' AND id_periode = $id_periode");
            if (mysqli_num_rows($result_cek_daftar) > 0) {
                 $error = "Anda sudah terdaftar sebagai peserta qurban pada periode ini.";
            } else {
                $query_insert = "INSERT INTO peserta_qurban (nik_peserta, id_hewan, id_periode, bagian_hewan, nominal_bayar) VALUES ('$nik_user', $id_hewan, $id_periode, 1, $nominal)";

                if (mysqli_query($conn, $query_insert)) {
                    if (empty($_SESSION['is_berqurban'])) {
                        mysqli_query($conn, "UPDATE users SET is_berqurban = 1 WHERE nik = '$nik_user'");
                        $_SESSION['is_berqurban'] = true;
                    }
                    if (($jumlah_peserta_saat_ini + 1) >= $max_peserta) {
                        mysqli_query($conn, "UPDATE hewan_qurban SET status = 'terpesan' WHERE id_hewan = $id_hewan");
                    }
                    $success = "Pendaftaran qurban berhasil! Silakan lakukan pembayaran.";
                } else {
                    $error = "Gagal mendaftar: " . mysqli_error($conn);
                }
            }
        } else {
            $error = "Kuota untuk hewan yang dipilih sudah penuh.";
        }
    } else {
        $error = "Hewan qurban tidak valid atau tidak tersedia.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Qurban</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
</head>
<body>
    <?php include_once '../src/components/header_berqurban.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Pendaftaran Qurban</h1>
                <a href="index.php" class="btn btn-outline-primary">Kembali</a>
            </div>

            <?php if (isset($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <?php if ($periode): ?>
            <div class="card animate-on-scroll">
                <div class="card-header"><h2 class="card-title">Pilih Hewan Qurban Periode <?php echo htmlspecialchars($periode['tahun_hijriah']); ?>H</h2></div>
                <div class="card-body">
                    <?php
                    $query_hewan = "SELECT h.*, (SELECT COUNT(*) FROM peserta_qurban pq WHERE pq.id_hewan = h.id_hewan) as jumlah_peserta
                                    FROM hewan_qurban h
                                    WHERE h.id_periode = $id_periode AND (h.status = 'rencana' OR h.status = 'tersedia')
                                    ORDER BY h.jenis_hewan, h.nomor_hewan ASC";
                    $result_hewan = mysqli_query($conn, $query_hewan);
                    ?>
                    <?php if(mysqli_num_rows($result_hewan) > 0): ?>
                    <form method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pilih</th>
                                        <th>Jenis</th>
                                        <th>Nomor</th>
                                        <th class="text-end">Nominal/Orang</th>
                                        <th class="text-center">Ketersediaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row_hewan = mysqli_fetch_assoc($result_hewan)):
                                        $max_peserta_hewan = ($row_hewan['jenis_hewan'] == 'sapi') ? 7 : 1;
                                        $is_disabled = ($row_hewan['jumlah_peserta'] >= $max_peserta_hewan);
                                    ?>
                                    <tr class="<?php echo $is_disabled ? 'table-light text-muted' : ''; ?>">
                                        <td class="text-center align-middle">
                                            <input class="form-check-input" type="radio" name="id_hewan" value="<?php echo $row_hewan['id_hewan']; ?>" id="hewan_<?php echo $row_hewan['id_hewan']; ?>" <?php if ($is_disabled) echo 'disabled'; ?> required>
                                        </td>
                                        <td class="align-middle"><?php echo ucfirst(htmlspecialchars($row_hewan['jenis_hewan'])); ?></td>
                                        <td class="align-middle"><?php echo htmlspecialchars($row_hewan['nomor_hewan']); ?></td>
                                        <td class="text-end align-middle fw-bold"><?php echo rupiah(($row_hewan['jenis_hewan'] == 'sapi') ? 3000000 : ($row_hewan['harga_hewan'] + $row_hewan['biaya_admin'])); ?></td>
                                        <td class="text-center align-middle">
                                            <?php echo $row_hewan['jumlah_peserta']; ?> / <?php echo $max_peserta_hewan; ?> Peserta
                                            <?php if($is_disabled): ?>
                                                <span class="badge badge-danger ms-2">Penuh</span>
                                            <?php else: ?>
                                                <span class="badge badge-success ms-2">Tersedia</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Daftar Qurban Sekarang</button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-info">Belum ada hewan qurban yang tersedia untuk pendaftaran.</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-warning">Pendaftaran belum dibuka karena tidak ada periode qurban yang aktif.</div>
            <?php endif; ?>
        </div>
    </main>
    <?php include_once '../src/components/footer.php'; ?>
</body>
</html>