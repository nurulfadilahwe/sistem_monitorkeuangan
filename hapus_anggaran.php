<?php
session_start();
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB Fail : ".$k->connect_error); }

$id = $_GET['id'];

$k->query("DELETE FROM anggaran WHERE id_anggaran='$id'");

$_SESSION['flash_msg'] = "Anggaran bulanan berhasil dihapus!";
header("Location: histori.php");
