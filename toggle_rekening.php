<?php
session_start();
include 'koneksi.php';

$id = (int)$_GET['id'];

// Ambil status lama
$res = $k->query("SELECT is_active FROM rekening WHERE id_rekening=$id");
$row = $res->fetch_assoc();
$newStatus = $row['is_active'] ? 0 : 1;

// Update status
$k->query("UPDATE rekening SET is_active=$newStatus WHERE id_rekening=$id");

// Redirect dengan pesan
$_SESSION['flash_msg'] = $newStatus ? "Rekening berhasil diaktifkan" : "Rekening berhasil dinonaktifkan";
header("Location: manajemen_rekening.php");
exit;
