<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'];

$k->query("DELETE FROM anggaran WHERE id_anggaran='$id'");

$_SESSION['flash_msg'] = "Anggaran bulanan berhasil dihapus!";
header("Location: histori.php");
