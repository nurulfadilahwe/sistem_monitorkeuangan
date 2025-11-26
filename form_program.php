<?php
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { 
    die("DB fail: " . $k->connect_error); 
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Tambah Program</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container py-4">

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        Tambah Program
    </div>
    <div class="card-body">
        <form method="post" action="simpan_program.php" class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Kode Program</label>
                <input type="text" name="kode" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Program</label>
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
                <a href="manajemen_program.php" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>

        </form>
    </div>
</div>

</div>
</body>
</html>