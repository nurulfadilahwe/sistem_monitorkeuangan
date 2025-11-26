<?php
// ---- Ambil ENV dari Railway ----
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$name = getenv('DB_NAME') ?: 'monitor_keuangan';
$port = getenv('DB_PORT') ?: 3306;

// ---- Koneksi MySQL ----
$k = new mysqli($host, $user, $pass, $name, $port);

if ($k->connect_error) {
    die("DB Connection Failed: " . $k->connect_error);
}
?>