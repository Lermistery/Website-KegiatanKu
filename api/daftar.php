<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = (int)($input['user_id'] ?? 0);
$kegiatan_id = (int)($input['kegiatan_id'] ?? 0);

if (!$user_id || !$kegiatan_id) {
    respond(['error' => 'Data tidak lengkap.'], 400);
}

// Check if already registered
$cek = mysqli_prepare($conn, "SELECT id FROM pendaftaran WHERE user_id = ? AND kegiatan_id = ?");
mysqli_stmt_bind_param($cek, 'ii', $user_id, $kegiatan_id);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);

if (mysqli_stmt_num_rows($cek) > 0) {
    respond(['error' => 'Kamu sudah mendaftar ke kegiatan ini.'], 409);
}

// Check if kegiatan is still open
$cek_kegiatan = mysqli_prepare($conn, "SELECT tanggal FROM kegiatan WHERE id = ?");
mysqli_stmt_bind_param($cek_kegiatan, 'i', $kegiatan_id);
mysqli_stmt_execute($cek_kegiatan);
$result = mysqli_stmt_get_result($cek_kegiatan);
$kegiatan = mysqli_fetch_assoc($result);

if (!$kegiatan) {
    respond(['error' => 'Kegiatan tidak ditemukan.'], 404);
}

if (strtotime($kegiatan['tanggal']) < strtotime(date('Y-m-d'))) {
    respond(['error' => 'Kegiatan sudah tutup.'], 400);
}

$insert = mysqli_prepare($conn, "INSERT INTO pendaftaran (user_id, kegiatan_id, status) VALUES (?, ?, 'pending')");
mysqli_stmt_bind_param($insert, 'ii', $user_id, $kegiatan_id);

if (mysqli_stmt_execute($insert)) {
    respond(['success' => true, 'message' => 'Pendaftaran berhasil! Tunggu verifikasi admin.']);
} else {
    respond(['error' => 'Gagal mendaftar.'], 500);
}
?>
