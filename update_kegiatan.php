<?php
session_start();
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB fail: ".$k->connect_error); }

$id     = (int)$_POST['id_kegiatan'];
$kode   = $k->real_escape_string($_POST['kode']);
$nama   = $k->real_escape_string($_POST['nama']);
$active = (int)$_POST['is_active'];

$cek = $k->query("
    SELECT id_kegiatan FROM kegiatan
    WHERE (kode_kegiatan='$kode' OR nama_kegiatan='$nama')
      AND id_kegiatan <> $id
    LIMIT 1
");

if ($cek->num_rows > 0) {
    $_SESSION['flash_msg'] = "Gagal! Kode atau nama kegiatan sudah dipakai.";
    header("Location: form_edit_kegiatan.php?id=$id");
    exit;
}

$q = $k->query("
    UPDATE kegiatan SET 
        kode_kegiatan='$kode',
        nama_kegiatan='$nama',
        is_active=$active
    WHERE id_kegiatan=$id
");

$_SESSION['flash_msg'] = $q
    ? "Kegiatan berhasil diperbarui."
    : "Gagal update kegiatan: ".$k->error;

header("Location: manajemen_kegiatan.php");
exit;
