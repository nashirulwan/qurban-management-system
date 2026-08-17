<?php
require_once '../config/database.php';
require_once '../function/helper.php';

// Logika pendaftaran dipindahkan ke proses_register.php
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Aktivasi Akun - Sistem Qurban</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/main.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
    <style>
        .nik-suggestion-box {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            background: white;
            z-index: 1000;
        }
        .nik-suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .nik-suggestion-item:hover {
            background-color: #f8f9fa;
        }
        .nik-suggestion-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <main class="main-content d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="container">
            <div class="d-flex justify-content-center">
                <div class="card" style="max-width: 480px; width: 100%;">
                    <div class="card-header text-center">
                        <h1 class="card-title">Aktivasi Akun Warga</h1>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="proses_register.php" id="registerForm">
                            <div class="form-group mb-3">
                                <label for="nik" class="form-label">Cari dan Pilih NIK Anda</label>
                                <input type="text" id="nik" name="nik" class="form-control" placeholder="Ketik NIK atau nama untuk mencari..." required autocomplete="off">
                                <div id="nik-suggestions" class="nik-suggestion-box mt-1 d-none"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="username" class="form-label">Buat Username</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Buat Password Baru</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group mb-4">
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block w-100">Aktifkan Akun</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        Sudah punya akun? <a href="login.php">Login di sini</a>.
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const nikInput = document.getElementById('nik');
        const suggestionBox = document.getElementById('nik-suggestions');
        let nikData = [];

        // Ambil data NIK dari API
        fetch('../api/get_unregistered_niks.php')
            .then(response => response.json())
            .then(data => {
                nikData = data;
            })
            .catch(error => {
                console.error('Error fetching NIK data:', error);
            });

        nikInput.addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            suggestionBox.innerHTML = '';

            if (query.length < 2) {
                suggestionBox.classList.add('d-none');
                return;
            }

            const filteredData = nikData.filter(item => {
                return item.nik.includes(query) ||
                       item.nama_lengkap.toLowerCase().includes(query);
            });

            if (filteredData.length > 0) {
                suggestionBox.classList.remove('d-none');
                filteredData.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'nik-suggestion-item';
                    div.textContent = `${item.nik} - ${item.nama_lengkap}`;
                    div.addEventListener('click', function() {
                        nikInput.value = item.nik;
                        suggestionBox.classList.add('d-none');
                    });
                    suggestionBox.appendChild(div);
                });
            } else {
                suggestionBox.classList.add('d-none');
            }
        });

        // Tutup suggestion box ketika klik di luar
        document.addEventListener('click', function(e) {
            if (e.target.id !== 'nik') {
                suggestionBox.classList.add('d-none');
            }
        });

        // Validasi form sebelum submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
            }
        });
    });
    </script>
</body>
</html>