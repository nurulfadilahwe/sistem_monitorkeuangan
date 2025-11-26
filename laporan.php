<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { die("DB fail: ".$k->connect_error); }

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');

$jenis = $_GET['jenis'] ?? 'Tidak Ditemukan'; // default "Semua", bisa kamu sesuaikan

$namaBulan = [1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"];

// --- Jenis anggaran aktif ---
$stmt = $k->prepare("
    SELECT jenis FROM anggaran 
    WHERE tahun=? AND bulan=?
    ORDER BY FIELD(jenis,'perubahan','pergeseran','murni')
    LIMIT 1
");
$stmt->bind_param("ii", $tahun, $bulan);
$stmt->execute();
$resJenis = $stmt->get_result();
if ($resJenis->num_rows > 0) {
    $jenis = $resJenis->fetch_row()[0];
}

// --- Query total per program ---
$sqlProgram = "
SELECT 
    p.id_program,
    p.nama_program,
    COALESCE(SUM((
        SELECT SUM(aSD.nilai_bulanan)
        FROM anggaran aSD
        WHERE aSD.id_rekening = rek.id_rekening
        AND aSD.tahun = $tahun
        AND aSD.bulan <= $bulan
        AND aSD.jenis = (
            SELECT aX.jenis 
            FROM anggaran aX
            WHERE aX.id_rekening = rek.id_rekening
                AND aX.tahun = $tahun
                AND aX.bulan = aSD.bulan
            ORDER BY FIELD(aX.jenis,'perubahan','pergeseran','murni')
            LIMIT 1
        )
    )),0) AS total_anggaran_sd,
    COALESCE(SUM((
        SELECT SUM(rSD.jumlah_realisasi)
        FROM realisasi_detail rSD
        WHERE rSD.id_rekening = rek.id_rekening
          AND rSD.tahun = $tahun 
          AND rSD.bulan <= $bulan
    )),0) AS total_realisasi_sd
FROM program p
JOIN kegiatan k ON k.id_program = p.id_program
JOIN subkegiatan sk ON sk.id_kegiatan = k.id_kegiatan
JOIN rekening rek ON rek.id_subkegiatan = sk.id_subkegiatan
WHERE 
    p.is_active = 1
    AND k.is_active = 1
    AND sk.is_active = 1
    AND rek.is_active = 1
GROUP BY p.id_program, p.nama_program
ORDER BY p.id_program;
";
$resProgram = $k->query($sqlProgram);

// --- Query tabel laporan ---
$sql = "
SELECT 
    p.nama_program,
    k.nama_kegiatan,
    s.nama_subkegiatan,
    r.kode_rekening,
    r.nama_rekening,
    COALESCE((
        SELECT aB.nilai_bulanan FROM anggaran aB
        WHERE aB.id_rekening = r.id_rekening 
          AND aB.tahun = $tahun 
          AND aB.bulan = $bulan
        ORDER BY FIELD(aB.jenis,'perubahan','pergeseran','murni')
        LIMIT 1
    ),0) AS anggaran_bulan,

    COALESCE((
        SELECT SUM(aSD.nilai_bulanan) 
        FROM anggaran aSD
        WHERE aSD.id_rekening = r.id_rekening 
          AND aSD.tahun = $tahun 
          AND aSD.bulan <= $bulan
    ),0) AS anggaran_sd,

    COALESCE((
        SELECT aT.nilai_tahunan FROM anggaran_tahunan aT
        WHERE aT.id_rekening = r.id_rekening 
          AND aT.tahun = $tahun
          AND $bulan BETWEEN aT.bulan_mulai AND aT.bulan_selesai
        ORDER BY FIELD(aT.jenis,'perubahan','pergeseran','murni')
        LIMIT 1
    ),0) AS anggaran_tahunan,

    COALESCE((
        SELECT SUM(rB.jumlah_realisasi) FROM realisasi_detail rB
        WHERE rB.id_rekening = r.id_rekening 
          AND rB.tahun = $tahun 
          AND rB.bulan = $bulan
    ),0) AS realisasi_bulan,

    COALESCE((
        SELECT SUM(rSD.jumlah_realisasi) FROM realisasi_detail rSD
        WHERE rSD.id_rekening = r.id_rekening 
          AND rSD.tahun = $tahun 
          AND rSD.bulan <= $bulan
    ),0) AS realisasi_sd

FROM rekening r
JOIN subkegiatan s ON s.id_subkegiatan = r.id_subkegiatan
JOIN kegiatan k ON k.id_kegiatan = s.id_kegiatan
JOIN program p ON p.id_program = k.id_program

WHERE 
    p.is_active = 1
    AND k.is_active = 1
    AND s.is_active = 1
    AND r.is_active = 1

ORDER BY p.nama_program, k.nama_kegiatan, s.nama_subkegiatan, r.kode_rekening
";
$res = $k->query($sql);


// --- Ambil total anggaran sd bulan aktif & total realisasi sd bulan sebelumnya ---
$sqlProgramPrev = "
SELECT 
    p.id_program,
    p.nama_program,
    COALESCE(SUM((
        SELECT SUM(aSD.nilai_bulanan)
        FROM anggaran aSD
        WHERE aSD.id_rekening = rek.id_rekening
          AND aSD.tahun = $tahun
          AND aSD.bulan <= $bulan
          AND aSD.jenis = (
              SELECT aX.jenis FROM anggaran aX
              WHERE aX.id_rekening = rek.id_rekening 
                AND aX.tahun = $tahun 
                AND aX.bulan = $bulan
              ORDER BY FIELD(aX.jenis,'perubahan','pergeseran','murni')
              LIMIT 1
          )
    )),0) AS total_anggaran_sd,

    COALESCE(SUM((
        SELECT SUM(rSD.jumlah_realisasi)
        FROM realisasi_detail rSD
        WHERE rSD.id_rekening = rek.id_rekening
          AND rSD.tahun = $tahun 
          AND rSD.bulan < $bulan
    )),0) AS realisasi_sd_bulan_sebelumnya

FROM program p
JOIN kegiatan k ON k.id_program = p.id_program
JOIN subkegiatan sk ON sk.id_kegiatan = k.id_kegiatan
JOIN rekening rek ON rek.id_subkegiatan = sk.id_subkegiatan

WHERE 
    p.is_active = 1
    AND k.is_active = 1
    AND sk.is_active = 1
    AND rek.is_active = 1

GROUP BY p.id_program, p.nama_program
ORDER BY p.id_program;
";
$resProgramPrev = $k->query($sqlProgramPrev);

$anggaranProgram = [];
$realisasiSebelum = [];
$namaProgram = [];
while($r = $resProgramPrev->fetch_assoc()){
    $id = (int)$r['id_program'];
    $anggaranProgram[$id] = (float)$r['total_anggaran_sd'];
    $realisasiSebelum[$id] = (float)$r['realisasi_sd_bulan_sebelumnya'];
    $namaProgram[$id] = $r['nama_program'];
}

// --- Realisasi mingguan bulan aktif ---
$sqlMingguan = "
SELECT 
    p.id_program,
    p.nama_program,
    (WEEK(rD.tanggal,1) - WEEK(DATE_SUB(rD.tanggal, INTERVAL DAY(rD.tanggal)-1 DAY),1) + 1) AS minggu_ke,
    SUM(rD.jumlah_realisasi) AS total_realisasi
FROM realisasi_detail rD
JOIN rekening r ON r.id_rekening = rD.id_rekening
JOIN subkegiatan s ON s.id_subkegiatan = r.id_subkegiatan
JOIN kegiatan k ON k.id_kegiatan = s.id_kegiatan
JOIN program p ON p.id_program = k.id_program

WHERE 
    rD.tahun = $tahun 
    AND rD.bulan = $bulan
    AND p.is_active = 1
    AND k.is_active = 1
    AND s.is_active = 1
    AND r.is_active = 1

GROUP BY p.id_program, minggu_ke
ORDER BY p.id_program, minggu_ke
";
$resMingguan = $k->query($sqlMingguan);

$dataMingguan = []; 
$maxWeek = 0;
while($row = $resMingguan->fetch_assoc()){
    $idProg = (int)$row['id_program'];
    $week   = (int)$row['minggu_ke'];
    $real   = (float)$row['total_realisasi'];
    $dataMingguan[$idProg][$week] = $real;
    if ($week > $maxWeek) $maxWeek = $week;
}

// simpan hasil query program ke $dataProgram juga
$dataProgram = [];
while ($row = $resProgram->fetch_assoc()) {
    $idProg = $row['id_program'];
    $dataProgram[$idProg] = $row;
}

// --- Hitung persentase mingguan (dengan akumulasi realisasi sebelumnya) ---
$persenMingguan = [];

// pastikan $dataProgram berupa array meskipun kosong
if (!empty($dataProgram) && is_array($dataProgram)) {
    foreach ($dataProgram as $idProg => $row) {
        $anggaran = (float)$row['total_anggaran_sd'];
        $realisasiTotal = (float)$row['total_realisasi_sd'];

        $runningTotal = $realisasiSebelum[$idProg] ?? 0;
        $weeks = $dataMingguan[$idProg] ?? [];

        for ($w = 1; $w <= $maxWeek; $w++) {
            // tambahkan realisasi tiap minggu ke total kumulatif
            $runningTotal += $weeks[$w] ?? 0;
            $persenMingguan[$idProg][$w] = ($anggaran > 0) ? ($runningTotal / $anggaran * 100) : 0;
        }
    }
}

// --- dataset untuk Chart.js ---
$orderedPrograms = array_values($namaProgram);

// $orderedPrograms = [
//     "PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA",
//     "PROGRAM PEMBINAAN PERPUSTAKAAN",
//     "PROGRAM PELESTARIAN KOLEKSI NASIONAL DAN NASKAH KUNO ",
//     "PROGRAM PENGELOLAAN ARSIP "
// ];

// -----warna chart bar-----
$programColors = [];
$colorList = ["#007bff","#28a745","#dc3545","#ffc107","#6f42c1","#20c997","#fd7e14"];

$i=0;
foreach ($orderedPrograms as $progName) {
    $programColors[$progName] = $colorList[$i % count($colorList)];
    $i++;
}

// $programColors = [
//     "PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA" => "#dc3545",
//     "PROGRAM PELESTARIAN KOLEKSI NASIONAL DAN NASKAH KUNO"        => "#ffc107",
//     "PROGRAM PENGELOLAAN ARSIP"                                   => "#007bff",
//     "PROGRAM PEMBINAAN PERPUSTAKAAN"                              => "#28a745",
// ];

// --- Dataset untuk Chart.js ---
$datasetsMingguan = [];
for ($w=1; $w <= $maxWeek; $w++){
    $values = [];
    foreach($orderedPrograms as $progName){
        // cari id program by nama
        $idProg = array_search($progName, $namaProgram);
        $values[] = $idProg ? ($persenMingguan[$idProg][$w] ?? 0) : 0;
    }
    $datasetsMingguan[] = [
        "label" => "Minggu $w",
        "backgroundColor" => array_values($programColors),
        "data" => $values
    ];
}

// --- query untuk chart bulanan 
$sqlBulanan = "
SELECT 
    p.id_program,
    p.nama_program,
    m.bulan,
    COALESCE((
        SELECT SUM(aSD.nilai_bulanan)
        FROM anggaran aSD
        JOIN rekening r2 ON r2.id_rekening = aSD.id_rekening
        JOIN subkegiatan s2 ON s2.id_subkegiatan = r2.id_subkegiatan
        JOIN kegiatan k2 ON k2.id_kegiatan = s2.id_kegiatan
        JOIN program p2 ON p2.id_program = k2.id_program
        WHERE aSD.tahun = $tahun
          AND aSD.bulan <= m.bulan
          AND p2.id_program = p.id_program
          AND p2.is_active = 1
          AND k2.is_active = 1
          AND s2.is_active = 1
          AND r2.is_active = 1
    ),0) AS total_anggaran_sd,

    COALESCE((
        SELECT SUM(rSD.jumlah_realisasi)
        FROM realisasi_detail rSD
        JOIN rekening r3 ON r3.id_rekening = rSD.id_rekening
        JOIN subkegiatan s3 ON s3.id_subkegiatan = r3.id_subkegiatan
        JOIN kegiatan k3 ON k3.id_kegiatan = s3.id_kegiatan
        JOIN program p3 ON p3.id_program = k3.id_program
        WHERE rSD.tahun = $tahun
          AND rSD.bulan <= m.bulan
          AND p3.id_program = p.id_program
          AND p3.is_active = 1
          AND k3.is_active = 1
          AND s3.is_active = 1
          AND r3.is_active = 1
    ),0) AS total_realisasi_sd

FROM program p
CROSS JOIN (
    SELECT n AS bulan
    FROM (
        SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 
        UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
    ) x
    WHERE n <= $bulan
) m
WHERE p.is_active = 1
ORDER BY p.id_program, m.bulan;
";
$resBulanan = $k->query($sqlBulanan);

$dataBulanan = [];
while($row = $resBulanan->fetch_assoc()){
    $prog   = $row['nama_program'];
    $bulan  = (int)$row['bulan'];
    $ang    = (float)$row['total_anggaran_sd'];
    $real   = (float)$row['total_realisasi_sd'];

    $dataBulanan[$prog]['anggaran'][$bulan]  = $ang;
    $dataBulanan[$prog]['realisasi'][$bulan] = $real;
}

$bulanLabels = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"];

$datasetsBulanan = [];
$colors = ["#007bff","#28a745","#dc3545","#ffc107"];

$i=0;
foreach($dataBulanan as $prog => $val){
    $datasetsBulanan[] = [
        "label" => $prog." - Anggaran",
        "borderColor" => $colors[$i%count($colors)],
        "fill" => false,
        "data" => array_values($val['anggaran'])
    ];
    $datasetsBulanan[] = [
        "label" => $prog." - Realisasi",
        "borderColor" => $colors[$i%count($colors)],
        "borderDash" => [5,5],
        "fill" => false,
        "data" => array_values($val['realisasi'])
    ];
    $i++;
}

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Monitoring Keuangan</title>
        <link href="css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
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
                    <h3 class="text-center mb-3">📊 Monitor Keuangan (Jenis aktif: <?= htmlspecialchars($jenis ?? 'Tidak ditentukan') ?>)</h3>
                    <div class="table-responsive bg-white shadow-sm rounded mb-4">
                        <h5 class="bg-dark text-white p-2">🔹 Rekap Per Program (s.d <?= $namaBulan[$bulan] ?> <?= $tahun ?>)</h5>
                        <table class="table table-bordered table-hover align-middle ">
                            <thead class="table-secondary text-center">
                                <tr>
                                    <th>Program</th>
                                    <th>Total Anggaran s.d <?= $namaBulan[$bulan] ?></th>
                                    <th>Total Realisasi s.d <?= $namaBulan[$bulan] ?></th>
                                    <th>Selisih</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($dataProgram)): ?>
                                    <?php foreach ($dataProgram as $row): 
                                        $selisih = $row['total_anggaran_sd'] - $row['total_realisasi_sd'];
                                        $persen  = $row['total_anggaran_sd'] > 0 ? ($row['total_realisasi_sd'] / $row['total_anggaran_sd']) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nama_program']) ?></td>
                                        <td class="text-end">Rp <?= number_format($row['total_anggaran_sd'],0,',','.') ?></td>
                                        <td class="text-end">Rp <?= number_format($row['total_realisasi_sd'],0,',','.') ?></td>
                                        <td class="text-end">Rp <?= number_format($selisih,0,',','.') ?></td>
                                        <td class="text-center"><?= number_format($persen,2) ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">Tidak ada data.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>


                    <div class="table-responsive bg-white shadow-sm rounded p-3">
                        <form class="row g-2 align-items-center justify-content-between mb-3" method="get">
                            <div class="col-md-8 d-flex flex-wrap gap-2">
                                <input type="number" class="form-control w-auto" name="tahun" value="<?= $tahun ?>">
                                <select class="form-select w-auto" name="bulan">
                                    <?php foreach($namaBulan as $i=>$n): ?>
                                    <option value="<?= $i ?>" <?= $i==$bulan?'selected':''; ?>><?= $n ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-primary">Tampilkan</button>
                            </div>

                            <div class="col-md-auto">
                                <a href="export_excel.php?tahun=<?= $tahun ?>&bulan=<?= $bulan ?>" class="btn btn-outline-success">
                                    📥 Export Excel
                                </a>
                            </div>
                        </form>

                        <table class="table table-bordered table-striped align-middle" id="tblLaporan">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Program</th>
                                    <th>Kegiatan</th>
                                    <th>Subkegiatan</th>
                                    <th>Rekening</th>
                                    <th>Anggaran Tahunan</th>
                                    <th>Anggaran Bulan <?= $namaBulan[$bulan] ?></th>
                                    <th>Anggaran s.d <?= $namaBulan[$bulan] ?></th>
                                    <th>Realisasi Bulan <?= $namaBulan[$bulan] ?></th>
                                    <th>Realisasi s.d <?= $namaBulan[$bulan] ?></th>
                                    <th>Selisih</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($res && $res->num_rows): ?>
                                <?php while($row = $res->fetch_assoc()):
                                    $selisih = $row['anggaran_sd'] - $row['realisasi_sd'];
                                    $persen  = $row['anggaran_sd'] > 0 ? ($row['realisasi_sd'] / $row['anggaran_sd']) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama_program']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_subkegiatan']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_rekening'].' - '.$row['nama_rekening']) ?></td>
                                    <td class="text-end">Rp <?= number_format($row['anggaran_tahunan'],0,',','.') ?></td>
                                    <td class="text-end">Rp <?= number_format($row['anggaran_bulan'],0,',','.') ?></td>
                                    <td class="text-end">Rp <?= number_format($row['anggaran_sd'],0,',','.') ?></td>
                                    <td class="text-end">Rp <?= number_format($row['realisasi_bulan'],0,',','.') ?></td>
                                    <td class="text-end">Rp <?= number_format($row['realisasi_sd'],0,',','.') ?></td>
                                    <td class="text-end">Rp <?= number_format($selisih,0,',','.') ?></td>
                                    <td class="text-center"><?= number_format($persen,2) ?>%</td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr><td colspan="11" class="text-center text-muted">Tidak ada data.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="card mt-5">
                        <div class="card-header"><i class="fas fa-chart-bar me-1"></i> Realisasi Per Minggu (<?= $namaBulan[$bulan] ?>/<?= $tahun ?>)</div>
                        <div class="card-body">
                            <canvas id="weeklyChart"></canvas>
                        </div>
                    </div>
                    <div class="card mt-5">
                        <div class="card-header"><i class="fas fa-chart-line me-1"></i> Grafik Anggaran vs Realisasi per Bulan (<?= $tahun ?>)</div>
                        <div class="card-body">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/datatables-demo.js"></script>
        <!-- DataTables JS -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

        <script>
        $(document).ready(function(){
            $('#tblLaporan').DataTable({
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50, 100],
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    "paginate": {
                        "previous": "Sebelumnya",
                        "next": "Berikutnya"
                    },
                    "zeroRecords": "Tidak ada data yang cocok."
                },
                "order": [[0, 'asc']]
            });
        });
        </script>


        <!-- Script Chart.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
        <script>
        var ctx = document.getElementById("weeklyChart").getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($orderedPrograms) ?>,
                datasets: <?= json_encode($datasetsMingguan) ?>
            },
            options: {
                responsive: true,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: v => v + "%"
                        },
                        scaleLabel: { display:true, labelString:"Persentase (%)" }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ": " +
                                    Number(tooltipItem.yLabel).toFixed(2) + "%";
                        }
                    }
                }
            }
        });
        </script>

        <script>
        var ctxM = document.getElementById("monthlyChart").getContext('2d');
        new Chart(ctxM, {
            type: 'line',
            data: {
                labels: <?= json_encode($bulanLabels) ?>,
                datasets: <?= json_encode($datasetsBulanan) ?>
            },
            options: {
                responsive: true,
                title: { display: true, text: "Anggaran vs Realisasi per Bulan" },
                tooltips: { mode: 'index', intersect: false },
                scales: {
                    yAxes: [{
                        ticks: {
                            callback: function(value){ return "Rp " + value.toLocaleString(); }
                        }
                    }]
                }
            }
        });
        </script>
    </body>
</html>