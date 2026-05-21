<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'admin@kegiatanku.com') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'tambah_kegiatan':
        $nama      = trim($_POST['nama_kegiatan'] ?? '');
        $lokasi    = trim($_POST['lokasi'] ?? '');
        $tanggal   = $_POST['tanggal'] ?? '';
        $waktu_mulai = !empty($_POST['waktu_mulai']) ? $_POST['waktu_mulai'] : null;
        $waktu_selesai = !empty($_POST['waktu_selesai']) ? $_POST['waktu_selesai'] : null;
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $kuota     = (int)($_POST['kuota'] ?? 0);

        if (!$nama || !$lokasi || !$tanggal) {
            echo json_encode(['error' => 'Nama, lokasi, dan tanggal wajib diisi.']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO kegiatan (nama_kegiatan, lokasi, tanggal, waktu_mulai, waktu_selesai, deskripsi, kuota, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'aktif')");
        mysqli_stmt_bind_param($stmt, 'ssssssi', $nama, $lokasi, $tanggal, $waktu_mulai, $waktu_selesai, $deskripsi, $kuota);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['error' => 'Gagal menambahkan: ' . mysqli_error($conn)]);
        }
        break;

    case 'edit_kegiatan':
        $id        = (int)($_POST['id'] ?? 0);
        $nama      = trim($_POST['nama_kegiatan'] ?? '');
        $lokasi    = trim($_POST['lokasi'] ?? '');
        $tanggal   = $_POST['tanggal'] ?? '';
        $waktu_mulai = !empty($_POST['waktu_mulai']) ? $_POST['waktu_mulai'] : null;
        $waktu_selesai = !empty($_POST['waktu_selesai']) ? $_POST['waktu_selesai'] : null;
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $kuota     = (int)($_POST['kuota'] ?? 0);
        $status    = $_POST['status'] ?? 'aktif';

        if (!$id || !$nama || !$lokasi || !$tanggal) {
            echo json_encode(['error' => 'Data tidak lengkap.']);
            exit;
        }

        $stmt = mysqli_prepare($conn, "UPDATE kegiatan SET nama_kegiatan=?, lokasi=?, tanggal=?, waktu_mulai=?, waktu_selesai=?, deskripsi=?, kuota=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssssssisi', $nama, $lokasi, $tanggal, $waktu_mulai, $waktu_selesai, $deskripsi, $kuota, $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Gagal mengubah: ' . mysqli_error($conn)]);
        }
        break;

    case 'hapus_kegiatan':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'ID tidak valid.']); exit; }

        // Hapus pendaftaran terkait dulu
        mysqli_query($conn, "DELETE FROM pendaftaran WHERE kegiatan_id = $id");
        $stmt = mysqli_prepare($conn, "DELETE FROM kegiatan WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Gagal menghapus: ' . mysqli_error($conn)]);
        }
        break;

    case 'get_kegiatan':
        $id = (int)($_GET['id'] ?? 0);
        $result = mysqli_query($conn, "SELECT * FROM kegiatan WHERE id = $id");
        $data = mysqli_fetch_assoc($result);
        echo json_encode($data ?: ['error' => 'Tidak ditemukan']);
        break;

    // ========== PENGGUNA ==========

    case 'hapus_user':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'ID tidak valid.']); exit; }

        // Jangan hapus admin sendiri
        $cek = mysqli_query($conn, "SELECT email FROM users WHERE id = $id");
        $user = mysqli_fetch_assoc($cek);
        if ($user && $user['email'] === 'admin@kegiatanku.com') {
            echo json_encode(['error' => 'Tidak bisa menghapus akun admin.']);
            exit;
        }

        mysqli_query($conn, "DELETE FROM pendaftaran WHERE user_id = $id");
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Gagal menghapus: ' . mysqli_error($conn)]);
        }
        break;

    default:
        echo json_encode(['error' => 'Aksi tidak dikenali.']);
        break;
}
?>
