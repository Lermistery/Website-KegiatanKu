<?php
include 'koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM kegiatan");

while ($row = mysqli_fetch_assoc($query)) {
    echo "
    <div class='col-lg-4'>
        <div class='activity-card'>
            <div class='card-body'>
                <h5>{$row['nama_kegiatan']}</h5>
                <p>{$row['lokasi']}</p>
            </div>
        </div>
    </div>
    ";
}
?>