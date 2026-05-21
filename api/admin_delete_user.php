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

$sql = "DELETE FROM users WHERE id=$id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Pengguna berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal menghapus pengguna: ' . $conn->error]);
}
?>
