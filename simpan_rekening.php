<?php
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { 
    die("DB fail: ".$k->connect_error); 
}

$id_sub = (int)$_POST['id_subkegiatan'];
$kode   = $k->real_escape_string($_POST['kode']);
$nama   = $k->real_escape_string($_POST['nama']);
$is_active  = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

$cek = $k->query("
    SELECT id_rekening FROM rekening 
    WHERE id_subkegiatan=$id_subkegiatan
      AND (kode_rekening='$kode' OR nama_rekening='$nama')
    LIMIT 1
");

if ($cek->num_rows > 0) {
    echo "<script>
            alert('Gagal! Rekening dengan kode atau nama tersebut sudah ada.');
            window.history.back();
        </script>";
    exit;
}

$sql = "INSERT INTO rekening (id_subkegiatan, kode_rekening, nama_rekening, is_active)
        VALUES ($id_sub, '$kode', '$nama', $is_active)";

if ($k->query($sql)) {
    echo "<script>
            alert('Rekening berhasil ditambahkan.');
            window.location='manajemen_rekening.php';
          </script>";
} else {
    echo "<script>
            alert('Gagal simpan rekening: " . addslashes($k->error) . "');
            window.history.back();
          </script>";
}