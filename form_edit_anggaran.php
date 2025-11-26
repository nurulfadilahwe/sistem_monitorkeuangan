<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'] ?? 0;
if (!$id) { die("ID tidak valid."); }

// Ambil Data Anggaran Bulanan
$q = $k->query("
    SELECT a.*, r.kode_rekening, r.nama_rekening
    FROM anggaran a 
    JOIN rekening r ON r.id_rekening = a.id_rekening
    WHERE a.id_anggaran = '$id'
");

if ($q->num_rows == 0) {
    die("Data tidak ditemukan");
}

$data = $q->fetch_assoc();

// Ambil Daftar Rekening
$rekening = $k->query("
    SELECT id_rekening,
           CONCAT(kode_rekening, ' - ', nama_rekening) AS rek
    FROM rekening
    WHERE is_active = 1
    ORDER BY kode_rekening ASC
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
<title>Edit Anggaran Bulanan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        Edit Anggaran Bulanan
    </div>

    <div class="card-body">

<form action="update_anggaran.php" method="post" class="row g-3">

    <input type="hidden" name="id_anggaran" value="<?= $data['id_anggaran']; ?>">

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
        <label class="form-label">Jenis</label>
        <select name="jenis" class="form-select">
            <option value="belanja" <?= $data['jenis']=='belanja'?'selected':'' ?>>Belanja</option>
            <option value="pendapatan" <?= $data['jenis']=='pendapatan'?'selected':'' ?>>Pendapatan</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Nilai Bulanan (Rp)</label>
        <input type="number" name="nilai_bulanan" class="form-control"
               value="<?= $data['nilai_bulanan'] ?>" required>
    </div>

    <div class="col-12 d-flex justify-content-between">
        <a href="histori.php" class="btn btn-secondary">Kembali</a>
        <button class="btn btn-primary">Simpan Perubahan</button>
    </div>

</form>

    </div>
</div>

</div>

</body>
</html>