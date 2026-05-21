<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['nama_kegiatan']) || !isset($data['deskripsi'])) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit;
}

$nama_kegiatan = $conn->real_escape_string($data['nama_kegiatan']);
$deskripsi = $conn->real_escape_string($data['deskripsi']);
$tanggal = $conn->real_escape_string($data['tanggal']);
$lokasi = $conn->real_escape_string($data['lokasi']);
$kuota = (int) $data['kuota'];
$status = $conn->real_escape_string($data['status'] ?? '');
$waktu_mulai = (int) $data['waktu_mulai'];
$waktu_selesai = (int) $data['waktu_selesai'];

$sql = "INSERT INTO kegiatan (nama_kegiatan, deskripsi, tanggal, lokasi, kuota, status, waktu_mulai, waktu_selesai) 
        VALUES ('$nama_kegiatan', '$deskripsi', '$tanggal', '$lokasi', $kuota, '$status', waktu_mulai, waktu_selesai)";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Kegiatan berhasil ditambahkan']);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal menambah kegiatan: ' . $conn->error]);
}
?>
