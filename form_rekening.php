<?php
include 'koneksi.php';

// Ambil daftar subkegiatan untuk dropdown
$sql = "SELECT s.id_subkegiatan, CONCAT(p.nama_program,' › ',keg.nama_kegiatan,' › ',s.nama_subkegiatan) AS label
        FROM subkegiatan s
        JOIN kegiatan keg ON keg.id_kegiatan = s.id_kegiatan
        JOIN program p ON p.id_program = keg.id_program
        ORDER BY p.nama_program, keg.nama_kegiatan, s.nama_subkegiatan";
$subs = $k->query($sql);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Tambah Rekening</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        Tambah Rekening
    </div>
    <div class="card-body">
        <form method="post" action="simpan_rekening.php" class="row g-3">
            
            <div class="col-12">
                <label class="form-label">Subkegiatan</label>
                <select name="id_subkegiatan" id="subSelect" class="form-select" required>
                    <option value="">-- Pilih Subkegiatan --</option>
                    <?php while($s = $subs->fetch_assoc()): ?>
                        <option value="<?= $s['id_subkegiatan'] ?>"><?= htmlspecialchars($s['label']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Kode Rekening</label>
                <input type="text" name="kode" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Rekening</label>
                <input type="text" name="nama" class="form-control" required>
            </div>

            <div class="col-12">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" selected>Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-between">
                <a href="manajemen_rekening.php" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

</div>

<!-- jQuery + Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function(){
    $('#subSelect').select2({
        placeholder: 'Cari subkegiatan...',
        width: '100%'
    });
});
</script>
</body>
</html>