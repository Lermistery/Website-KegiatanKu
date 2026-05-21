<?php
session_start();
require_once 'backend/koneksi.php';

$hari_ini = date('Y-m-d');
$query_aktif = mysqli_query($conn, "SELECT COUNT(*) as total FROM kegiatan WHERE tanggal >= '$hari_ini'");
$kegiatan_aktif = mysqli_fetch_assoc($query_aktif)['total'];

$query_pendaftar = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftaran");
$total_pendaftar = mysqli_fetch_assoc($query_pendaftar)['total'];

$query_pengguna = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_pengguna = mysqli_fetch_assoc($query_pengguna)['total'];

$query_selesai = mysqli_query($conn, "SELECT COUNT(*) as total FROM kegiatan WHERE tanggal < '$hari_ini'");
$kegiatan_selesai = mysqli_fetch_assoc($query_selesai)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KegiatanKu - Platform Manajemen Kegiatan Sosial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-logo">KegiatanKu</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-link active" data-page="dashboard">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <a href="#kegiatan-section" class="nav-link" data-page="kegiatan">
                <i class="bi bi-calendar-event"></i> Kegiatan
            </a>
            <a href="#riwayat-section" class="nav-link" data-page="riwayat">
                <i class="bi bi-clock-history"></i> Riwayat
            </a>
            <?php if(isset($_SESSION['user']) && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] == 'admin'): ?>
            <a href="backend/admin.php" class="nav-link text-warning fw-bold">
                <i class="bi bi-speedometer2"></i> Panel Admin
            </a>
            <?php endif; ?>
            <?php if(isset($_SESSION['user'])): ?>
            <a href="#profile-section" class="nav-link" data-page="profile">
                <i class="bi bi-person-circle"></i> Profile & Hadiah
            </a>
            <?php endif; ?>
        </nav>
    </div>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button class="btn btn-outline-secondary d-md-none" id="sidebar-toggle">
                    <i class="bi bi-list"></i>
                </button>
                <div class="search-bar">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari kegiatan...">
                    <button class="btn btn-primary" id="searchBtn">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="topbar-right">
                <div class="user-info">
                    <?php if(isset($_SESSION['user'])): ?>
                        <span class="user-name"><?php echo $_SESSION['user']['nama']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="theme-toggle">
                    <label class="switch">
                        <input type="checkbox" id="theme-switch">
                        <span class="slider"></span>
                    </label>
                </div>
                <?php if(isset($_SESSION['user'])): ?>
                    <a href="backend/logout.php" class="btn btn-danger ms-3">Logout</a>
                <?php else: ?>
                    <button type="button" class="btn btn-outline-primary ms-3" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                <?php endif; ?>
            </div>
        </header>

        <main class="main-content">
            <div class="container-fluid">
                <section class="hero-section" data-aos="fade-in">
                    <div class="hero-content">
                        <h1 class="hero-title">Bergabunglah dalam Kegiatan Sosial yang Bermakna</h1>
                        <p class="hero-subtitle">Temukan dan ikuti berbagai kegiatan sosial di sekitar Anda. Jadilah bagian dari perubahan positif untuk masyarakat.</p>
                        <?php if(!isset($_SESSION['user'])): ?>
                            <button class="btn btn-primary btn-lg hero-btn" data-bs-toggle="modal" data-bs-target="#loginModal">Mulai Sekarang</button>
                        <?php else: ?>
                            <a href="#kegiatan-section" class="btn btn-primary btn-lg hero-btn">Lihat Kegiatan</a>
                        <?php endif; ?>
                    </div>
                    <div class="hero-image">
                        <img src="images/download.jpg" alt="Hero Image" class="img-fluid">
                    </div>
                </section>

                <section class="stats-section" data-aos="fade-up" data-aos-delay="200">
                    <div class="row text-center">
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <h3 class="stat-number" data-target="<?= $kegiatan_aktif; ?>">0</h3>
                                <p class="stat-label">Kegiatan Aktif</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <h3 class="stat-number" data-target="<?= $total_pendaftar; ?>">0</h3>
                                <p class="stat-label">Total Pendaftar</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <h3 class="stat-number" data-target="<?= $total_pengguna; ?>">0</h3>
                                <p class="stat-label">Pengguna Terdaftar</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <h3 class="stat-number" data-target="<?= $kegiatan_selesai; ?>">0</h3>
                                <p class="stat-label">Kegiatan Selesai</p>
                            </div>
                        </div>
                    </div>
                </section>

                <h1 id="kegiatan-section" class="page-title" data-aos="fade-up" data-aos-delay="300">Dashboard Kegiatan Sosial</h1>
                <div class="row">
<?php
$query = mysqli_query($conn, "SELECT * FROM kegiatan ORDER BY id DESC");
if (!$query) {
    die('Error query kegiatan: ' . mysqli_error($conn));
}

while($row = mysqli_fetch_assoc($query)):
?>

<div class="col-lg-4 mb-4">
    <div class="activity-card">
        <div class="card-body">
            <h5><?= $row['nama_kegiatan']; ?></h5>
            <?php
    $tanggal_kegiatan = strtotime($row['tanggal']); // Ubah tanggal di DB jadi format waktu
    $hari_ini = strtotime(date('Y-m-d')); // Ambil tanggal hari ini (server)
    if ($tanggal_kegiatan >= $hari_ini) {
        $teks_status = "Buka";
        $warna_badge = "bg-success"; // Warna hijau bawaan Bootstrap
        $status_tombol = "";
    } else {
        $teks_status = "Tutup";
        $warna_badge = "bg-danger"; // Warna merah bawaan Bootstrap
        $status_tombol = "disabled";
    }
    
    $id_kegiatan = $row['id'];
    $count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftaran WHERE kegiatan_id = $id_kegiatan AND status NOT IN ('rejected', 'Ditolak')");
    $count_data = mysqli_fetch_assoc($count_query);
    $jumlah_pendaftar = $count_data['total'];
    $kuota_text = ($row['kuota'] > 0) ? $row['kuota'] : '-';
?>

<?php
    $waktu_text = "Belum ditentukan";
    if (!empty($row['waktu_mulai']) && !empty($row['waktu_selesai'])) {
        $waktu_text = date('H:i', strtotime($row['waktu_mulai'])) . ' - ' . date('H:i', strtotime($row['waktu_selesai'])) . ' WIB';
    } elseif (!empty($row['waktu_mulai'])) {
        $waktu_text = date('H:i', strtotime($row['waktu_mulai'])) . ' WIB';
    }
?>
<p>
    📍 <?= $row['lokasi']; ?><br>
    📅 <?= $row['tanggal']; ?><br>
    ⏰ <?= $waktu_text; ?><br>
    👥 Pendaftar: <?= $jumlah_pendaftar; ?> / <?= $kuota_text; ?><br>
    Status: 
    <span class="badge <?= $warna_badge; ?>">
        <?= $teks_status; ?>
    </span>
</p>

<?php
    $status_pendaftaran = null;
    if (isset($_SESSION['user'])) {
        $id_user = $_SESSION['user']['id'];
        $cek_query = mysqli_query($conn, "SELECT status FROM pendaftaran WHERE user_id = $id_user AND kegiatan_id = $id_kegiatan");
        if ($cek_daftar = mysqli_fetch_assoc($cek_query)) {
            $status_pendaftaran = $cek_daftar['status'];
        }
    }
?>

<?php if ($status_pendaftaran === 'pending'): ?>
    <button class="btn btn-warning" disabled>Menunggu Verifikasi</button>
<?php elseif (in_array($status_pendaftaran, ['verified', 'Diterima'])): ?>
    <button class="btn btn-success" disabled>Terdaftar</button>
<?php elseif (in_array($status_pendaftaran, ['rejected', 'Ditolak'])): ?>
    <button class="btn btn-danger" disabled>Ditolak</button>
<?php else: ?>
    <form action="daftar.php" method="POST">
        <input type="hidden" name="kegiatan_id" value="<?= $row['id']; ?>">
        <?php if(!isset($_SESSION['user'])): ?>
            <button type="button" class="btn btn-primary" <?= $status_tombol; ?> data-bs-toggle="modal" data-bs-target="#loginModal">Daftar</button>
        <?php else: ?>
            <button class="btn btn-primary" <?= $status_tombol; ?>>Daftar</button>
        <?php endif; ?>
    </form>
<?php endif; ?>
        </div>
    </div>
</div>

<?php endwhile; ?>
</div>

                <?php if(isset($_SESSION['user'])): ?>
                <section id="riwayat-section" style="margin-top: 50px;">
                    <h2 class="page-title" data-aos="fade-up">Riwayat Kegiatan Anda</h2>
                    <div class="row" data-aos="fade-up">
                    <?php
                    $id_user = $_SESSION['user']['id'];
                    $riwayat_query = mysqli_query($conn, "SELECT k.nama_kegiatan, k.tanggal, k.lokasi, p.status FROM pendaftaran p JOIN kegiatan k ON p.kegiatan_id = k.id WHERE p.user_id = $id_user ORDER BY p.id DESC");
                    
                    if(mysqli_num_rows($riwayat_query) > 0):
                        while($riwayat = mysqli_fetch_assoc($riwayat_query)):
                            $status_bg = 'bg-secondary';
                            $status_label = ucfirst($riwayat['status']);

                            if(in_array($riwayat['status'], ['verified', 'Diterima'])) {
                                $status_bg = 'bg-success';
                                $status_label = 'Terdaftar';
                            } elseif($riwayat['status'] == 'pending') {
                                $status_bg = 'bg-warning text-dark';
                                $status_label = 'Menunggu Verifikasi';
                            } elseif(in_array($riwayat['status'], ['rejected', 'Ditolak'])) {
                                $status_bg = 'bg-danger';
                                $status_label = 'Ditolak';
                            }
                    ?>
                        <div class="col-lg-4 mb-3">
                            <div class="activity-card p-3">
                                <h5><?= $riwayat['nama_kegiatan'] ?></h5>
                                <p class="mb-2"><small>📍 <?= $riwayat['lokasi'] ?><br>📅 <?= $riwayat['tanggal'] ?></small></p>
                                <span class="badge <?= $status_bg ?>"><?= $status_label ?></span>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="col-12"><div class="alert alert-info">Belum ada riwayat kegiatan.</div></div>
                    <?php endif; ?>
                    </div>
                </section>
                
                <section id="profile-section" style="margin-top: 50px;">
                    <h2 class="page-title" data-aos="fade-up">Profil & Poin Reward</h2>
                    <?php
                        $poin_user = 0;
                        $kegiatan_bulan_ini = 0;

                        // Ambil poin user (akan error diam-diam jika kolom poin belum ada, makanya dipasang @)
                        $poin_query = @mysqli_query($conn, "SELECT poin FROM users WHERE id = $id_user");
                        if($poin_query && mysqli_num_rows($poin_query) > 0) {
                            $poin_user = mysqli_fetch_assoc($poin_query)['poin'];
                        }

                        // Ambil total kegiatan bulan ini
                        $bulan_ini = date('Y-m');
                        $keg_bulan_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pendaftaran p JOIN kegiatan k ON p.kegiatan_id = k.id WHERE p.user_id = $id_user AND DATE_FORMAT(k.tanggal, '%Y-%m') = '$bulan_ini'");
                        if($keg_bulan_query) {
                            $kegiatan_bulan_ini = mysqli_fetch_assoc($keg_bulan_query)['total'];
                        }
                    ?>
                    <div class="row" data-aos="fade-up">
                        <div class="col-md-6 mb-4">
                            <div class="activity-card p-4 text-center h-100">
                                <i class="bi bi-calendar-check text-primary" style="font-size: 3rem;"></i>
                                <h4 class="mt-3">Kegiatan Bulan Ini</h4>
                                <h1 class="display-4 fw-bold text-primary"><?= $kegiatan_bulan_ini ?></h1>
                                <p class="text-muted">Total kegiatan yang kamu ikuti di bulan <?= date('F Y') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="activity-card p-4 text-center h-100">
                                <i class="bi bi-star-fill text-warning" style="font-size: 3rem;"></i>
                                <h4 class="mt-3">Poin Reward Kamu</h4>
                                <h1 class="display-4 fw-bold text-warning"><?= $poin_user ?></h1>
                                <p class="text-muted">Disetujui = +5 Poin, Ditolak = +2 Poin</p>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="mt-4 mb-3" data-aos="fade-up">Tukar Poin dengan Hadiah</h3>
                    <div class="row" data-aos="fade-up" data-aos-delay="100">
                        <!-- Hadiah 1 -->
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm h-100 border-0 rounded-4">
                                <div class="card-body text-center p-4">
                                    <i class="bi bi-cup-hot text-danger" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Coffee Latte</h5>
                                    <p class="text-warning fw-bold fs-5 mb-4">50 Poin</p>
                                    <form action="backend/tukar_hadiah.php" method="POST">
                                        <input type="hidden" name="hadiah" value="Coffee Latte">
                                        <input type="hidden" name="harga" value="50">
                                        <button type="submit" class="btn btn-outline-danger w-100 rounded-pill" <?= ($poin_user < 50) ? 'disabled' : '' ?>>Tukar Sekarang</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Hadiah 2 -->
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm h-100 border-0 rounded-4">
                                <div class="card-body text-center p-4">
                                    <i class="bi bi-bucket text-success" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Ember</h5>
                                    <p class="text-warning fw-bold fs-5 mb-4">100 Poin</p>
                                    <form action="backend/tukar_hadiah.php" method="POST">
                                        <input type="hidden" name="hadiah" value="Ember">
                                        <input type="hidden" name="harga" value="100">
                                        <button type="submit" class="btn btn-outline-success w-100 rounded-pill" <?= ($poin_user < 100) ? 'disabled' : '' ?>>Tukar Sekarang</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Hadiah 3 -->
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm h-100 border-0 rounded-4">
                                <div class="card-body text-center p-4">
                                    <i class="bi bi-person text-primary" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Baju / Kaos Relawan</h5>
                                    <p class="text-warning fw-bold fs-5 mb-4">250 Poin</p>
                                    <form action="backend/tukar_hadiah.php" method="POST">
                                        <input type="hidden" name="hadiah" value="Baju Relawan">
                                        <input type="hidden" name="harga" value="250">
                                        <button type="submit" class="btn btn-outline-primary w-100 rounded-pill" <?= ($poin_user < 250) ? 'disabled' : '' ?>>Tukar Sekarang</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                </div>
            </main>

    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <h5>KegiatanKu</h5>
                    <p>Platform terpercaya untuk kegiatan sosial yang berdampak positif bagi masyarakat.</p>
                </div>
                <div class="col-md-3">
                    <h6>Link Cepat</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">Tentang Kami</a></li>
                        <li><a href="#" class="footer-link">Kontak</a></li>
                        <li><a href="#" class="footer-link">Bantuan</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Ikuti Kami</h6>
                    <div class="social-icons">
                        <a href="https://facebook.com/" target="_blank" class="social-icon"><i class="bi bi-facebook"></i></a>
                        <a href="https://wa.me/628123456789" target="_blank" class="social-icon"><i class="bi bi-whatsapp"></i></a>
                        <a href="https://instagram.com" target="_blank" class="social-icon"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <p class="text-center mb-0">&copy; 2026 KegiatanKu. All rights reserved.</p>
        </div>
    </footer>
    </div>

    <!-- Login -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login ke KegiatanKu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm" action="backend/login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" id="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <p class="mt-2 text-center">
                        Belum punya akun? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Register -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Register ke KegiatanKu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm" action="backend/register.php" method="POST">
                        <div class="mb-3">
                            <label for="regName" class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" id="regName" required>
                        </div>
                        <div class="mb-3">
                            <label for="regEmail" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" id="regEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="regPassword" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" id="regPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="regConfirmPassword" class="form-label">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" class="form-control" id="regConfirmPassword" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Register</button>
                    </form>
                    <p class="mt-3 text-center">
                        Sudah punya akun? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="script.js"></script>
</body>
</html>