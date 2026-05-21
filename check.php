<?php
include 'backend/koneksi.php';
$q = mysqli_query($conn, 'SELECT * FROM pendaftaran');
while ($row = mysqli_fetch_assoc($q)) { print_r($row); }
echo "Session user:\n";
session_start();
print_r($_SESSION ?? []);
?>
