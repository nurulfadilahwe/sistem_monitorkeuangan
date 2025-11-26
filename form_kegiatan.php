<?php
include 'koneksi.php';

// Ambil daftar program utk dropdown
$sql = "SELECT id_program, nama_program 
        FROM program 
        WHERE is_active = 1
        ORDER BY nama_program";
$programs = $k->query($sql);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Tambah Kegiatan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>
<body class="bg-light">
<div class="container py-4">

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        Tambah Kegiatan
    </div>
    <div class="card-body">

        <form method="post" action="simpan_kegiatan.php" class="row g-3">

            <div class="col-12">
                <label class="form-label">Program</label>
                <select name="id_program" id="programSelect" class="form-select" required>
                    <option value="">-- Pilih Program --</option>
                    <?php while($p = $programs->fetch_assoc()): ?>
                        <option value="<?= $p['id_program'] ?>">
                            <?= htmlspecialchars($p['nama_program']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Kode Kegiatan</label>
                <input type="text" name="kode" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Kegiatan</label>
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
                <a href="manajemen_kegiatan.php" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary text-white">Simpan</button>
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
    $('#programSelect').select2({
        placeholder: 'Cari program...',
        width: '100%'
    });
});
</script>

</body>
</html>