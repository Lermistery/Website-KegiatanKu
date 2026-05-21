<?php include 'koneksi.php'; ?>

<form action="proses_tambah_kegiatan.php" method="POST">
    <input type="text" name="nama_kegiatan" placeholder="Nama Kegiatan" class="form-control mb-2">
    <input type="text" name="lokasi" placeholder="Lokasi" class="form-control mb-2">
    <input type="date" name="tanggal" class="form-control mb-2">
    <input type="number" name="kuota" placeholder="Kuota" class="form-control mb-2">
    <textarea name="deskripsi" class="form-control mb-2"></textarea>

    <button class="btn btn-success">Tambah</button>
</form>