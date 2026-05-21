<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include 'config.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'User ID diperlukan']);
    exit;
}

$poin_user = 0;
$poin_query = @$conn->query("SELECT poin FROM users WHERE id = $user_id");
if ($poin_query && $poin_query->num_rows > 0) {
    $poin_user = (int)$poin_query->fetch_assoc()['poin'];
}

$bulan_ini = date('Y-m');
$kegiatan_bulan_ini = 0;
$keg_bulan_query = $conn->query("SELECT COUNT(*) as total FROM pendaftaran p JOIN kegiatan k ON p.kegiatan_id = k.id WHERE p.user_id = $user_id AND DATE_FORMAT(k.tanggal, '%Y-%m') = '$bulan_ini'");
if ($keg_bulan_query) {
    $kegiatan_bulan_ini = (int)$keg_bulan_query->fetch_assoc()['total'];
}

echo json_encode([
    'success' => true,
    'poin' => $poin_user,
    'kegiatan_bulan_ini' => $kegiatan_bulan_ini
]);
?>
