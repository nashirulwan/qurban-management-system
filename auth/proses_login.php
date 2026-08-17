<?php
require_once '../config/database.php';
require_once '../function/helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$login_identifier = trim((string) ($_POST['login_identifier'] ?? ''));
$password_dari_form = (string) ($_POST['password'] ?? '');

if ($login_identifier === '' || $password_dari_form === '') {
    header('Location: login.php?error=wrong');
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    'SELECT nik, username, password, nama_lengkap, is_active, is_admin, is_panitia, is_warga, is_berqurban
     FROM users
     WHERE nik = ? OR username = ?
     LIMIT 1'
);

if (!$stmt) {
    error_log('Login query preparation failed: ' . mysqli_error($conn));
    header('Location: login.php?error=wrong');
    exit();
}

mysqli_stmt_bind_param($stmt, 'ss', $login_identifier, $login_identifier);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

if (!$user || empty($user['password']) || !password_verify($password_dari_form, $user['password'])) {
    header('Location: login.php?error=wrong');
    exit();
}

if ((int) $user['is_active'] !== 1) {
    header('Location: login.php?error=inactive');
    exit();
}

session_regenerate_id(true);
$_SESSION['nik'] = $user['nik'];
$_SESSION['username'] = $user['username'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['is_admin'] = (bool) $user['is_admin'];
$_SESSION['is_panitia'] = (bool) $user['is_panitia'];
$_SESSION['is_warga'] = (bool) $user['is_warga'];
$_SESSION['is_berqurban'] = (bool) $user['is_berqurban'];

if ($_SESSION['is_admin']) {
    $redirect = '../admin/index.php';
} elseif ($_SESSION['is_panitia']) {
    $redirect = '../panitia/index.php';
} elseif ($_SESSION['is_berqurban']) {
    $redirect = '../berqurban/index.php';
} elseif ($_SESSION['is_warga']) {
    $redirect = '../warga/index.php';
} else {
    $redirect = '../index.php';
}

header('Location: ' . $redirect);
exit();
