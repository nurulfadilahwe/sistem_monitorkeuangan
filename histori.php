<?php
session_start();
include 'koneksi.php';

// Query Anggaran Tahunan
$sql_tahunan = "
    SELECT at.id, at.tahun, at.jenis, at.nilai_tahunan, at.bulan_mulai, at.bulan_selesai,
           r.kode_rekening, r.nama_rekening
    FROM anggaran_tahunan at
    JOIN rekening r ON r.id_rekening = at.id_rekening
    ORDER BY at.tahun DESC, r.kode_rekening
";
$data_tahunan = $k->query($sql_tahunan);

// Query Anggaran Bulanan
$sql_bulanan = "
    SELECT a.id_anggaran, a.tahun, a.bulan, a.nilai_bulanan, a.jenis,
           r.kode_rekening, r.nama_rekening
    FROM anggaran a
    JOIN rekening r ON r.id_rekening = a.id_rekening
    ORDER BY a.tahun DESC, a.bulan, r.kode_rekening
";
$data_bulanan = $k->query($sql_bulanan);

// Query Realisasi
$sql_realisasi = "
    SELECT rd.id_detail, rd.tahun, rd.bulan, rd.tanggal, rd.jumlah_realisasi,
           r.kode_rekening, r.nama_rekening
    FROM realisasi_detail rd
    JOIN rekening r ON r.id_rekening = rd.id_rekening
    ORDER BY rd.tahun DESC, rd.bulan DESC, rd.tanggal DESC
";
$data_realisasi = $k->query($sql_realisasi);

$bulan = [
    1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"Mei",6=>"Jun",
    7=>"Jul",8=>"Agu",9=>"Sep",10=>"Okt",11=>"Nov",12=>"Des"
];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Monitoring Keuangan</title>
        <link href="css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>

        <style>
        .nav-tabs .nav-link.active {
            background-color: #0d6efd !important;
            color: white !important;
        }

        </style>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a class="navbar-brand" href="index.php">Monitoring Keuangan</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav flex-column">
                            <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                            <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-upload"></i></div>
                                Input Data Anggaran/Realisasi
                            </a>
                            <a class="nav-link <?= $current_page == 'master_data.php' ? 'active' : '' ?>" href="master_data.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tasks"></i></div>
                                Kelola Master Data
                            </a>
                            <a class="nav-link <?= $current_page == 'laporan.php' ? 'active' : '' ?>" href="laporan.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                                Laporan
                            </a>
                            <a class="nav-link <?= $current_page == 'histori.php' ? 'active' : '' ?>" href="histori.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Histori Input
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Kepala Subbagian Perencanaan dan Keuangan
                    </div>
                </nav>
            </div>

            <!-- Main Content -->
            <div id="layoutSidenav_content">
                <div class="container py-4">
                    <h3 class="text-center mb-3">📊 Histori Input</h3>
                    <div class="d-flex align-items-center mb-3">
                        <div class="container py-4">
                            <div class="card shadow">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Histori Input Data</h5>
                                </div>

                                <div class="card-body">

                                    <?php if(isset($_SESSION['flash_msg'])): ?>
                                        <div class="alert alert-info"><?= $_SESSION['flash_msg']; unset($_SESSION['flash_msg']); ?></div>
                                    <?php endif; ?>

                                    <!-- ======================== -->
                                    <!--        NAV TABS          -->
                                    <!-- ======================== -->
                                    <ul class="nav nav-tabs" id="myTab">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#tahunan">Anggaran Tahunan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#bulanan">Anggaran Bulanan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#realisasi">Realisasi</a>
                                        </li>
                                    </ul>

                                    <div class="tab-content pt-3">

                                        <!-- ========================= -->
                                        <!-- TAB 1: ANGGRAN TAHUNAN -->
                                        <!-- ========================= -->
                                        <div class="tab-pane fade show active" id="tahunan">
                                            <table id="tableTahunan" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Tahun</th>
                                                        <th>Rekening</th>
                                                        <th>Jenis</th>
                                                        <th>Periode</th>
                                                        <th>Nilai Tahunan</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php while($d = $data_tahunan->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= $d['tahun'] ?></td>
                                                        <td><?= $d['kode_rekening']." - ".$d['nama_rekening'] ?></td>
                                                        <td><?= ucfirst($d['jenis']) ?></td>
                                                        <td><?= $bulan[$d['bulan_mulai']] ?> - <?= $bulan[$d['bulan_selesai']] ?></td>
                                                        <td>Rp <?= number_format($d['nilai_tahunan'],0,',','.') ?></td>
                                                        <td>
                                                            <a href="form_edit_anggaran_tahunan.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                            <a onclick="return confirm('Yakin hapus?')"
                                                                href="hapus_anggaran_tahunan.php?id=<?= $d['id'] ?>"
                                                                class="btn btn-sm btn-danger">Hapus</a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- ========================= -->
                                        <!-- TAB 2: ANGGRAN BULANAN -->
                                        <!-- ========================= -->
                                        <div class="tab-pane fade" id="bulanan">
                                            <table id="tableBulanan" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Tahun</th>
                                                        <th>Bulan</th>
                                                        <th>Rekening</th>
                                                        <th>Jenis</th>
                                                        <th>Nilai Bulanan</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php while($b = $data_bulanan->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= $b['tahun'] ?></td>
                                                        <td><?= $bulan[$b['bulan']] ?></td>
                                                        <td><?= $b['kode_rekening']." - ".$b['nama_rekening'] ?></td>
                                                        <td><?= ucfirst($b['jenis']) ?></td>
                                                        <td>Rp <?= number_format($b['nilai_bulanan'],0,',','.') ?></td>
                                                        <td>
                                                            <a href="form_edit_anggaran.php?id=<?= $b['id_anggaran'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                            <a onclick="return confirm('Yakin hapus?')"
                                                                href="hapus_anggaran.php?id=<?= $b['id_anggaran'] ?>"
                                                                class="btn btn-sm btn-danger">Hapus</a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- ========================= -->
                                        <!-- TAB 3: REALISASI -->
                                        <!-- ========================= -->
                                        <div class="tab-pane fade" id="realisasi">
                                            <table id="tableRealisasi" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>Tahun</th>
                                                        <th>Bulan</th>
                                                        <th>Rekening</th>
                                                        <th>Jumlah Realisasi</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php while($r = $data_realisasi->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= $r['tanggal'] ?></td>
                                                        <td><?= $r['tahun'] ?></td>
                                                        <td><?= $bulan[$r['bulan']] ?></td>
                                                        <td><?= $r['kode_rekening']." - ".$r['nama_rekening'] ?></td>
                                                        <td>Rp <?= number_format($r['jumlah_realisasi'],0,',','.') ?></td>
                                                        <td>
                                                            <a href="form_edit_realisasi.php?id=<?= $r['id_detail'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                            <a onclick="return confirm('Yakin hapus?')"
                                                                href="hapus_realisasi.php?id=<?= $r['id_detail'] ?>"
                                                                class="btn btn-sm btn-danger">Hapus</a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div> <!-- /tab content -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Edit -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true"></div>

            <!-- Toast container -->
            <div class="toast-container" id="toastContainer"></div>
        </div>

        <!-- scripts -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script>
        $(document).ready(function() {
            $('#tabelHistori').DataTable({
                "paging": true,            // pagination
                "lengthChange": true,      // dropdown pilihan tampil 10/25/50
                "searching": true,         // pencarian
                "ordering": true,          // sorting kolom
                "info": true,
                "autoWidth": false,
                "responsive": true,

                // Optional: Setting kolom default tidak bisa sorting
                "columnDefs": [
                    { "orderable": false, "targets": 5 } // kolom 'Aksi' tidak sortable
                ],

                // Optional: Set bahasa (biar lebih ramah)
                "language": {
                    "lengthMenu": "Tampilkan _MENU_ baris",
                    "search": "Cari:",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data",
                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "›",
                        "previous": "‹"
                    }
                }
            });
        });
        </script>

        <script>
        function initTable(id) {
            if (!$.fn.DataTable.isDataTable(id)) {
                $(id).DataTable();
            }
        }

        // Aktifkan tabel saat tab dibuka
        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', e => {
                let target = e.target.getAttribute("href");

                if (target === "#tahunan") initTable('#tableTahunan');
                if (target === "#bulanan") initTable('#tableBulanan');
                if (target === "#realisasi") initTable('#tableRealisasi');
            });
        });

        // Inisialisasi tab pertama
        initTable('#tableTahunan');
        </script>

        <script>
            setTimeout(function(){
                $(".alert").fadeTo(1000, 0).slideUp(1000, function(){
                    $(this).remove();
                });
            }, 1000); // 3 detik
        </script>
    </body>
</html>