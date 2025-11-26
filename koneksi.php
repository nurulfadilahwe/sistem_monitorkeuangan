<?php
// ---- Ambil ENV dari Railway ----
$host = getenv('DB_HOST') ?: 'mysql.railway.internal';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'rBJTeTJsXakbtmACCEvWYBBDKAJhPSUH';
$name = getenv('DB_NAME') ?: 'railway';
$port = getenv('DB_PORT') ?: 3306;

// ---- Koneksi MySQL ----
$k = new mysqli($host, $user, $pass, $name, $port);

if ($k->connect_error) {
    die("DB Connection Failed: " . $k->connect_error);
}
?>