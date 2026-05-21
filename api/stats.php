<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include 'config.php';

// Hitung total kegiatan keseluruhan (buat Admin)
$res_total_keg = $conn->query("SELECT COUNT(id) as total FROM kegiatan");
$total_kegiatan = ($res_total_keg && $res_total_keg->num_rows > 0) ? $res_total_keg->fetch_assoc()['total'] : 0;

// Hitung kegiatan yang aktif / Buka (buat User)
$res_keg_aktif = $conn->query("SELECT COUNT(id) as total FROM kegiatan WHERE status='Buka' OR status='aktif'");
$kegiatan_aktif = ($res_keg_aktif && $res_keg_aktif->num_rows > 0) ? $res_keg_aktif->fetch_assoc()['total'] : 0;

// Hitung kegiatan yang selesai (buat User)
$res_keg_selesai = $conn->query("SELECT COUNT(id) as total FROM kegiatan WHERE status='selesai'");
$kegiatan_selesai = ($res_keg_selesai && $res_keg_selesai->num_rows > 0) ? $res_keg_selesai->fetch_assoc()['total'] : 0;

// Hitung total pengguna keseluruhan (buat Admin & User)
$res_user = $conn->query("SELECT COUNT(id) as total FROM users");
$total_user = ($res_user && $res_user->num_rows > 0) ? $res_user->fetch_assoc()['total'] : 0;

// Hitung total pendaftar di semua kegiatan (buat User)
$res_pendaftar = $conn->query("SELECT COUNT(id) as total FROM pendaftaran");
$total_pendaftar = ($res_pendaftar && $res_pendaftar->num_rows > 0) ? $res_pendaftar->fetch_assoc()['total'] : 0;

echo json_encode([
    'success' => true,
    // Buat Admin Dashboard
    'total_kegiatan' => (int) $total_kegiatan,
    'total_user' => (int) $total_user,
    
    // Buat User Dashboard (Home Screen)
    'kegiatan_aktif' => (int) $kegiatan_aktif,
    'kegiatan_selesai' => (int) $kegiatan_selesai,
    'total_pengguna' => (int) $total_user, 
    'total_pendaftar' => (int) $total_pendaftar
]);
?>
