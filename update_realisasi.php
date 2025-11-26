<?php
header('Content-Type: application/json');
include 'koneksi.php';

$id = isset($_POST['id_detail']) ? (int)$_POST['id_detail'] : 0;
$jenis = $_POST['jenis'] ?? '';
$jumlah = isset($_POST['jumlah_realisasi']) && $_POST['jumlah_realisasi'] !== '' ? floatval($_POST['jumlah_realisasi']) : null;
$tanggal = isset($_POST['tanggal']) && $_POST['tanggal'] !== '' ? $_POST['tanggal'] : null;
$note = $_POST['note'] ?? '';

if (!$id || !$jenis) {
    echo json_encode(["success" => false, "error" => "Parameter tidak lengkap"]);
    exit;
}

$q = null;

switch ($jenis) {
    case 'Realisasi':
        // Ambil data lama
        $lama = $k->query("SELECT id_rekening, tahun, bulan, jumlah_realisasi 
                        FROM realisasi_detail 
                        WHERE id_detail = $id")
                ->fetch_assoc();

        $id_rekening = $lama['id_rekening'];
        $tahun       = $lama['tahun'];
        $bulan       = $lama['bulan'];
        $nilai_lama  = floatval($lama['jumlah_realisasi']);

        // Ambil anggaran bulanan
        $ag = $k->query("
            SELECT nilai_bulanan 
            FROM anggaran 
            WHERE id_rekening = $id_rekening
            AND tahun = $tahun
            AND bulan = $bulan
        ")->fetch_assoc();

        $nilai_anggaran_bulan = floatval($ag['nilai_bulanan'] ?? 0);

        // Hitung total realisasi bulan ini
        $sum = $k->query("
            SELECT SUM(jumlah_realisasi) AS total 
            FROM realisasi_detail
            WHERE id_rekening = $id_rekening
            AND tahun = $tahun
            AND bulan = $bulan
        ")->fetch_assoc();

        $total_lama = floatval($sum['total'] ?? 0);

        // Hitung total baru setelah perubahan
        $total_baru = ($total_lama - $nilai_lama) + floatval($jumlah);

        // VALIDASI: Tidak boleh melewati anggaran bulanan
        if ($total_baru > $nilai_anggaran_bulan) {
            echo json_encode([
                "success" => false,
                "error"   => "Realisasi melebihi anggaran bulanan!"
            ]);
            exit;
        }

        // Jika lolos validasi → update
        if ($tanggal === null) {
            $q = $k->prepare("UPDATE realisasi_detail SET jumlah_realisasi = ?, tanggal = NULL, catatan = ? WHERE id_detail = ?");
            $q->bind_param("dsi", $jumlah, $note, $id);
        } else {
            $q = $k->prepare("UPDATE realisasi_detail SET jumlah_realisasi = ?, tanggal = ?, catatan = ? WHERE id_detail = ?");
            $q->bind_param("dssi", $jumlah, $tanggal, $note, $id);
        }
        break;

        if ($tanggal === null) {
            $q = $k->prepare("UPDATE realisasi_detail SET jumlah_realisasi = ?, tanggal = NULL, catatan = ? WHERE id_detail = ?");
            $q->bind_param("dsi", $jumlah, $note, $id);
        } else {
            $q = $k->prepare("UPDATE realisasi_detail SET jumlah_realisasi = ?, tanggal = ?, catatan = ? WHERE id_detail = ?");
            $q->bind_param("dssi", $jumlah, $tanggal, $note, $id);
        }
        break;

    case 'Anggaran Tahunan':
        $q = $k->prepare("UPDATE anggaran_tahunan SET nilai_tahunan = ? WHERE id = ?");
        $q->bind_param("di", $jumlah, $id);
        break;

    case 'Anggaran Bulanan':
        $q = $k->prepare("UPDATE anggaran SET nilai_bulanan = ?, updated_at = NOW() WHERE id_anggaran = ?");
        $q->bind_param("di", $jumlah, $id);
        break;

    default:
        echo json_encode(["success" => false, "error" => "Jenis tidak dikenal"]);
        exit;
}

if (!$q) {
    echo json_encode(["success" => false, "error" => "Query tidak dibuat"]);
    exit;
}

if ($q->execute()) {
    $response = ["success" => true, "jenis" => $jenis, "new_value" => $jumlah];

    if ($jenis === 'Anggaran Tahunan') {
        $qq = $k->prepare("
            SELECT p.nama_program, r.kode_rekening, r.nama_rekening
            FROM anggaran_tahunan a
            JOIN rekening r ON r.id_rekening = a.id_rekening
            JOIN subkegiatan s ON s.id_subkegiatan = r.id_subkegiatan
            JOIN kegiatan k ON k.id_kegiatan = s.id_kegiatan
            JOIN program p ON p.id_program = k.id_program
            WHERE a.id = ?
        ");
        $qq->bind_param("i", $id);
        $qq->execute();
        $row = $qq->get_result()->fetch_assoc();
        $response["program"] = $row["nama_program"];
        $response["kode"] = $row["kode_rekening"];
        $response["nama_rekening"] = $row["nama_rekening"];
    }

    if ($jenis === 'Anggaran Bulanan') {
        $qq = $k->prepare("
            SELECT p.nama_program, r.kode_rekening, r.nama_rekening
            FROM anggaran ab
            JOIN rekening r ON r.id_rekening = ab.id_rekening
            JOIN subkegiatan s ON s.id_subkegiatan = r.id_subkegiatan
            JOIN kegiatan k ON k.id_kegiatan = s.id_kegiatan
            JOIN program p ON p.id_program = k.id_program
            WHERE ab.id_anggaran = ?
        ");
        $qq->bind_param("i", $id);
        $qq->execute();
        $row = $qq->get_result()->fetch_assoc();
        $response["program"] = $row["nama_program"];
        $response["kode"] = $row["kode_rekening"];
        $response["nama_rekening"] = $row["nama_rekening"];
    }

    if ($jenis === 'Realisasi') {
        $r = $k->prepare("SELECT tanggal FROM realisasi_detail WHERE id_detail = ?");
        $r->bind_param("i", $id);
        $r->execute();
        $res = $r->get_result();
        $row = $res->fetch_assoc();
        $response['new_tanggal'] = $row ? $row['tanggal'] : null;
    } elseif ($jenis === 'Anggaran Bulanan') {
        $r = $k->prepare("SELECT created_at, updated_at FROM anggaran WHERE id_anggaran = ?");
        $r->bind_param("i", $id);
        $r->execute();
        $res = $r->get_result();
        $row = $res->fetch_assoc();
        $response['new_tanggal'] = $row ? ($row['updated_at'] ?? $row['created_at']) : null;
    }

    echo json_encode($response);
} else {
    echo json_encode(["success" => false, "error" => $k->error]);
}