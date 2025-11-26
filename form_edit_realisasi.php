<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'] ?? 0;
if (!$id) die("ID tidak valid.");

// Ambil data realisasi
$q = $k->query("
    SELECT rd.*, r.kode_rekening, r.nama_rekening
    FROM realisasi_detail rd
    JOIN rekening r ON r.id_rekening = rd.id_rekening
    WHERE rd.id_detail = '$id'
");

if ($q->num_rows == 0) die("Data tidak ditemukan");

$data = $q->fetch_assoc();

// Daftar rekening
$rekening = $k->query("
    SELECT id_rekening,
           CONCAT(kode_rekening,' - ',nama_rekening) AS rek
    FROM rekening
    WHERE is_active = 1
    ORDER BY kode_rekening
");

$namaBulan = [
    1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",
    5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",
    9=>"September",10=>"Oktober",11=>"November",12=>"Desember"
];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Realisasi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

<div class="card shadow">
    <div class="card-header bg-success text-white">
        Edit Realisasi
    </div>
    <div class="card-body">

<form action="update_edit_realisasi.php" method="post" class="row g-3">

    <input type="hidden" name="id_detail" value="<?= $data['id_detail'] ?>">

    <div class="col-12">
        <label class="form-label">Rekening</label>
        <select name="id_rekening" class="form-select" required>
            <?php while($r = $rekening->fetch_assoc()): ?>
                <option value="<?= $r['id_rekening'] ?>"
                    <?= $r['id_rekening']==$data['id_rekening']?'selected':'' ?>>
                    <?= $r['rek'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Tahun</label>
        <input type="number" name="tahun" class="form-control"
               value="<?= $data['tahun'] ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Bulan</label>
        <select name="bulan" class="form-select" required>
            <?php foreach($namaBulan as $key=>$val): ?>
                <option value="<?= $key ?>" <?= $key==$data['bulan']?'selected':'' ?>>
                    <?= $val ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control"
               value="<?= $data['tanggal'] ?>" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Jumlah Realisasi (Rp)</label>
        <input type="number" name="jumlah_realisasi" class="form-control"
               value="<?= $data['jumlah_realisasi'] ?>" required>
    </div>

    <div class="col-12 d-flex justify-content-between">
        <a href="histori.php" class="btn btn-secondary">Kembali</a>
        <button class="btn btn-success">Simpan Perubahan</button>
    </div>

</form>

    </div>
</div>

</div>

</body>
</html>