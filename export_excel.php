<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi DB
include 'koneksi.php';

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');

$namaBulan = [1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"];

// Ambil jenis aktif
$jenis = '';
$qJenis = $k->query("SELECT jenis FROM anggaran_jenis_aktif WHERE tahun=$tahun AND bulan=$bulan");
if ($qJenis && $qJenis->num_rows) {
    $jenis = $qJenis->fetch_row()[0];
} else {
    $jenis = 'murni'; // default
}

// Query data (sama persis dengan monitor.php)
$sql = "
SELECT 
    p.nama_program,
    k.nama_kegiatan,
    s.nama_subkegiatan,
    r.kode_rekening,
    r.nama_rekening,

    COALESCE((
        SELECT aB.nilai_bulanan FROM anggaran aB
        WHERE aB.id_rekening = r.id_rekening AND aB.tahun = $tahun AND aB.bulan = $bulan AND aB.jenis = '$jenis'
        LIMIT 1
    ),0) AS anggaran_bulan,

    COALESCE((
        SELECT SUM(aSD.nilai_bulanan) FROM anggaran aSD
        WHERE aSD.id_rekening = r.id_rekening AND aSD.tahun = $tahun AND aSD.bulan <= $bulan AND aSD.jenis = '$jenis'
    ),0) AS anggaran_sd,

    COALESCE((
        SELECT SUM(rB.jumlah_realisasi) FROM realisasi_detail rB
        WHERE rB.id_rekening = r.id_rekening AND rB.tahun = $tahun AND rB.bulan = $bulan
    ),0) AS realisasi_bulan,

    COALESCE((
        SELECT SUM(rSD.jumlah_realisasi) FROM realisasi_detail rSD
        WHERE rSD.id_rekening = r.id_rekening AND rSD.tahun = $tahun AND rSD.bulan <= $bulan
    ),0) AS realisasi_sd

FROM rekening r
JOIN subkegiatan s ON s.id_subkegiatan = r.id_subkegiatan
JOIN kegiatan k ON k.id_kegiatan = s.id_kegiatan
JOIN program p ON p.id_program = k.id_program
ORDER BY p.nama_program, k.nama_kegiatan, s.nama_subkegiatan, r.kode_rekening
";

$res = $k->query($sql);

// Set header untuk Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_keuangan_{$tahun}_{$bulan}.xls");

echo "<table border='1'>";
echo "<tr>
        <th>Program</th>
        <th>Kegiatan</th>
        <th>Subkegiatan</th>
        <th>Rekening</th>
        <th>Anggaran Bulan {$namaBulan[$bulan]}</th>
        <th>Anggaran s.d {$namaBulan[$bulan]}</th>
        <th>Realisasi Bulan {$namaBulan[$bulan]}</th>
        <th>Realisasi s.d {$namaBulan[$bulan]}</th>
        <th>Selisih</th>
        <th>%</th>
    </tr>";

if ($res && $res->num_rows) {
    while($row = $res->fetch_assoc()){
        $selisih = $row['anggaran_bulan'] - $row['realisasi_bulan'];
        $persen  = $row['anggaran_sd'] > 0 ? ($row['realisasi_sd'] / $row['anggaran_sd']) * 100 : 0;

        echo "<tr>
            <td>".htmlspecialchars($row['nama_program'])."</td>
            <td>".htmlspecialchars($row['nama_kegiatan'])."</td>
            <td>".htmlspecialchars($row['nama_subkegiatan'])."</td>
            <td>".htmlspecialchars($row['kode_rekening'].' - '.$row['nama_rekening'])."</td>
            <td>".number_format($row['anggaran_bulan'],0,',','.')."</td>
            <td>".number_format($row['anggaran_sd'],0,',','.')."</td>
            <td>".number_format($row['realisasi_bulan'],0,',','.')."</td>
            <td>".number_format($row['realisasi_sd'],0,',','.')."</td>
            <td>".number_format($selisih,0,',','.')."</td>
            <td>".number_format($persen,2)."%</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='10'>Tidak ada data</td></tr>";
}
echo "</table>";