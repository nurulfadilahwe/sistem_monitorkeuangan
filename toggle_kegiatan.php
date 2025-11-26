<?php
session_start();
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB fail: ".$k->connect_error); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$res = $k->query("SELECT is_active FROM kegiatan WHERE id_kegiatan=$id");
if (!$res || $res->num_rows==0) { $_SESSION['flash_msg'] = 'Kegiatan tidak ditemukan'; header('Location: manajemen_kegiatan.php'); exit; }
$row = $res->fetch_assoc();
$new = $row['is_active'] ? 0 : 1;
$k->query("UPDATE kegiatan SET is_active=$new WHERE id_kegiatan=$id");
$_SESSION['flash_msg'] = $new ? 'Kegiatan berhasil diaktifkan' : 'Kegiatan berhasil dinonaktifkan';
header('Location: manajemen_kegiatan.php');
exit;
