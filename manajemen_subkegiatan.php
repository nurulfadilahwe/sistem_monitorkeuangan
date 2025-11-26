<?php
session_start();
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB fail: ".$k->connect_error); }

$flash = $_SESSION['flash_msg'] ?? null; unset($_SESSION['flash_msg']);

$sql = "SELECT s.id_subkegiatan, s.nama_subkegiatan, s.is_active, k.nama_kegiatan, p.nama_program
        FROM subkegiatan s
        JOIN kegiatan k ON k.id_kegiatan = s.id_kegiatan
        JOIN program p ON p.id_program = k.id_program
        ORDER BY p.nama_program, k.nama_kegiatan, s.nama_subkegiatan";
$res = $k->query($sql);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Manajemen Subkegiatan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

<?php if ($flash): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span>Manajemen Subkegiatan</span>
        <a href="form_subkegiatan.php" class="btn btn-light btn-sm">+ Tambah Subkegiatan</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tblSub" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Program</th>
                        <th>Kegiatan</th>
                        <th>Nama Subkegiatan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['id_subkegiatan'] ?></td>
                        <td><?= htmlspecialchars($r['nama_program']) ?></td>
                        <td><?= htmlspecialchars($r['nama_kegiatan']) ?></td>
                        <td><?= htmlspecialchars($r['nama_subkegiatan']) ?></td>
                        <td>
                            <span class="badge <?= $r['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $r['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td class="d-flex gap-2">
                            <a href="form_edit_subkegiatan.php?id=<?= $r['id_subkegiatan'] ?>" 
                                class="btn btn-sm btn-primary">
                                Edit
                            </a>
                            <a href="toggle_subkegiatan.php?id=<?= $r['id_subkegiatan'] ?>"
                                class="btn btn-sm <?= $r['is_active'] ? 'btn-danger' : 'btn-success' ?>">
                                <?= $r['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <a href="master_data.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
        </div>
    </div>
</div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function(){ $('#tblSub').DataTable({"pageLength":10}); setTimeout(()=>$(".alert").fadeTo(1000,0).slideUp(1000,function(){ $(this).remove() }),3000); });
</script>
</body>
</html>
