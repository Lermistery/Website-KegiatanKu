<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$password) {
    respond(['error' => 'Email dan password wajib diisi.'], 400);
}

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if ($data && password_verify($password, $data['password'])) {
    respond([
        'success' => true,
        'user' => [
            'id' => (int)$data['id'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'user',
            'poin' => (int)($data['poin'] ?? 0)
        ]
    ]);
} else {
    respond(['error' => 'Email atau password salah.'], 401);
}
?>
