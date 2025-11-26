<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $k->query("DELETE FROM anggaran_tahunan WHERE id = $id");
    $_SESSION['flash_msg'] = "Anggaran Tahunan berhasil dihapus.";
}
header("Location: histori.php");