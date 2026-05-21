# 🌐 KegiatanKu - Web Platform Kegiatan Sosial & Relawan

**KegiatanKu** adalah platform website berbasis PHP Native yang dirancang untuk memanajemen pendaftaran kegiatan sosial dan relawan. Website ini tidak hanya berfungsi sebagai portal informasi, tetapi juga sebagai **Backend & API Server** yang terintegrasi langsung dengan aplikasi *mobile* (Flutter).

---

## ✨ Fitur Utama (Web & API)

### 👤 Dashboard User (Relawan)
- **Registrasi & Login Aman:** Dilengkapi dengan enkripsi *password hashing*.
- **Eksplorasi Kegiatan:** Tampilan UI modern menggunakan Bootstrap & animasi AOS untuk mencari dan mendaftar kegiatan sosial.
- **Sistem Poin (Gamifikasi) 🎮:** 
  - Mendapat **+5 Poin** jika pendaftaran disetujui (Verified).
  - Mendapat **+2 Poin** jika pendaftaran ditolak (Rejected).
- **Penukaran Hadiah (Redeem):** Poin yang terkumpul dapat ditukar dengan hadiah fisik (*Coffee Latte*, Ember, Baju Relawan) secara langsung dari halaman profil.
- **Riwayat & Statistik:** Pemantauan status pendaftaran dan total kegiatan yang diikuti dalam bulan berjalan.

### 🛡️ Dashboard Admin (Pengurus)
- **Manajemen CRUD Kegiatan:** Tambah, edit, hapus, dan atur status kegiatan (Buka/Tutup).
- **Sistem Verifikasi:** Persetujuan atau penolakan calon relawan dengan satu klik (terintegrasi otomatis dengan sistem penambahan poin user).
- **Manajemen User:** Mengubah *role* pengguna atau menghapus akun yang tidak valid.

---

## 🛠️ Teknologi & Tools
- **Bahasa Pemrograman:** PHP (Native), JavaScript
- **Database:** MySQL
- **Frontend / UI:** HTML5, CSS3, Bootstrap 5, AOS Animation
- **Deployment Teruji:** InfinityFree Server (dengan dukungan SSL / HTTPS)

---

## 🚀 Panduan Instalasi Lokal (XAMPP)

1. **Clone Repository:**
   ```bash
   git clone https://github.com/username-kamu/kegiatanku-web.git
   ```
2. **Pindahkan Folder:**
   Pastikan folder project ini berada di dalam direktori `C:\xampp\htdocs\`.
3. **Konfigurasi Database:**
   - Buka aplikasi XAMPP, jalankan **Apache** dan **MySQL**.
   - Buka `http://localhost/phpmyadmin` di browser.
   - Buat database baru bernama `kegiatanku`.
   - Lakukan **Import** pada file `database.sql`.
4. **Konfigurasi Koneksi:**
   - Cek file `backend/koneksi.php` dan `api/config.php`.
   - Pastikan variabel `$host = "localhost"`, `$user = "root"`, `$pass = ""`, dan `$db = "kegiatanku"`.
5. **Jalankan Website:**
   - Buka browser dan akses URL: `http://localhost/KegiatanKu/`

---
*Dikembangkan dengan dedikasi tinggi untuk tugas akhir oleh [Rafi].*
