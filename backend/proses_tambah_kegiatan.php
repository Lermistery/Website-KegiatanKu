<?php
include 'koneksi.php';

$nama = mysqli_real_escape_string($conn, $_POST['nama_kegiatan']);
$lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
$tanggal = $_POST['tanggal'];
$deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
$kuota = isset($_POST['kuota']) ? (int) $_POST['kuota'] : 0;

$result = mysqli_query($conn, "SHOW COLUMNS FROM kegiatan LIKE 'kuota'");
$hasKuota = $result && mysqli_num_rows($result) > 0;

if ($hasKuota) {
    $sql = "INSERT INTO kegiatan (nama_kegiatan, lokasi, tanggal, deskripsi, kuota) VALUES ('$nama','$lokasi','$tanggal','$deskripsi','$kuota')";
} else {
    $sql = "INSERT INTO kegiatan (nama_kegiatan, lokasi, tanggal, deskripsi) VALUES ('$nama','$lokasi','$tanggal','$deskripsi')";
}

if (!mysqli_query($conn, $sql)) {
    die("Error menambahkan kegiatan: " . mysqli_error($conn));
}

echo "<script>alert('Kegiatan berhasil ditambahkan'); window.location='index.php';</script>";
?>