<?php
session_start();
// Jika sudah login, redirect ke halaman index utama yang akan mengarahkan sesuai role
if (isset($_SESSION['nik'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login - Sistem Qurban</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
</head>
<body>
    <main class="main-content d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="container">
            <div class="d-flex justify-content-center">
                <div class="card" style="max-width: 480px; width: 100%;">
                    <div class="card-header text-center">
                        <h1 class="card-title">Login Sistem</h1>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success">
                                <?php
                                echo $_SESSION['success_message'];
                                unset($_SESSION['success_message']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger">
                                <?php
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="proses_login.php">
                            <div class="form-group mb-3">
                                <label for="login_identifier" class="form-label">NIK atau Username</label>
                                <input type="text" id="login_identifier" name="login_identifier" class="form-control" required autofocus>
                            </div>
                            <div class="form-group mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block w-100">Login</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        Belum punya akun? <a href="register.php">Aktifkan akun Anda di sini</a>.
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
