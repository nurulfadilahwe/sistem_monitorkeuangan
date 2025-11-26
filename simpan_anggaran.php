<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB fail: ".$k->connect_error); }

$id_rekening   = $_POST['id_rekening'] ?? 0;
$tahun         = $_POST['tahun'] ?? 0;
$bulan         = $_POST['bulan'] ?? 0;
$jenis         = $_POST['jenis'] ?? 'murni';
$nilai_bulanan = $_POST['nilai_bulanan'] ?? 0;

if (!$id_rekening || !$tahun || !$bulan) {
    die("Input tidak lengkap.");
}

$cek = $k->query("SELECT is_active FROM rekening WHERE id_rekening=$id_rekening");
if (!$cek || $cek->fetch_assoc()['is_active'] != 1) {
    die("Rekening ini sudah tidak aktif, tidak bisa digunakan.");
}

$stmt = $k->prepare("INSERT INTO anggaran (id_rekening, tahun, bulan, jenis, nilai_bulanan)
                    VALUES (?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE nilai_bulanan=VALUES(nilai_bulanan), jenis=VALUES(jenis)");
$stmt->bind_param("iiisd", $id_rekening, $tahun, $bulan, $jenis, $nilai_bulanan);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo "<script>alert('Anggaran tersimpan.'); window.location='laporan.php?tahun=$tahun&bulan=$bulan';</script>";
} else {
    echo "Gagal simpan anggaran: ".$k->error;
}
