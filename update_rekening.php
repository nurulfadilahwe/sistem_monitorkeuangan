<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id     = (int)$_POST['id_rekening'];
    $sub    = (int)$_POST['id_subkegiatan'];
    $kode   = $k->real_escape_string($_POST['kode_rekening']);
    $nama   = $k->real_escape_string($_POST['nama_rekening']);
    $aktif  = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    /**
     * 1️⃣ CEK DUPLIKASI KODE REKENING
     */
    $cekKode = $k->query("
        SELECT id_rekening FROM rekening 
        WHERE kode_rekening='$kode' AND id_rekening<>$id
        LIMIT 1
    ");

    if ($cekKode && $cekKode->num_rows > 0) {
        $_SESSION['flash_msg'] = "Gagal! Kode rekening sudah digunakan.";
        header("Location: form_edit_rekening.php?id=$id");
        exit;
    }

    /**
     * 2️⃣ CEK DUPLIKASI NAMA REKENING DALAM SUBKEGIATAN YANG SAMA
     */
    $cekNama = $k->query("
        SELECT id_rekening FROM rekening 
        WHERE nama_rekening='$nama' 
          AND id_subkegiatan=$sub
          AND id_rekening<>$id
        LIMIT 1
    ");

    if ($cekNama && $cekNama->num_rows > 0) {
        $_SESSION['flash_msg'] = "Gagal! Nama rekening sudah ada di subkegiatan ini.";
        header("Location: form_edit_rekening.php?id=$id");
        exit;
    }

    /**
     * 3️⃣ UPDATE DATA
     */
    $sql = "
        UPDATE rekening SET 
            id_subkegiatan = $sub,
            kode_rekening = '$kode',
            nama_rekening = '$nama',
            is_active = $aktif
        WHERE id_rekening = $id
    ";

    if ($k->query($sql)) {
        $_SESSION['flash_msg'] = "Rekening berhasil diperbarui.";
    } else {
        $_SESSION['flash_msg'] = "Gagal update rekening: " . $k->error;
    }

    header("Location: manajemen_rekening.php");
    exit;

} else {
    $_SESSION['flash_msg'] = "Akses tidak valid.";
    header("Location: manajemen_rekening.php");
    exit;
}