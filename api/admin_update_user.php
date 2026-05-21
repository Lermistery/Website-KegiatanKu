<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php'; 

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['role'])) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit;
}

$id = (int) $data['id'];
$role = $conn->real_escape_string($data['role']);

$sql = "UPDATE users SET role='$role' WHERE id=$id";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Role berhasil diubah']);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal mengubah role: ' . $conn->error]);
}
?>
