<?php
session_start();
require_once 'config/database.php';

// Function to format currency
function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

$session_roles = [];
foreach (['admin' => 'Admin', 'panitia' => 'Panitia', 'berqurban' => 'Peserta Qurban', 'warga' => 'Warga'] as $role_key => $role_name) {
    if (!empty($_SESSION['is_' . $role_key])) {
        $session_roles[] = $role_name;
    }
}
$role_label = implode(', ', $session_roles) ?: 'Pengguna';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Sistem Qurban RT 001</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/styles/main.css">
    <!-- Add favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z'/%3E%3Cpath d='m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65'/%3E%3Cpath d='m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65'/%3E%3C/svg%3E">
</head>
<body>
    <!-- Include the header -->
    <?php include_once 'src/components/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Welcome Banner -->
            <div class="card animate-on-scroll bg-primary" style="border-radius: var(--radius-lg); color: white;">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1 style="color: white; margin-bottom: var(--space-2);">Sistem Manajemen Qurban RT 001</h1>
                        <p class="mb-3" style="opacity: 0.9;">Desa AAAA</p>

                        <?php if (isset($_SESSION['nik'])): ?>
                            <div class="d-flex gap-2 align-items-center mb-3">
                                <div class="bg-white p-2 rounded" style="color: var(--primary-dark);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div>
                                    <p style="margin: 0; font-weight: 500;">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p style="margin: 0; font-size: 0.875rem; opacity: 0.9;">Role: <?php echo htmlspecialchars($role_label, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <?php if (!empty($_SESSION['is_admin'])): ?>
                                    <a href="admin/index.php" class="btn btn-light">Dashboard Admin</a>
                                <?php elseif (!empty($_SESSION['is_panitia'])): ?>
                                    <a href="panitia/index.php" class="btn btn-light">Dashboard Panitia</a>
                                <?php elseif (!empty($_SESSION['is_warga'])): ?>
                                    <a href="warga/index.php" class="btn btn-light">Dashboard Warga</a>
                                    <a href="berqurban/daftar_qurban.php" class="btn btn-warning">Daftar Qurban</a>
                                <?php elseif (!empty($_SESSION['is_berqurban'])): ?>
                                    <a href="berqurban/index.php" class="btn btn-light">Dashboard Berqurban</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <a href="auth/login.php" class="btn btn-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10 17 15 12 10 7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                                Login
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-center" style="max-width: 240px; margin-top: var(--space-4);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.2;">
                            <path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
                            <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/>
                            <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Informasi Qurban Tahun Ini -->
            <div class="card animate-on-scroll">
                <div class="card-header">
                    <h2 class="card-title">Informasi Qurban Tahun Ini</h2>
                </div>

                <div class="card-body">
                    <?php
                    $query = "SELECT * FROM periode_qurban WHERE is_active = TRUE ORDER BY id_periode DESC LIMIT 1";
                    $result = mysqli_query($conn, $query);
                    if ($periode = mysqli_fetch_assoc($result)):
                    ?>
                        <div class="d-flex flex-wrap gap-4 mb-4">
                            <div class="p-4 bg-light rounded shadow" style="min-width: 200px;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <h3 style="margin: 0;">Tahun Hijriah</h3>
                                </div>
                                <p style="font-size: 1.25rem; font-weight: 600;" class="text-primary"><?php echo $periode['tahun_hijriah']; ?> H</p>
                            </div>

                            <div class="p-4 bg-light rounded shadow" style="min-width: 200px;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-secondary">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <h3 style="margin: 0;">Tahun Masehi</h3>
                                </div>
                                <p style="font-size: 1.25rem; font-weight: 600;" class="text-secondary"><?php echo $periode['tahun_masehi']; ?> M</p>
                            </div>

                            <div class="p-4 bg-light rounded shadow" style="min-width: 200px;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-accent">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <h3 style="margin: 0;">Tanggal Pelaksanaan</h3>
                                </div>
                                <p style="font-size: 1.25rem; font-weight: 600;" class="text-accent">
                                    <?php echo date('d F Y', strtotime($periode['tanggal_pelaksanaan'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>Informasi periode qurban belum tersedia.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Daftar Hewan Qurban -->
            <div class="card animate-on-scroll">
                <div class="card-header">
                    <h2 class="card-title">Daftar Hewan Qurban</h2>
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
                                    <th class="text-right">Est. Daging (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM hewan_qurban WHERE id_periode = " . ($periode['id_periode'] ?? 0);
                                $result = mysqli_query($conn, $query);
                                $no = 1;

                                if (mysqli_num_rows($result) > 0):
                                    while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge <?php echo $row['jenis_hewan'] == 'sapi' ? 'badge-primary' : 'badge-secondary'; ?>">
                                                <?php echo ucfirst($row['jenis_hewan']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td><?php echo $row['nomor_hewan']; ?></td>
                                    <td class="text-right"><?php echo rupiah($row['harga_hewan']); ?></td>
                                    <td class="text-right"><?php echo rupiah($row['biaya_admin']); ?></td>
                                    <td class="text-right"><?php echo $row['estimasi_daging']; ?> kg</td>
                                </tr>
                                <?php
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data hewan qurban</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (!isset($_SESSION['nik'])): ?>
                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        <a href="auth/login.php" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                            Login untuk Daftar Qurban
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Include the footer -->
    <?php include_once 'src/components/footer.php'; ?>

    <!-- Include main JavaScript file -->
    <script src="src/js/main.js"></script>
</body>
</html>