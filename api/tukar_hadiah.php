<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['hadiah']) || !isset($data['harga'])) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit;
}

$user_id = (int) $data['user_id'];
$nama_hadiah = $conn->real_escape_string($data['hadiah']);
$harga = (int) $data['harga'];

// Cek poin user
$poin_query = $conn->query("SELECT poin FROM users WHERE id = $user_id");
if ($poin_query && $poin_query->num_rows > 0) {
    $user_poin = (int)$poin_query->fetch_assoc()['poin'];

    if ($user_poin >= $harga) {
        // Kurangi poin
        $conn->query("UPDATE users SET poin = poin - $harga WHERE id = $user_id");
        
        // Catat di tabel penukaran_hadiah
        $stmt = $conn->prepare("INSERT INTO penukaran_hadiah (user_id, nama_hadiah, poin_digunakan) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $nama_hadiah, $harga);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => "Berhasil menukarkan $harga poin dengan $nama_hadiah!"]);
    } else {
        echo json_encode(['success' => false, 'error' => "Poin tidak cukup! Kamu butuh $harga poin."]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal mengecek poin user']);
}
?>
