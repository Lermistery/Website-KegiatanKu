<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'admin@kegiatanku.com') {
    http_response_code(403);
    echo 'forbidden';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id']) || empty($_POST['aksi'])) {
    http_response_code(400);
    echo 'bad request';
    exit;
}

$id   = (int)$_POST['id'];
$aksi = $_POST['aksi'];

if (!in_array($aksi, ['verified', 'rejected'])) {
    http_response_code(400);
    echo 'invalid action';
    exit;
}

$update = mysqli_prepare($conn, "UPDATE pendaftaran SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($update, 'si', $aksi, $id);

if (mysqli_stmt_execute($update)) {
    // GAMIFIKASI: Beri poin ke user
    $q_pendaftaran = mysqli_query($conn, "SELECT user_id, poin_diberikan FROM pendaftaran WHERE id = $id");
    if($q_pendaftaran && mysqli_num_rows($q_pendaftaran) > 0) {
        $data_p = mysqli_fetch_assoc($q_pendaftaran);
        $user_id = $data_p['user_id'];
        $poin_diberikan = $data_p['poin_diberikan'];

        if ($poin_diberikan == 0) {
            $tambah_poin = ($aksi == 'verified') ? 5 : 2;
            mysqli_query($conn, "UPDATE users SET poin = poin + $tambah_poin WHERE id = $user_id");
            mysqli_query($conn, "UPDATE pendaftaran SET poin_diberikan = 1 WHERE id = $id");
        }
    }
    echo 'ok';
} else {
    http_response_code(500);
    echo 'error';
}
?>
