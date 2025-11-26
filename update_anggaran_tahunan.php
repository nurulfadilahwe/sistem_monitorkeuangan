<?php
session_start();
include 'koneksi.php';

$id            = $_POST['id'] ?? 0;
$id_rekening   = $_POST['id_rekening'] ?? 0;
$tahun         = $_POST['tahun'] ?? 0;
$bulan_mulai   = $_POST['bulan_mulai'] ?? 0;
$bulan_selesai = $_POST['bulan_selesai'] ?? 0;
$jenis         = $_POST['jenis'] ?? '';
$nilai         = $_POST['nilai_tahunan'] ?? 0;

// VALIDASI
if ($bulan_mulai > $bulan_selesai) {
    $_SESSION['flash_msg'] = "Bulan mulai tidak boleh lebih besar dari bulan selesai!";
    header("Location: form_edit_anggaran_tahunan.php?id=$id");
    exit;
}

$cek = $k->query("
    SELECT id FROM anggaran_tahunan
    WHERE id_rekening=$id_rekening
          AND tahun=$tahun
          AND jenis='$jenis'
          AND id<>$id
");
if ($cek->num_rows>0) {
    $_SESSION['flash_msg'] = "Data anggaran untuk rekening/jns/tahun tersebut sudah ada!";
    header("Location: form_edit_anggaran_tahunan.php?id=$id");
    exit;
}

// UPDATE
$stmt = $k->prepare("
    UPDATE anggaran_tahunan
    SET id_rekening=?, tahun=?, bulan_mulai=?, bulan_selesai=?, jenis=?, nilai_tahunan=?
    WHERE id=?
");
$stmt->bind_param("iiiisdi",
    $id_rekening, $tahun, $bulan_mulai, $bulan_selesai, $jenis, $nilai, $id
);

if ($stmt->execute()) {
    $_SESSION['flash_msg'] = "Anggaran tahunan berhasil diperbarui.";
} else {
    $_SESSION['flash_msg'] = "Gagal update: ".$k->error;
}
header("Location: histori.php");
