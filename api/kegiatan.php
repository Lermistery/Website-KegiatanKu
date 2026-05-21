<?php
require_once 'config.php';

$hari_ini = date('Y-m-d');
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$search = trim($_GET['search'] ?? '');

// Build query
$sql = "SELECT k.*, 
    (SELECT COUNT(*) FROM pendaftaran p WHERE p.kegiatan_id = k.id AND p.status NOT IN ('rejected', 'Ditolak')) AS jumlah_pendaftar
    FROM kegiatan k";

if ($search) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $sql .= " WHERE k.nama_kegiatan LIKE '%$search_escaped%' OR k.lokasi LIKE '%$search_escaped%'";
}

$sql .= " ORDER BY k.id DESC";

$query = mysqli_query($conn, $sql);
$kegiatan = [];

while ($row = mysqli_fetch_assoc($query)) {
    $item = [
        'id' => (int)$row['id'],
        'nama_kegiatan' => $row['nama_kegiatan'],
        'lokasi' => $row['lokasi'],
        'tanggal' => $row['tanggal'],
        'waktu_mulai' => $row['waktu_mulai'],
        'waktu_selesai' => $row['waktu_selesai'],
        'deskripsi' => $row['deskripsi'],
        'kuota' => (int)$row['kuota'],
        'status' => $row['status'],
        'jumlah_pendaftar' => (int)$row['jumlah_pendaftar'],
        'is_open' => strtotime($row['tanggal']) >= strtotime($hari_ini),
    ];

    // Check user's registration status if user_id provided
    if ($user_id > 0) {
        $cek = mysqli_query($conn, "SELECT status FROM pendaftaran WHERE user_id = $user_id AND kegiatan_id = {$row['id']}");
        $daftar = mysqli_fetch_assoc($cek);
        $item['pendaftaran_status'] = $daftar ? $daftar['status'] : null;
    } else {
        $item['pendaftaran_status'] = null;
    }

    $kegiatan[] = $item;
}

respond(['kegiatan' => $kegiatan]);
?>
