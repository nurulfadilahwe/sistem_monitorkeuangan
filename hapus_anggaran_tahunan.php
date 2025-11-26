<?php
session_start();
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB fail: ".$k->connect_error); }

$id = $_GET['id'] ?? 0;

if ($id) {
    $k->query("DELETE FROM anggaran_tahunan WHERE id = $id");
    $_SESSION['flash_msg'] = "Anggaran Tahunan berhasil dihapus.";
}
header("Location: histori.php");