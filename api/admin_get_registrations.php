<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include 'config.php';

// JOIN tabel pendaftaran dengan users dan kegiatan biar dapet nama asli
$sql = "SELECT p.id, p.status, p.created_at, u.nama as nama_user, k.nama_kegiatan 
        FROM pendaftaran p 
        JOIN users u ON p.user_id = u.id 
        JOIN kegiatan k ON p.kegiatan_id = k.id 
        ORDER BY p.id DESC";
        
$result = $conn->query($sql);

$registrations = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $registrations[] = $row;
    }
}

echo json_encode(['success' => true, 'registrations' => $registrations]);
?>
