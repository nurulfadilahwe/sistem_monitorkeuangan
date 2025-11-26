<?php
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { 
    die("DB fail: " . $k->connect_error); 
}

$id_kegiatan = (int)$_POST['id_kegiatan'];
$kode        = $k->real_escape_string($_POST['kode']);
$nama        = $k->real_escape_string($_POST['nama']);
$is_active   = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

$cek = $k->query("
    SELECT id_subkegiatan FROM subkegiatan 
    WHERE id_kegiatan=$id_kegiatan
      AND (kode_subkegiatan='$kode' OR nama_subkegiatan='$nama')
    LIMIT 1
");

if ($cek->num_rows > 0) {
    echo "<script>
            alert('Gagal! SubKegiatan dengan kode atau nama tersebut sudah ada.');
            window.history.back();
        </script>";
    exit;
}

$sql = "INSERT INTO subkegiatan (id_kegiatan, kode_subkegiatan, nama_subkegiatan, is_active)
        VALUES ($id_kegiatan, '$kode', '$nama', $is_active)";

if ($k->query($sql)) {
    echo "<script>
            alert('Subkegiatan berhasil ditambahkan.');
            window.location='manajemen_subkegiatan.php';
          </script>";
} else {
    echo "<script>
            alert('Gagal simpan subkegiatan: " . addslashes($k->error) . "');
            window.history.back();
          </script>";
}