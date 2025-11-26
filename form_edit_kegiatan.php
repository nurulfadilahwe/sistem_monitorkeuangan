<?php
include 'koneksi.php';

$id = (int)$_GET['id'];

$q = $k->query("
    SELECT k.*, p.nama_program 
    FROM kegiatan k 
    JOIN program p ON p.id_program = k.id_program
    WHERE k.id_kegiatan=$id
");

$data = $q->fetch_assoc();
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Edit Kegiatan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        Edit Kegiatan
    </div>

    <div class="card-body">

        <form method="post" action="update_kegiatan.php">
            <input type="hidden" name="id_kegiatan" value="<?= $data['id_kegiatan'] ?>">

            <div class="mb-3">
                <label>Program</label>
                <input type="text" class="form-control" value="<?= $data['nama_program'] ?>" readonly>
            </div>

            <div class="mb-3">
                <label>Kode Kegiatan</label>
                <input type="text" name="kode" class="form-control" required 
                    value="<?= htmlspecialchars($data['kode_kegiatan']) ?>">
            </div>

            <div class="mb-3">
                <label>Nama Kegiatan</label>
                <input type="text" name="nama" class="form-control" required
                    value="<?= htmlspecialchars($data['nama_kegiatan']) ?>">
            </div>

            <div class="mb-3">
                <label>Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" <?= $data['is_active'] ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= !$data['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-between">
                <a href="manajemen_kegiatan.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>

    </div>
</div>
</div>

</body>
</html>
