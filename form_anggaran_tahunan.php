<?php
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB fail: ".$k->connect_error); }

$sql = "SELECT r.id_rekening,
            CONCAT(p.nama_program,' › ',keg.nama_kegiatan,' › ',sub.nama_subkegiatan,' › ',r.kode_rekening,' - ',r.nama_rekening) AS label
        FROM rekening r
        JOIN subkegiatan sub ON sub.id_subkegiatan = r.id_subkegiatan
        JOIN kegiatan keg ON keg.id_kegiatan = sub.id_kegiatan
        JOIN program p ON p.id_program = keg.id_program
        WHERE r.is_active = 1
        ORDER BY p.nama_program, keg.nama_kegiatan, sub.nama_subkegiatan, r.kode_rekening";
$rek = $k->query($sql);

$tahun = date('Y');

$namaBulan = [
    1=>"Januari", 2=>"Februari", 3=>"Maret", 4=>"April", 5=>"Mei", 6=>"Juni",
    7=>"Juli", 8=>"Agustus", 9=>"September", 10=>"Oktober", 11=>"November", 12=>"Desember"
];
?>


<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Input Anggaran Tahunan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
<div class="card shadow">
    <div class="card-header bg-success text-white">Input Anggaran Tahunan</div>
    <div class="card-body">
    <form action="simpan_anggaran_tahunan.php" method="post" class="row g-3">
        <div class="col-12">
            <label class="form-label">Rekening</label>
            <select class="form-select" name="id_rekening" id="rekSelect" required>
                <option value="">-- Pilih Rekening --</option>
                <?php while($r = $rek->fetch_assoc()): ?>
                <option value="<?= $r['id_rekening'] ?>"><?= htmlspecialchars($r['label']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Tahun</label>
            <input type="number" class="form-control" name="tahun" value="<?= $tahun ?>" required>
        </div>

        <div class="col-md-2">
            <label class="form-label">Bulan Mulai</label>
            <select class="form-select" name="bulan_mulai" required>
                <?php for($i=1;$i<=12;$i++): ?>
                <option value="<?= $i ?>"><?= $namaBulan[$i] ?></option>
                <?php endfor; ?>
            </select>
            </div>
            <div class="col-md-2">
            <label class="form-label">Bulan Selesai</label>
            <select class="form-select" name="bulan_selesai" required>
                <?php for($i=1;$i<=12;$i++): ?>
                <option value="<?= $i ?>"><?= $namaBulan[$i] ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Jenis</label>
            <select class="form-select" name="jenis" required>
                <option value="murni">Murni</option>
                <option value="pergeseran">Pergeseran</option>
                <option value="perubahan">Perubahan</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Nilai Anggaran Tahunan (Rp)</label>
            <input type="number" class="form-control" name="nilai_tahunan" min="0" step="1" required>
        </div>

        <div class="col-12 d-flex justify-content-between">
            <a href="index.php" class="btn btn-secondary">Kembali</a>
            <button class="btn btn-success">Simpan</button>
        </div>
    </form>
    </div>
</div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function(){
    $('#rekSelect').select2({placeholder:'Cari rekening...', width:'100%'});
});
</script>
</body>
</html>
