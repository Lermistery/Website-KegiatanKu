<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user']['id'];
    $nama_hadiah = $_POST['hadiah'] ?? '';
    $harga = (int)($_POST['harga'] ?? 0);

    if ($harga <= 0 || empty($nama_hadiah)) {
        echo "<script>alert('Data hadiah tidak valid!'); window.location.href='../index.php#profile-section';</script>";
        exit;
    }

    // Cek poin user
    $poin_query = mysqli_query($conn, "SELECT poin FROM users WHERE id = $user_id");
    if ($poin_query && mysqli_num_rows($poin_query) > 0) {
        $user_poin = mysqli_fetch_assoc($poin_query)['poin'];

        if ($user_poin >= $harga) {
            // Kurangi poin
            mysqli_query($conn, "UPDATE users SET poin = poin - $harga WHERE id = $user_id");
            
            // Catat di tabel penukaran_hadiah
            $stmt = mysqli_prepare($conn, "INSERT INTO penukaran_hadiah (user_id, nama_hadiah, poin_digunakan) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isi", $user_id, $nama_hadiah, $harga);
            mysqli_stmt_execute($stmt);

            echo "<script>alert('Berhasil menukarkan $harga poin dengan $nama_hadiah! Cek email untuk instruksi pengambilan.'); window.location.href='../index.php#profile-section';</script>";
        } else {
            echo "<script>alert('Poin kamu tidak cukup! Kamu butuh $harga poin.'); window.location.href='../index.php#profile-section';</script>";
        }
    } else {
        echo "<script>alert('Terjadi kesalahan saat mengecek poin.'); window.location.href='../index.php#profile-section';</script>";
    }
} else {
    header("Location: ../index.php");
}
?>
