<?php
session_start();
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB fail: ".$k->connect_error); }

// Ambil pesan flash jika ada
$flash = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

// Ambil semua program
$sql = "SELECT id_program, kode_program, nama_program, is_active
        FROM program
        ORDER BY kode_program, nama_program";
$res = $k->query($sql);
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Manajemen Program</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

<?php if ($flash): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <span>Manajemen Program</span>
        <a href="form_program.php" class="btn btn-light btn-sm">+ Tambah Program</a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="tblProgram" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="">ID</th>
                        <th width="150">Kode Program</th>
                        <th>Nama Program</th>
                        <th width="">Status</th>
                        <th width="">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_program'] ?></td>
                        <td><?= htmlspecialchars($row['kode_program']) ?></td>
                        <td><?= htmlspecialchars($row['nama_program']) ?></td>
                        <td>
                            <span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td>
                            <a href="form_edit_program.php?id=<?= $row['id_program'] ?>" 
                                class="btn btn-sm btn-primary">
                                Edit
                            </a>
                            <a href="toggle_program.php?id=<?= $row['id_program'] ?>"
                            class="btn btn-sm <?= $row['is_active'] ? 'btn-danger' : 'btn-success' ?>">
                                <?= $row['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
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

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function(){
    $('#tblProgram').DataTable({
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50, 100],
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            "paginate": {
                "previous": "Sebelumnya",
                "next": "Berikutnya"
            }
        }
    });

    // Auto close alert
    setTimeout(function(){
        $(".alert").fadeTo(1000, 0).slideUp(1000, function(){
            $(this).remove();
        });
    }, 1000);
});
</script>
</body>
</html>
