<?php
include 'koneksi.php';

$id_rekening   = $_POST['id_rekening']   ?? 0;
$tahun         = $_POST['tahun']         ?? 0;
$bulan_mulai   = $_POST['bulan_mulai']   ?? 1;
$bulan_selesai = $_POST['bulan_selesai'] ?? 12;
$jenis         = $_POST['jenis']         ?? 'murni';
$nilai_tahunan = $_POST['nilai_tahunan'] ?? 0;

if (!$id_rekening || !$tahun) {
    die("Input tidak lengkap.");
}

// --- Cek rekening aktif ---
$cek = $k->query("SELECT is_active FROM rekening WHERE id_rekening=$id_rekening");
if (!$cek || $cek->fetch_assoc()['is_active'] != 1) {
    die("Rekening ini sudah tidak aktif, tidak bisa digunakan.");
}

// --- Simpan data ---
$stmt = $k->prepare("
    INSERT INTO anggaran_tahunan 
        (id_rekening, tahun, bulan_mulai, bulan_selesai, jenis, nilai_tahunan)
    VALUES (?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE 
        bulan_mulai=VALUES(bulan_mulai),
        bulan_selesai=VALUES(bulan_selesai),
        jenis=VALUES(jenis),
        nilai_tahunan=VALUES(nilai_tahunan)
");

$stmt->bind_param("iiiisd", 
    $id_rekening, 
    $tahun, 
    $bulan_mulai, 
    $bulan_selesai, 
    $jenis, 
    $nilai_tahunan
);

$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo "<script>alert('Anggaran tahunan tersimpan.'); window.location='laporan.php?tahun=$tahun';</script>";
} else {
    echo "Gagal simpan: ".$k->error;
}