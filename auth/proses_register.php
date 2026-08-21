<?php
require_once '../config/database.php';
require_once '../function/helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_request();
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi dasar
    if (empty($nik) || empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Semua kolom wajib diisi.";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Password dan konfirmasi password tidak cocok.";
        header("Location: register.php");
        exit();
    }

    // 1. Cek apakah NIK ada dan belum terdaftar
    $query_cek_nik = "SELECT * FROM users WHERE nik = '$nik' AND username IS NULL AND is_active = 0";
    $result_nik = mysqli_query($conn, $query_cek_nik);
    if (mysqli_num_rows($result_nik) == 0) {
        $_SESSION['error_message'] = "NIK tidak ditemukan atau sudah diaktifkan.";
        header("Location: register.php");
        exit();
    }

    // 2. Cek apakah username sudah digunakan
    $query_cek_username = "SELECT nik FROM users WHERE username = '$username'";
    $result_username = mysqli_query($conn, $query_cek_username);
    if (mysqli_num_rows($result_username) > 0) {
        $_SESSION['error_message'] = "Username sudah digunakan. Silakan pilih username lain.";
        header("Location: register.php");
        exit();
    }

    // 3. Jika semua valid, update data user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query_update = "UPDATE users SET
                        username = '$username',
                        password = '$hashed_password',
                        is_active = 1
                     WHERE nik = '$nik'";

    if (mysqli_query($conn, $query_update)) {
        $_SESSION['success_message'] = "Akun Anda berhasil diaktifkan! Silakan login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan pada database. Silakan coba lagi.";
        header("Location: register.php");
        exit();
    }

} else {
    header("Location: register.php");
    exit();
}
?>