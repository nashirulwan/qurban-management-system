<?php
// file: panitia/proses_update_pembayaran_qurban.php

require_once '../config/database.php';
require_once '../function/helper.php';

cek_login();
cek_role('panitia');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_pembayaran_qurban.php');
    exit;
}

$id_peserta = filter_input(INPUT_POST, 'id_peserta', FILTER_VALIDATE_INT);
$status_baru = filter_input(INPUT_POST, 'status_baru', FILTER_SANITIZE_STRING);

if (!$id_peserta || !in_array($status_baru, ['belum_bayar', 'dp', 'lunas'])) {
    $_SESSION['error_message'] = 'Data tidak valid!';
    header('Location: kelola_pembayaran_qurban.php');
    exit;
}

// Update status pembayaran
$query = "UPDATE peserta_qurban SET status_bayar = ? WHERE id_peserta = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'si', $status_baru, $id_peserta);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success_message'] = 'Status pembayaran berhasil diperbarui!';
} else {
    $_SESSION['error_message'] = 'Gagal memperbarui status pembayaran!';
}

header('Location: kelola_pembayaran_qurban.php');
exit;
?>