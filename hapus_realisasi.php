<?php
session_start();
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB Fail : ".$k->connect_error); }

$id = $_GET['id'];

$k->query("DELETE FROM realisasi_detail WHERE id_detail='$id'");

$_SESSION['flash_msg'] = "Realisasi berhasil dihapus!";
header("Location: histori.php");
