<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'];

$k->query("DELETE FROM realisasi_detail WHERE id_detail='$id'");

$_SESSION['flash_msg'] = "Realisasi berhasil dihapus!";
header("Location: histori.php");
