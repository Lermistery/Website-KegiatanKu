<?php
require_once 'config.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$user_id) {
    respond(['error' => 'User ID diperlukan.'], 400);
}

$query = mysqli_query($conn, "SELECT k.nama_kegiatan, k.tanggal, k.lokasi, k.waktu_mulai, k.waktu_selesai, p.status, p.created_at 
    FROM pendaftaran p 
    JOIN kegiatan k ON p.kegiatan_id = k.id 
    WHERE p.user_id = $user_id 
    ORDER BY p.id DESC");

$riwayat = [];
while ($row = mysqli_fetch_assoc($query)) {
    $riwayat[] = [
        'nama_kegiatan' => $row['nama_kegiatan'],
        'tanggal' => $row['tanggal'],
        'lokasi' => $row['lokasi'],
        'waktu_mulai' => $row['waktu_mulai'],
        'waktu_selesai' => $row['waktu_selesai'],
        'status' => $row['status'],
        'created_at' => $row['created_at'],
    ];
}

respond(['riwayat' => $riwayat]);
?>
