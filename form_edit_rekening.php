<?php
session_start();
include 'koneksi.php';

// Pastikan ada ID
if (!isset($_GET['id'])) {
    $_SESSION['flash_msg'] = "ID rekening tidak ditemukan.";
    header("Location: manajemen_rekening.php");
    exit;
}

$id = (int)$_GET['id'];

// Ambil data rekening
$sql = "SELECT * FROM rekening WHERE id_rekening = $id LIMIT 1";
$res = $k->query($sql);
if ($res->num_rows == 0) {
    $_SESSION['flash_msg'] = "Rekening tidak ditemukan.";
    header("Location: manajemen_rekening.php");
    exit;
}
$rekening = $res->fetch_assoc();

// Ambil daftar subkegiatan
$subs = $k->query("SELECT id_subkegiatan, nama_subkegiatan FROM subkegiatan ORDER BY nama_subkegiatan");
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Edit Rekening</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

<div class="card shadow">
     <div class="card-header bg-primary text-white">
        Edit Rekening
    </div>
    <div class="card-body">

        <form action="update_rekening.php" method="post">
            <input type="hidden" name="id_rekening" value="<?= $rekening['id_rekening'] ?>">

            <div class="mb-3">
                <label class="form-label">Subkegiatan</label>
                <select name="id_subkegiatan" class="form-select" required>
                    <option value="">-- Pilih Subkegiatan --</option>
                    <?php while($s = $subs->fetch_assoc()): ?>
                        <option value="<?= $s['id_subkegiatan'] ?>"
                            <?= $rekening['id_subkegiatan'] == $s['id_subkegiatan'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nama_subkegiatan']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Tambahan: Edit kode rekening -->
            <div class="mb-3">
                <label class="form-label">Kode Rekening</label>
                <input type="text" 
                       name="kode_rekening" 
                       class="form-control" 
                       value="<?= htmlspecialchars($rekening['kode_rekening']) ?>" 
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Rekening</label>
                <input type="text" 
                       name="nama_rekening" 
                       class="form-control" 
                       value="<?= htmlspecialchars($rekening['nama_rekening']) ?>" 
                       required>
            </div>

            <!-- Status -->
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" <?= $rekening['is_active'] == 1 ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= $rekening['is_active'] == 0 ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <a href="manajemen_rekening.php" class="btn btn-secondary">Batal</a>
                <button class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>

    </div>
</div>

</div>

</body>
</html>