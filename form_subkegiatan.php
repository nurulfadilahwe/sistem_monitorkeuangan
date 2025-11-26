<?php
include 'koneksi.php';

// Ambil daftar kegiatan (join nama program untuk memudahkan user)
$sql = "SELECT 
            k.id_kegiatan, 
            CONCAT(p.nama_program, ' › ', k.nama_kegiatan) AS label
        FROM kegiatan k
        JOIN program p ON p.id_program = k.id_program
        WHERE k.is_active = 1
        ORDER BY p.nama_program, k.nama_kegiatan";
$kegs = $k->query($sql);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Tambah Subkegiatan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>
<body class="bg-light">
<div class="container py-4">

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        Tambah Subkegiatan
    </div>
    <div class="card-body">

        <form method="post" action="simpan_subkegiatan.php" class="row g-3">

            <div class="col-12">
                <label class="form-label">Kegiatan</label>
                <select name="id_kegiatan" id="kegiatanSelect" class="form-select" required>
                    <option value="">-- Pilih Kegiatan --</option>
                    <?php while($kg = $kegs->fetch_assoc()): ?>
                        <option value="<?= $kg['id_kegiatan'] ?>">
                            <?= htmlspecialchars($kg['label']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Kode Subkegiatan</label>
                <input type="text" name="kode" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Subkegiatan</label>
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
                <a href="manajemen_subkegiatan.php" class="btn btn-secondary">Kembali</a>
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
    $('#kegiatanSelect').select2({
        placeholder: 'Cari kegiatan...',
        width: '100%'
    });
});
</script>

</body>
</html>