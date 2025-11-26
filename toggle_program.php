<?php
session_start();
include 'koneksi.php';

$id = (int)$_GET['id'];

// Ambil status saat ini
$row = $k->query("SELECT is_active FROM program WHERE id_program = $id")->fetch_assoc();

// Toggle nilai 1 → 0 atau 0 → 1
$new = $row['is_active'] ? 0 : 1;

// Update status
$k->query("UPDATE program SET is_active = $new WHERE id_program = $id");

// Flash message
$_SESSION['flash_msg'] = $new ? "Program diaktifkan" : "Program dinonaktifkan";

// Kembali ke manajemen program
header("Location: manajemen_program.php");
exit;
