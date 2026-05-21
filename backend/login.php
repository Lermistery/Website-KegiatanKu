<?php
session_start();
include 'koneksi.php';

$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

$query = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
mysqli_stmt_bind_param($query, 's', $email);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
$data = mysqli_fetch_assoc($result);

if ($data) {
    if (password_verify($password, $data['password'])) {
        $_SESSION['user'] = [
            'id' => $data['id'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'user'
        ];
        echo "<script>alert('Login berhasil'); window.location='../index.php';</script>";
    } else {
        echo "<script>alert('Password salah'); window.location='../index.php';</script>";
    }
} else {
    echo "<script>alert('Email tidak ditemukan'); window.location='../index.php';</script>";
}
?>