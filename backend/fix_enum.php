<?php
include 'backend/koneksi.php';

// Change ENUM to match our PHP code
$query = "ALTER TABLE pendaftaran MODIFY COLUMN status ENUM('pending', 'Diterima', 'Ditolak') DEFAULT 'pending'";
if (mysqli_query($conn, $query)) {
    echo "Kolom status berhasil diperbarui.\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

// Update blank status to pending
$update_blank = "UPDATE pendaftaran SET status = 'pending' WHERE status = '' OR status IS NULL";
mysqli_query($conn, $update_blank);

// Dump again
$q = mysqli_query($conn, 'SELECT * FROM pendaftaran');
while ($row = mysqli_fetch_assoc($q)) { print_r($row); }
?>
