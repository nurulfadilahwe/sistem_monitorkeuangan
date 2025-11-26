<?php
$k = new mysqli("localhost","root","","monitor_keuangan");
if ($k->connect_error) { 
    die("DB fail: " . $k->connect_error); 
}

$kode      = $k->real_escape_string($_POST['kode']);
$nama      = $k->real_escape_string($_POST['nama']);
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

// CEK DUPLIKAT (kode atau nama)
$cek = $k->query("SELECT id_program FROM program 
                WHERE kode_program='$kode' OR nama_program='$nama' 
                LIMIT 1");

if ($cek->num_rows > 0) {
    echo "<script>
            alert('Gagal! Program dengan kode atau nama tersebut sudah ada.');
            window.history.back();
        </script>";
    exit;
}

// INSERT jika tidak duplikat
$sql = "INSERT INTO program (kode_program, nama_program, is_active)
        VALUES ('$kode', '$nama', $is_active)";

if ($k->query($sql)) {
    echo "<script>
            alert('Program berhasil ditambahkan.');
            window.location='manajemen_program.php';
          </script>";
} else {
    echo "<script>
            alert('Gagal simpan program: " . addslashes($k->error) . "');
            window.history.back();
          </script>";
}