<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Require an authenticated session. */
function cek_login(): void
{
    if (empty($_SESSION['nik'])) {
        header('Location: ' . resolve_base_url() . '/auth/login.php');
        exit();
    }
}

/** Require one role, while allowing admins to access every protected page. */
function cek_role(string $role_required): void
{
    cek_login();

    $allowed_roles = ['admin', 'panitia', 'warga', 'berqurban'];
    if (!in_array($role_required, $allowed_roles, true)) {
        header('Location: ' . resolve_base_url() . '/index.php?error=unauthorized');
        exit();
    }

    if (!empty($_SESSION['is_admin']) || !empty($_SESSION['is_' . $role_required])) {
        return;
    }

    header('Location: ' . resolve_base_url() . '/index.php?error=unauthorized');
    exit();
}

/** Require at least one role from the supplied list. */
function cek_multi_role(array $roles_allowed): void
{
    cek_login();

    if (!empty($_SESSION['is_admin'])) {
        return;
    }

    foreach ($roles_allowed as $role) {
        if (in_array($role, ['panitia', 'warga', 'berqurban'], true) && !empty($_SESSION['is_' . $role])) {
            return;
        }
    }

    header('Location: ' . resolve_base_url() . '/index.php?error=unauthorized');
    exit();
}

/** Return the optional application path, e.g. /qurban when hosted in a subdirectory. */
function resolve_base_url(): string
{
    $base_path = trim((string) (getenv('APP_BASE_PATH') ?: ''));
    if ($base_path === '' || $base_path === '/') {
        return '';
    }

    return '/' . trim($base_path, '/');
}

function generate_qr_code(string $data): string
{
    return 'QR_' . strtoupper(bin2hex(random_bytes(16)));
}

function tampilkan_qr_code(string $data, int $size = 150): string
{
    $encoded_data = urlencode($data);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded_data}";
}

function rupiah($angka): string
{
    if (!is_numeric($angka)) {
        return 'Rp 0';
    }

    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_request(): void
{
    $session_token = (string) ($_SESSION['csrf_token'] ?? '');
    $submitted_token = (string) ($_POST['csrf_token'] ?? '');

    if ($session_token === '' || $submitted_token === '' || !hash_equals($session_token, $submitted_token)) {
        http_response_code(419);
        header('Content-Type: text/plain; charset=UTF-8');
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}
