<?php
session_start();
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { 
    die("DB fail: ".$k->connect_error); 
}

$id_sub   = (int)$_POST['id_subkegiatan'];
$id_keg   = (int)$_POST['id_kegiatan'];
$kode     = $k->real_escape_string($_POST['kode_subkegiatan']);
$nama     = $k->real_escape_string($_POST['nama_subkegiatan']);
$active   = (int)$_POST['is_active'];

$stmt = $k->prepare("
    SELECT id_subkegiatan 
    FROM subkegiatan
    WHERE (kode_subkegiatan=? OR nama_subkegiatan=?)
      AND id_kegiatan=?
      AND id_subkegiatan<>?
    LIMIT 1
");
$stmt->bind_param("ssii", $kode, $nama, $id_keg, $id_sub);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['flash_msg'] = "Gagal! Kode atau nama subkegiatan sudah dipakai.";
    header("Location: form_edit_subkegiatan.php?id=$id_sub");
    exit;
}

$stmt2 = $k->prepare("
    UPDATE subkegiatan
    SET id_kegiatan=?, 
        kode_subkegiatan=?,
        nama_subkegiatan=?,
        is_active=?
    WHERE id_subkegiatan=?
");
$stmt2->bind_param("issii", $id_keg, $kode, $nama, $active, $id_sub);

if ($stmt2->execute()) {
    $_SESSION['flash_msg'] = "Subkegiatan berhasil diperbarui.";
} else {
    $_SESSION['flash_msg'] = "Gagal mengupdate: ".$stmt2->error;
}

header("Location: manajemen_subkegiatan.php");
exit;