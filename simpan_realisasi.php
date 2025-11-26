<?php
include 'koneksi.php';

$id_rekening      = $_POST['id_rekening'] ?? 0;
$tahun            = $_POST['tahun'] ?? 0;
$bulan            = $_POST['bulan'] ?? 0;
$tanggal          = $_POST['tanggal'] ?? date('Y-m-d');
$jumlah_realisasi = floatval($_POST['jumlah_realisasi'] ?? 0);

if (!$id_rekening || !$tahun || !$bulan) {
    die("Input tidak lengkap.");
}

// Pastikan rekening aktif
$cek = $k->query("SELECT is_active FROM rekening WHERE id_rekening=$id_rekening");
if (!$cek || $cek->fetch_assoc()['is_active'] != 1) {
    die("Rekening ini sudah tidak aktif, tidak bisa digunakan.");
}

/**
 * 1️⃣ Ambil anggaran bulanan
 */
$ag = $k->query("
    SELECT nilai_bulanan 
    FROM anggaran 
    WHERE id_rekening = $id_rekening
      AND tahun = $tahun
      AND bulan = $bulan
");
$row = $ag->fetch_assoc();
$nilai_anggaran_bulan = $row['nilai_bulanan'] ?? 0;

/**
 * 2️⃣ Hitung total realisasi bulan ini
 */
$sum = $k->query("
    SELECT SUM(jumlah_realisasi) AS total 
    FROM realisasi_detail
    WHERE id_rekening = $id_rekening
      AND tahun = $tahun
      AND bulan = $bulan
");
$rowSum = $sum->fetch_assoc();
$total_lama = floatval($rowSum['total'] ?? 0);

/**
 * 3️⃣ Validasi — total baru tidak boleh melebihi anggaran
 */
$total_baru = $total_lama + $jumlah_realisasi;

if ($total_baru > $nilai_anggaran_bulan) {
    die("<script>alert('Gagal! Realisasi melebihi anggaran bulanan.'); window.history.back();</script>");
}

/**
 * 4️⃣ Simpan realisasi
 */
$stmt = $k->prepare("
    INSERT INTO realisasi_detail (id_rekening, tahun, bulan, tanggal, jumlah_realisasi)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("iiisd", $id_rekening, $tahun, $bulan, $tanggal, $jumlah_realisasi);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo "<script>alert('Realisasi tersimpan.'); window.location='laporan.php?tahun=$tahun&bulan=$bulan';</script>";
} else {
    echo "Gagal simpan realisasi: ".$k->error;
}