<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit;
}

$id = (int) $data['id'];
$status = $conn->real_escape_string($data['status']);

$sql = "UPDATE pendaftaran SET status='$status' WHERE id=$id";

if ($conn->query($sql)) {
    // GAMIFIKASI: Beri poin ke user
    $q_pendaftaran = $conn->query("SELECT user_id, poin_diberikan FROM pendaftaran WHERE id = $id");
    if($q_pendaftaran && $q_pendaftaran->num_rows > 0) {
        $data_p = $q_pendaftaran->fetch_assoc();
        $user_id = $data_p['user_id'];
        $poin_diberikan = $data_p['poin_diberikan'];

        if ($poin_diberikan == 0) {
            $tambah_poin = ($status == 'verified') ? 5 : 2;
            $conn->query("UPDATE users SET poin = poin + $tambah_poin WHERE id = $user_id");
            $conn->query("UPDATE pendaftaran SET poin_diberikan = 1 WHERE id = $id");
        }
    }
    echo json_encode(['success' => true, 'message' => 'Status berhasil diubah']);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>
