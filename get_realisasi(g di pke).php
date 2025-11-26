<?php
header('Content-Type: application/json');
$k = new mysqli("localhost", "root", "", "monitor_keuangan");
if ($k->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$jenis = $_GET['jenis'] ?? '';

if (!$id || !$jenis) {
    echo json_encode(["success" => false, "error" => "Parameter tidak lengkap"]);
    exit;
}

switch ($jenis) {
    case 'Realisasi':
        $q = $k->prepare("SELECT tanggal, jumlah_realisasi AS jumlah, catatan FROM realisasi_detail WHERE id_detail = ?");
        $q->bind_param("i", $id);
        break;

    case 'Anggaran Tahunan':
        $q = $k->prepare("SELECT nilai_tahunan AS jumlah FROM anggaran_tahunan WHERE id = ?");
        $q->bind_param("i", $id);
        break;

    case 'Anggaran Bulanan':
        $q = $k->prepare("SELECT nilai_bulanan AS jumlah, created_at AS tanggal FROM anggaran WHERE id_anggaran = ?");
        $q->bind_param("i", $id);
        break;

    default:
        echo json_encode(["success" => false, "error" => "Jenis input tidak dikenal"]);
        exit;
}

$q->execute();
$res = $q->get_result();

if ($res && $res->num_rows > 0) {
    $data = $res->fetch_assoc();
    echo json_encode(["success" => true, "data" => $data]);
} else {
    echo json_encode(["success" => false, "error" => "Data tidak ditemukan"]);
}
