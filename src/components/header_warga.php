<?php
// file: src/components/header_warga.php (Revisi Total Navigasi)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="navbar">
  <div class="container navbar-container">
    <a href="index.php" class="navbar-brand">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
        <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/>
        <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>
      </svg>
      <span>Warga RT 001</span>
    </a>
    <nav class="navbar-nav">
        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Dashboard</a>

        <?php if (!empty($_SESSION['is_panitia']) || !empty($_SESSION['is_berqurban'])): ?>
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" href="#">Pindah Role</a>
            <ul class="dropdown-menu">
                <?php if (!empty($_SESSION['is_panitia'])): ?><li><a class="dropdown-item" href="../panitia/index.php">Dashboard Panitia</a></li><?php endif; ?>
                <?php if (!empty($_SESSION['is_berqurban'])): ?><li><a class="dropdown-item" href="../berqurban/index.php">Dashboard Berqurban</a></li><?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>

        <a href="../auth/logout.php" class="nav-link btn btn-outline-primary">Logout</a>
    </nav>
  </div>
</header>
<?php