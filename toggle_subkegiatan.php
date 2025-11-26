<?php
session_start();
include 'koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$res = $k->query("SELECT is_active FROM subkegiatan WHERE id_subkegiatan=$id");
if (!$res || $res->num_rows==0) { $_SESSION['flash_msg'] = 'Subkegiatan tidak ditemukan'; header('Location: manajemen_subkegiatan.php'); exit; }
$row = $res->fetch_assoc();
$new = $row['is_active'] ? 0 : 1;
$k->query("UPDATE subkegiatan SET is_active=$new WHERE id_subkegiatan=$id");
$_SESSION['flash_msg'] = $new ? 'Subkegiatan berhasil diaktifkan' : 'Subkegiatan berhasil dinonaktifkan';
header('Location: manajemen_subkegiatan.php');
exit;
