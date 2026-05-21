<?php
include 'koneksi.php';

$nama = mysqli_real_escape_string($conn, $_POST['nama']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if ($password !== $confirm_password) {
    echo "<script>alert('Password dan konfirmasi password tidak cocok'); window.location='../index.php';</script>";
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$query = mysqli_prepare($conn, "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($query, 'sss', $nama, $email, $hashed_password);

if (mysqli_stmt_execute($query)) {
    echo "<script>alert('Register berhasil! Silakan login.'); window.location='../index.php';</script>";
} else {
    echo "<script>alert('Register gagal: " . mysqli_error($conn) . "'); window.location='../index.php';</script>";
}
?>