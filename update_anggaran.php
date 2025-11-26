<?php
session_start();
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB Fail : ".$k->connect_error); }

$id = $_POST['id_anggaran'];
$rek = $_POST['id_rekening'];
$tahun = $_POST['tahun'];
$bulan = $_POST['bulan'];
$jenis = $_POST['jenis'];
$nilai = $_POST['nilai_bulanan'];

if ($nilai < 0) {
    $_SESSION['flash_msg'] = "Nilai tidak boleh kurang dari 0!";
    header("Location: form_edit_anggaran.php?id=".$id);
    exit;
}

$k->query("
    UPDATE anggaran SET
        id_rekening='$rek',
        tahun='$tahun',
        bulan='$bulan',
        jenis='$jenis',
        nilai_bulanan='$nilai'
    WHERE id_anggaran='$id'
");

$_SESSION['flash_msg'] = "Perubahan anggaran bulanan berhasil disimpan!";
header("Location: histori.php");
