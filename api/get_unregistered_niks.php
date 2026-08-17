<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Ambil semua NIK dan Nama dari warga yang belum aktif (username IS NULL)
$query = "SELECT nik, nama_lengkap FROM users WHERE username IS NULL AND is_active = 0";
$result = mysqli_query($conn, $query);

$data = [];
if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>