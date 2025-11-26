<?php
session_start();
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB Fail : ".$k->connect_error); }

$id = $_POST['id_detail'];
$rek = $_POST['id_rekening'];
$tahun = $_POST['tahun'];
$bulan = $_POST['bulan'];
$tanggal = $_POST['tanggal'];
$jumlah = $_POST['jumlah_realisasi'];

if ($jumlah < 0) {
    $_SESSION['flash_msg'] = "Jumlah realisasi tidak boleh kurang dari 0!";
    header("Location: form_edit_realisasi.php?id=".$id);
    exit;
}

$k->query("
    UPDATE realisasi_detail SET
        id_rekening='$rek',
        tahun='$tahun',
        bulan='$bulan',
        tanggal='$tanggal',
        jumlah_realisasi='$jumlah'
    WHERE id_detail='$id'
");

$_SESSION['flash_msg'] = "Realisasi berhasil diperbarui!";
header("Location: histori.php");
