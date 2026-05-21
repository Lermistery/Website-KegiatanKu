<?php
session_start();
include 'backend/koneksi.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['kegiatan_id'])) {
    header('Location: index.php');
    exit;
}

$user_id     = (int)$_SESSION['user']['id'];
$kegiatan_id = (int)$_POST['kegiatan_id'];

$cek = mysqli_prepare($conn, "SELECT id FROM pendaftaran WHERE user_id = ? AND kegiatan_id = ?");
mysqli_stmt_bind_param($cek, 'ii', $user_id, $kegiatan_id);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);

if (mysqli_stmt_num_rows($cek) > 0) {
    echo "<script>alert('Kamu sudah mendaftar ke kegiatan ini.'); window.location='index.php';</script>";
    exit;
}

// Cek kegiatan masih buka
$cek_kegiatan = mysqli_prepare($conn, "SELECT tanggal FROM kegiatan WHERE id = ?");
mysqli_stmt_bind_param($cek_kegiatan, 'i', $kegiatan_id);
mysqli_stmt_execute($cek_kegiatan);
$result = mysqli_stmt_get_result($cek_kegiatan);
$kegiatan = mysqli_fetch_assoc($result);

if (!$kegiatan) {
    echo "<script>alert('Kegiatan tidak ditemukan.'); window.location='index.php';</script>";
    exit;
}

if (strtotime($kegiatan['tanggal']) < strtotime(date('Y-m-d'))) {
    echo "<script>alert('Kegiatan sudah tutup, pendaftaran ditolak.'); window.location='index.php';</script>";
    exit;
}

// Simpan pendaftaran dengan status pending
$insert = mysqli_prepare($conn, "INSERT INTO pendaftaran (user_id, kegiatan_id, status) VALUES (?, ?, 'pending')");
mysqli_stmt_bind_param($insert, 'ii', $user_id, $kegiatan_id);

if (mysqli_stmt_execute($insert)) {
    echo "<script>alert('Pendaftaran berhasil! Tunggu verifikasi dari admin.'); window.location='index.php';</script>";
} else {
    echo "<script>alert('Terjadi kesalahan, coba lagi.'); window.location='index.php';</script>";
}
?>
