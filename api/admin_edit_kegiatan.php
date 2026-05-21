<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID tidak ditemukan']);
    exit;
}

$id = (int) $data['id'];
$judul = $conn->real_escape_string($data['judul']);
$deskripsi = $conn->real_escape_string($data['deskripsi']);
$tanggal = $conn->real_escape_string($data['tanggal']);
$lokasi = $conn->real_escape_string($data['lokasi']);
$kuota = (int) $data['kuota'];
$gambar = $conn->real_escape_string($data['gambar'] ?? '');

$sql = "UPDATE kegiatan SET 
        judul='$judul', deskripsi='$deskripsi', tanggal='$tanggal', 
        lokasi='$lokasi', kuota=$kuota, gambar='$gambar' 
        WHERE id=$id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Kegiatan berhasil diupdate']);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal mengupdate kegiatan: ' . $conn->error]);
}
?>
