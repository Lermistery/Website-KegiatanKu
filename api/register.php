<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$nama = trim($input['nama'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

if (!$nama || !$email || !$password) {
    respond(['error' => 'Semua field wajib diisi.'], 400);
}

if ($password !== $confirm_password) {
    respond(['error' => 'Password dan konfirmasi password tidak cocok.'], 400);
}

// Check if email already exists
$cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($cek, 's', $email);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);

if (mysqli_stmt_num_rows($cek) > 0) {
    respond(['error' => 'Email sudah terdaftar.'], 409);
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'sss', $nama, $email, $hashed);

if (mysqli_stmt_execute($stmt)) {
    respond(['success' => true, 'message' => 'Register berhasil!']);
} else {
    respond(['error' => 'Register gagal: ' . mysqli_error($conn)], 500);
}
?>
