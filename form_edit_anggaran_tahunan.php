<?php
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB fail: ".$k->connect_error); }

$id = $_GET['id'] ?? 0;
if (!$id) { die("ID tidak valid."); }

// Ambil data anggaran
$sql = "
    SELECT *
    FROM anggaran_tahunan
    WHERE id = $id
";
$ang = $k->query($sql)->fetch_assoc();
if (!$ang) { die("Data tidak ditemukan."); }

// Data rekening
$rek = $k->query("
    SELECT id_rekening,
           CONCAT(kode_rekening, ' - ', nama_rekening) AS rek
    FROM rekening
    WHERE is_active = 1
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
<title>Edit Anggaran Tahunan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
<div class="card shadow">
    <div class="card-header bg-warning">Edit Anggaran Tahunan</div>
    <div class="card-body">

<form action="update_anggaran_tahunan.php" method="post" class="row g-3">

    <input type="hidden" name="id" value="<?= $ang['id'] ?>">

    <div class="col-12">
        <label class="form-label">Rekening</label>
        <select class="form-select" name="id_rekening" required>
            <?php while($r=$rek->fetch_assoc()): ?>
            <option value="<?= $r['id_rekening'] ?>"
                <?= $r['id_rekening']==$ang['id_rekening'] ? 'selected' : '' ?>>
                <?= $r['rek'] ?>
            </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Tahun</label>
        <input type="number" class="form-control" name="tahun"
               value="<?= $ang['tahun'] ?>" required>
    </div>

    <div class="col-md-2">
        <label class="form-label">Bulan Mulai</label>
        <select class="form-select" name="bulan_mulai" required>
            <?php foreach($namaBulan as $i=>$n): ?>
            <option value="<?= $i ?>" <?= $i==$ang['bulan_mulai']?'selected':'' ?>>
                <?= $n ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Bulan Selesai</label>
        <select class="form-select" name="bulan_selesai" required>
            <?php foreach($namaBulan as $i=>$n): ?>
            <option value="<?= $i ?>" <?= $i==$ang['bulan_selesai']?'selected':'' ?>>
                <?= $n ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Jenis</label>
        <select class="form-select" name="jenis" required>
            <option value="murni"      <?= $ang['jenis']=='murni'?'selected':'' ?>>Murni</option>
            <option value="pergeseran" <?= $ang['jenis']=='pergeseran'?'selected':'' ?>>Pergeseran</option>
            <option value="perubahan"  <?= $ang['jenis']=='perubahan'?'selected':'' ?>>Perubahan</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Nilai Tahunan (Rp)</label>
        <input type="number" class="form-control"
               name="nilai_tahunan" value="<?= $ang['nilai_tahunan'] ?>" required>
    </div>

    <div class="col-12 d-flex justify-content-between">
        <a href="histori.php" class="btn btn-secondary">Kembali</a>
        <button class="btn btn-warning">Simpan Perubahan</button>
    </div>

</form>

    </div>
</div>
</div>

</body>
</html>
