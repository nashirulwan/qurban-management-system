<?php
/**
 * Database connection.
 *
 * Configure the connection through environment variables:
 * DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, and DB_PASSWORD.
 */

mysqli_report(MYSQLI_REPORT_OFF);

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_port = (int) (getenv('DB_PORT') ?: 3306);
$db_database = getenv('DB_DATABASE') ?: 'db_qurban';
$db_username = getenv('DB_USERNAME') ?: '';
$db_password = getenv('DB_PASSWORD') ?: '';

if ($db_username === '') {
    http_response_code(500);
    error_log('Database configuration is missing DB_USERNAME.');
    exit('Database configuration is incomplete. Check your environment settings.');
}

$conn = mysqli_init();
if (!mysqli_real_connect($conn, $db_host, $db_username, $db_password, $db_database, $db_port)) {
    http_response_code(500);
    error_log('Database connection failed: ' . mysqli_connect_error());
    exit('Database connection unavailable. Check your local database settings.');
}

mysqli_set_charset($conn, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
