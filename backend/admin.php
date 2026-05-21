<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'admin@kegiatanku.com') {
    echo "<script>alert('Akses ditolak. Hanya admin.'); window.location='../index.php';</script>";
    exit;
}

$page = $_GET['page'] ?? 'dashboard';

// --- Stats ---
$total_kegiatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM kegiatan"))['t'];
$total_user     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM users"))['t'];
$total_pending  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM pendaftaran WHERE status='pending'"))['t'];
$total_verified = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS t FROM pendaftaran WHERE status='verified'"))['t'];

// --- Page-specific queries ---
if ($page === 'dashboard') {
    $q_pending = mysqli_query($conn, "SELECT p.id, u.nama AS nama_user, u.email, k.nama_kegiatan, k.tanggal, p.status, p.created_at FROM pendaftaran p JOIN users u ON p.user_id=u.id JOIN kegiatan k ON p.kegiatan_id=k.id WHERE p.status='pending' ORDER BY p.created_at DESC");
    $q_all     = mysqli_query($conn, "SELECT p.id, u.nama AS nama_user, u.email, k.nama_kegiatan, k.tanggal, p.status, p.created_at FROM pendaftaran p JOIN users u ON p.user_id=u.id JOIN kegiatan k ON p.kegiatan_id=k.id ORDER BY p.created_at DESC");
} elseif ($page === 'kegiatan') {
    $q_kegiatan = mysqli_query($conn, "SELECT k.*, (SELECT COUNT(*) FROM pendaftaran p WHERE p.kegiatan_id=k.id AND p.status='verified') AS peserta_count FROM kegiatan k ORDER BY k.id DESC");
} elseif ($page === 'pengguna') {
    $q_users = mysqli_query($conn, "SELECT u.*, (SELECT COUNT(*) FROM pendaftaran p WHERE p.user_id=u.id) AS total_daftar FROM users u ORDER BY u.id DESC");
}

$page_titles = ['dashboard' => 'Dashboard', 'kegiatan' => 'Kelola Kegiatan', 'pengguna' => 'Kelola Pengguna'];
$page_title  = $page_titles[$page] ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title; ?> — Admin KegiatanKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --bg:#f0f4fa; --primary:#007bff; --primary-d:#0056b3;
            --sidebar:#fff; --card:#fff; --text:#2d3748; --muted:#718096;
            --border:#e2e8f0; --shadow:0 4px 24px rgba(0,0,0,.07);
        }
        .dark-mode {
            --bg:#121826; --primary:#6a0dad; --primary-d:#5a0ca5;
            --sidebar:#1e2535; --card:#1e2535; --text:#e2e8f0; --muted:#a0aec0;
            --border:#2d3748; --shadow:0 4px 24px rgba(0,0,0,.3);
        }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);transition:background .3s,color .3s}

        /* SIDEBAR */
        .adm-sidebar{position:fixed;left:0;top:0;width:260px;height:100vh;background:var(--sidebar);border-right:1px solid var(--border);box-shadow:var(--shadow);z-index:1000;display:flex;flex-direction:column;transition:left .3s}
        .adm-brand{padding:28px 24px 22px;border-bottom:1px solid var(--border)}
        .adm-brand .logo-pill{display:inline-flex;align-items:center;gap:10px;background:linear-gradient(135deg,var(--primary),var(--primary-d));color:#fff;border-radius:12px;padding:8px 16px;font-weight:700;font-size:1.1rem}
        .adm-brand .logo-pill i{font-size:1.2rem}
        .adm-brand p{color:var(--muted);font-size:.75rem;margin-top:10px;margin-left:4px}
        .adm-nav{padding:18px 12px;flex:1;overflow-y:auto}
        .adm-nav-label{font-size:.65rem;font-weight:700;letter-spacing:1.2px;color:var(--muted);text-transform:uppercase;padding:8px 12px 6px}
        .adm-nav a{display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:10px;color:var(--text);text-decoration:none;font-weight:500;font-size:.9rem;transition:all .2s;margin-bottom:2px}
        .adm-nav a i{font-size:1.1rem;width:22px;text-align:center}
        .adm-nav a:hover{background:rgba(0,123,255,.09);color:var(--primary)}
        .adm-nav a.active{background:linear-gradient(135deg,var(--primary),var(--primary-d));color:#fff!important;box-shadow:0 4px 14px rgba(0,123,255,.35)}
        .adm-nav a .badge-count{margin-left:auto;background:#ff4d4f;color:#fff;font-size:.68rem;font-weight:700;border-radius:20px;padding:2px 8px;min-width:22px;text-align:center}
        .sidebar-bottom{padding:16px 12px;border-top:1px solid var(--border)}
        .sidebar-bottom a{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;color:var(--muted);text-decoration:none;font-size:.88rem;transition:all .2s}
        .sidebar-bottom a:hover{background:rgba(220,53,69,.1);color:#dc3545}

        /* TOPBAR */
        .adm-topbar{position:fixed;top:0;left:260px;right:0;height:64px;background:var(--card);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 28px;z-index:999;box-shadow:0 2px 8px rgba(0,0,0,.04);transition:left .3s}
        .adm-topbar .page-label{font-size:1.1rem;font-weight:600}
        .adm-topbar .topbar-right{display:flex;align-items:center;gap:16px}
        .admin-avatar{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-d));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem}
        .theme-toggle{position:relative;display:inline-block;width:48px;height:24px;cursor:pointer}
        .theme-toggle input{opacity:0;width:0;height:0}
        .tslider{position:absolute;inset:0;background:#cbd5e0;border-radius:24px;transition:.3s}
        .tslider::before{content:'';position:absolute;width:18px;height:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s}
        input:checked+.tslider{background:var(--primary)}
        input:checked+.tslider::before{transform:translateX(24px)}
        .sidebar-toggle-btn{display:none;background:none;border:1px solid var(--border);border-radius:8px;padding:6px 10px;color:var(--text);cursor:pointer}

        /* MAIN */
        .adm-main{margin-left:260px;margin-top:64px;padding:32px 28px;min-height:calc(100vh - 64px);transition:margin-left .3s}

        /* STATS */
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px}
        .stat-box{background:var(--card);border-radius:16px;padding:24px;box-shadow:var(--shadow);border:1px solid var(--border);display:flex;align-items:center;gap:18px;transition:transform .2s,box-shadow .2s}
        .stat-box:hover{transform:translateY(-3px);box-shadow:0 8px 32px rgba(0,0,0,.12)}
        .stat-icon{width:54px;height:54px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0}
        .stat-icon.blue{background:rgba(0,123,255,.12);color:#007bff}
        .stat-icon.orange{background:rgba(255,140,0,.12);color:#ff8c00}
        .stat-icon.green{background:rgba(34,197,94,.12);color:#22c55e}
        .stat-icon.purple{background:rgba(139,92,246,.12);color:#8b5cf6}
        .stat-box .stat-val{font-size:2rem;font-weight:700;line-height:1}
        .stat-box .stat-lbl{font-size:.8rem;color:var(--muted);margin-top:4px}

        /* TABS */
        .tab-row{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap}
        .tab-btn{padding:8px 20px;border-radius:10px;border:1px solid var(--border);background:var(--card);color:var(--muted);font-size:.88rem;font-weight:500;cursor:pointer;font-family:'Poppins',sans-serif;transition:all .2s}
        .tab-btn.active{background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 4px 14px rgba(0,123,255,.3)}
        .tab-pane{display:none}.tab-pane.active{display:block}

        /* TABLE CARD */
        .tcard{background:var(--card);border-radius:16px;box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;margin-bottom:28px}
        .tcard-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
        .tcard-header h2{font-size:1rem;font-weight:600;margin:0}
        .tcard-header .badge-pending-count{background:rgba(251,191,36,.18);color:#d97706;font-size:.78rem;font-weight:600;padding:4px 12px;border-radius:20px}
        table{width:100%;border-collapse:collapse}
        thead th{padding:13px 20px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);background:var(--bg);text-align:left;white-space:nowrap}
        tbody td{padding:14px 20px;border-top:1px solid var(--border);font-size:.88rem;vertical-align:middle}
        tbody tr{transition:background .15s}
        tbody tr:hover{background:rgba(0,123,255,.04)}
        .user-cell{display:flex;align-items:center;gap:10px}
        .user-ava{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.8rem;flex-shrink:0}
        .user-cell .uname{font-weight:600;font-size:.88rem}
        .user-cell .uemail{font-size:.75rem;color:var(--muted)}

        /* STATUS BADGES */
        .sbadge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:600}
        .sbadge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor}
        .sbadge-pending{background:rgba(251,191,36,.18);color:#d97706}
        .sbadge-verified{background:rgba(34,197,94,.15);color:#16a34a}
        .sbadge-rejected{background:rgba(239,68,68,.12);color:#dc2626}
        .sbadge-aktif{background:rgba(34,197,94,.15);color:#16a34a}
        .sbadge-selesai{background:rgba(107,114,128,.15);color:#6b7280}

        /* ACTION BUTTONS */
        .btn-approve{display:inline-flex;align-items:center;gap:5px;padding:5px 14px;border-radius:8px;font-size:.8rem;font-weight:600;background:rgba(34,197,94,.12);color:#16a34a;border:1px solid rgba(34,197,94,.3);cursor:pointer;font-family:'Poppins',sans-serif;transition:all .2s}
        .btn-approve:hover{background:#22c55e;color:#fff;box-shadow:0 3px 10px rgba(34,197,94,.4)}
        .btn-reject{display:inline-flex;align-items:center;gap:5px;padding:5px 14px;border-radius:8px;font-size:.8rem;font-weight:600;background:rgba(239,68,68,.1);color:#dc2626;border:1px solid rgba(239,68,68,.25);cursor:pointer;font-family:'Poppins',sans-serif;transition:all .2s;margin-left:6px}
        .btn-reject:hover{background:#ef4444;color:#fff;box-shadow:0 3px 10px rgba(239,68,68,.4)}
        .btn-edit{display:inline-flex;align-items:center;gap:5px;padding:5px 14px;border-radius:8px;font-size:.8rem;font-weight:600;background:rgba(59,130,246,.1);color:#3b82f6;border:1px solid rgba(59,130,246,.25);cursor:pointer;font-family:'Poppins',sans-serif;transition:all .2s}
        .btn-edit:hover{background:#3b82f6;color:#fff;box-shadow:0 3px 10px rgba(59,130,246,.4)}
        .btn-add{display:inline-flex;align-items:center;gap:8px;padding:9px 20px;border-radius:10px;font-size:.88rem;font-weight:600;background:linear-gradient(135deg,var(--primary),var(--primary-d));color:#fff;border:none;cursor:pointer;font-family:'Poppins',sans-serif;transition:all .2s;box-shadow:0 4px 14px rgba(0,123,255,.3)}
        .btn-add:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,123,255,.4)}
        .processed-text{font-size:.78rem;color:var(--muted);font-style:italic}

        /* EMPTY STATE */
        .empty-state{text-align:center;padding:60px 20px;color:var(--muted)}
        .empty-state i{font-size:3.5rem;display:block;margin-bottom:14px;color:var(--border)}

        /* MODAL */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
        .modal-overlay.show{display:flex}
        .modal-box{background:var(--card);border-radius:20px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.25);animation:modalIn .25s ease}
        @keyframes modalIn{from{opacity:0;transform:scale(.95) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)}}
        .modal-head{padding:24px 28px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border)}
        .modal-head h3{font-size:1.1rem;font-weight:700;margin:0}
        .modal-close{background:none;border:none;font-size:1.4rem;color:var(--muted);cursor:pointer;padding:4px;border-radius:8px;transition:all .2s}
        .modal-close:hover{background:rgba(239,68,68,.1);color:#ef4444}
        .modal-body{padding:20px 28px 28px}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;font-size:.82rem;font-weight:600;color:var(--muted);margin-bottom:6px;letter-spacing:.3px}
        .form-group input,.form-group textarea,.form-group select{width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:.9rem;font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);transition:border .2s,box-shadow .2s;outline:none}
        .form-group input:focus,.form-group textarea:focus,.form-group select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,123,255,.12)}
        .form-group textarea{resize:vertical;min-height:80px}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .modal-btn{width:100%;padding:12px;border:none;border-radius:12px;font-size:.95rem;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;transition:all .2s}
        .modal-btn.primary{background:linear-gradient(135deg,var(--primary),var(--primary-d));color:#fff;box-shadow:0 4px 14px rgba(0,123,255,.3)}
        .modal-btn.primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,123,255,.4)}

        /* TOAST */
        .toast-container{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px}
        .toast-item{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:14px 18px;box-shadow:0 8px 32px rgba(0,0,0,.15);display:flex;align-items:center;gap:12px;animation:slideUp .3s ease;font-size:.88rem;min-width:280px}
        .toast-item i{font-size:1.2rem}
        .toast-item.success i{color:#22c55e}
        .toast-item.error i{color:#ef4444}
        @keyframes slideUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}

        /* DETAIL INFO */
        .detail-row{display:flex;gap:8px;align-items:flex-start;padding:8px 0;border-bottom:1px solid var(--border)}
        .detail-row:last-child{border-bottom:none}
        .detail-label{font-size:.8rem;font-weight:600;color:var(--muted);min-width:100px;flex-shrink:0}
        .detail-value{font-size:.88rem}

        /* RESPONSIVE */
        @media(max-width:992px){.stats-grid{grid-template-columns:repeat(2,1fr)}.form-row{grid-template-columns:1fr}}
        @media(max-width:768px){.adm-sidebar{left:-260px}.adm-sidebar.show{left:0}.adm-topbar{left:0}.adm-main{margin-left:0}.sidebar-toggle-btn{display:block}.stats-grid{grid-template-columns:1fr 1fr}}
        @media(max-width:480px){.stats-grid{grid-template-columns:1fr}.adm-main{padding:20px 16px}.modal-box{margin:16px;max-width:calc(100% - 32px)}}
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="adm-sidebar" id="adminSidebar">
    <div class="adm-brand">
        <div class="logo-pill"><i class="bi bi-stars"></i> KegiatanKu</div>
        <p>Panel Administrator</p>
    </div>
    <nav class="adm-nav">
        <div class="adm-nav-label">Menu Utama</div>
        <a href="admin.php?page=dashboard" class="<?= $page==='dashboard'?'active':''; ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
            <?= $total_pending > 0 ? "<span class='badge-count'>$total_pending</span>" : ''; ?>
        </a>
        <a href="../index.php"><i class="bi bi-globe2"></i> Lihat Website</a>
        <div class="adm-nav-label" style="margin-top:14px">Manajemen</div>
        <a href="admin.php?page=kegiatan" class="<?= $page==='kegiatan'?'active':''; ?>">
            <i class="bi bi-calendar3"></i> Kegiatan
            <span class="badge-count" style="background:var(--primary)"><?= $total_kegiatan; ?></span>
        </a>
        <a href="admin.php?page=pengguna" class="<?= $page==='pengguna'?'active':''; ?>">
            <i class="bi bi-people"></i> Pengguna
            <span class="badge-count" style="background:#8b5cf6"><?= $total_user; ?></span>
        </a>
    </nav>
    <div class="sidebar-bottom">
        <a href="logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</aside>

<!-- TOPBAR -->
<header class="adm-topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <span class="page-label"><?= $page_title; ?></span>
    </div>
    <div class="topbar-right">
        <label class="theme-toggle">
            <input type="checkbox" id="darkToggle">
            <span class="tslider"></span>
        </label>
        <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user']['nama'],0,1)); ?></div>
        <span style="font-size:.88rem;font-weight:500"><?= htmlspecialchars($_SESSION['user']['nama']); ?></span>
    </div>
</header>

<!-- MAIN -->
<main class="adm-main">

<!-- ============ PAGE: DASHBOARD ============ -->
<?php if ($page === 'dashboard'): ?>

    <div class="stats-grid" data-aos="fade-up">
        <div class="stat-box"><div class="stat-icon blue"><i class="bi bi-calendar-event"></i></div><div class="stat-info"><div class="stat-val"><?= $total_kegiatan; ?></div><div class="stat-lbl">Total Kegiatan</div></div></div>
        <div class="stat-box"><div class="stat-icon purple"><i class="bi bi-people"></i></div><div class="stat-info"><div class="stat-val"><?= $total_user; ?></div><div class="stat-lbl">Total Pengguna</div></div></div>
        <div class="stat-box"><div class="stat-icon orange"><i class="bi bi-hourglass-split"></i></div><div class="stat-info"><div class="stat-val"><?= $total_pending; ?></div><div class="stat-lbl">Menunggu Verifikasi</div></div></div>
        <div class="stat-box"><div class="stat-icon green"><i class="bi bi-patch-check"></i></div><div class="stat-info"><div class="stat-val"><?= $total_verified; ?></div><div class="stat-lbl">Terverifikasi</div></div></div>
    </div>

    <div class="tcard" data-aos="fade-up" data-aos-delay="100">
        <div class="tcard-header">
            <h2><i class="bi bi-clipboard-check me-2"></i>Daftar Pendaftaran</h2>
            <?php if($total_pending>0): ?><span class="badge-pending-count"><?= $total_pending; ?> menunggu</span><?php endif; ?>
        </div>
        <div style="padding:16px 20px 0">
            <div class="tab-row">
                <button class="tab-btn active" onclick="switchTab('pending',this)">Perlu Diverifikasi <?= $total_pending>0?"($total_pending)":''; ?></button>
                <button class="tab-btn" onclick="switchTab('all',this)">Semua</button>
            </div>
        </div>

        <!-- TAB PENDING -->
        <div class="tab-pane active" id="tab-pending">
        <?php $rp=[]; while($r=mysqli_fetch_assoc($q_pending)) $rp[]=$r; ?>
        <?php if(!$rp): ?>
            <div class="empty-state"><i class="bi bi-check-circle"></i><p>Semua sudah diverifikasi.</p></div>
        <?php else: ?>
        <div style="overflow-x:auto"><table><thead><tr><th>#</th><th>Pengguna</th><th>Kegiatan</th><th>Tgl Kegiatan</th><th>Waktu Daftar</th><th>Aksi</th></tr></thead><tbody>
        <?php foreach($rp as $i=>$r): ?>
        <tr id="row-<?=$r['id']?>"><td><?=$i+1?></td>
        <td><div class="user-cell"><div class="user-ava"><?=strtoupper(substr($r['nama_user'],0,1))?></div><div><div class="uname"><?=htmlspecialchars($r['nama_user'])?></div><div class="uemail"><?=htmlspecialchars($r['email'])?></div></div></div></td>
        <td><strong><?=htmlspecialchars($r['nama_kegiatan'])?></strong></td>
        <td><?=date('d M Y',strtotime($r['tanggal']))?></td>
        <td><?=date('d M Y, H:i',strtotime($r['created_at']))?></td>
        <td><button class="btn-approve" onclick="verifikasi(<?=$r['id']?>,'verified')"><i class="bi bi-check-lg"></i> Setujui</button><button class="btn-reject" onclick="verifikasi(<?=$r['id']?>,'rejected')"><i class="bi bi-x-lg"></i> Tolak</button></td>
        </tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
        </div>

        <!-- TAB ALL -->
        <div class="tab-pane" id="tab-all">
        <?php $ra=[]; while($r=mysqli_fetch_assoc($q_all)) $ra[]=$r; ?>
        <?php if(!$ra): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada pendaftaran.</p></div>
        <?php else: ?>
        <div style="overflow-x:auto"><table><thead><tr><th>#</th><th>Pengguna</th><th>Kegiatan</th><th>Tgl Kegiatan</th><th>Waktu Daftar</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        <?php foreach($ra as $i=>$r):
            $cls=match($r['status']){'pending'=>'sbadge-pending','verified'=>'sbadge-verified','rejected'=>'sbadge-rejected',default=>''};
            $lbl=match($r['status']){'pending'=>'Menunggu','verified'=>'Terverifikasi','rejected'=>'Ditolak',default=>$r['status']};
        ?>
        <tr id="row-all-<?=$r['id']?>"><td><?=$i+1?></td>
        <td><div class="user-cell"><div class="user-ava"><?=strtoupper(substr($r['nama_user'],0,1))?></div><div><div class="uname"><?=htmlspecialchars($r['nama_user'])?></div><div class="uemail"><?=htmlspecialchars($r['email'])?></div></div></div></td>
        <td><strong><?=htmlspecialchars($r['nama_kegiatan'])?></strong></td>
        <td><?=date('d M Y',strtotime($r['tanggal']))?></td>
        <td><?=date('d M Y, H:i',strtotime($r['created_at']))?></td>
        <td><span class="sbadge <?=$cls?>" id="badge-<?=$r['id']?>"><?=$lbl?></span></td>
        <td>
            <?php if($r['status']==='pending'): ?>
            <button class="btn-approve" onclick="verifikasi(<?=$r['id']?>,'verified')"><i class="bi bi-check-lg"></i> Setujui</button><button class="btn-reject" onclick="verifikasi(<?=$r['id']?>,'rejected')"><i class="bi bi-x-lg"></i> Tolak</button>
            <?php else: ?><span class="processed-text">Sudah diproses</span><?php endif; ?>
        </td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
        </div>
    </div>


<!-- ============ PAGE: KEGIATAN ============ -->
<?php elseif ($page === 'kegiatan'): ?>

    <div class="tcard" data-aos="fade-up">
        <div class="tcard-header">
            <h2><i class="bi bi-calendar3 me-2"></i>Daftar Kegiatan (<?= $total_kegiatan; ?>)</h2>
            <button class="btn-add" onclick="openModal('tambahKegiatan')"><i class="bi bi-plus-lg"></i> Tambah Kegiatan</button>
        </div>
        <?php
            $kegiatan_rows = [];
            while($r = mysqli_fetch_assoc($q_kegiatan)) $kegiatan_rows[] = $r;
        ?>
        <?php if(!$kegiatan_rows): ?>
            <div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada kegiatan.</p></div>
        <?php else: ?>
        <div style="overflow-x:auto"><table><thead><tr>
            <th>#</th><th>Nama Kegiatan</th><th>Lokasi</th><th>Tanggal</th><th>Kuota</th><th>Peserta</th><th>Status</th><th>Aksi</th>
        </tr></thead><tbody>
        <?php foreach($kegiatan_rows as $i=>$r):
            $s_cls = $r['status']==='aktif'?'sbadge-aktif':'sbadge-selesai';
            $s_lbl = $r['status']==='aktif'?'Aktif':'Selesai';
        ?>
        <tr id="keg-<?=$r['id']?>">
            <td><?=$i+1?></td>
            <td><strong><?=htmlspecialchars($r['nama_kegiatan'])?></strong>
                <?php if($r['deskripsi']): ?><br><span style="font-size:.78rem;color:var(--muted)"><?=mb_strimwidth(htmlspecialchars($r['deskripsi']),0,60,'...')?></span><?php endif; ?>
            </td>
            <td><i class="bi bi-geo-alt" style="color:var(--primary);margin-right:4px"></i><?=htmlspecialchars($r['lokasi'])?></td>
            <td><?=date('d M Y',strtotime($r['tanggal']))?></td>
            <td><?=$r['kuota']?:'-'?></td>
            <td><span style="font-weight:600;color:var(--primary)"><?=$r['peserta_count']?></span></td>
            <td><span class="sbadge <?=$s_cls?>"><?=$s_lbl?></span></td>
            <td>
                <button class="btn-edit" onclick="editKegiatan(<?=$r['id']?>)"><i class="bi bi-pencil"></i> Edit</button>
                <button class="btn-reject" onclick="hapusKegiatan(<?=$r['id']?>,'<?=addslashes($r['nama_kegiatan'])?>')"><i class="bi bi-trash3"></i> Hapus</button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </div>


<!-- ============ PAGE: PENGGUNA ============ -->
<?php elseif ($page === 'pengguna'): ?>

    <div class="tcard" data-aos="fade-up">
        <div class="tcard-header">
            <h2><i class="bi bi-people me-2"></i>Daftar Pengguna (<?= $total_user; ?>)</h2>
        </div>
        <?php
            $user_rows = [];
            while($r = mysqli_fetch_assoc($q_users)) $user_rows[] = $r;
        ?>
        <?php if(!$user_rows): ?>
            <div class="empty-state"><i class="bi bi-person-x"></i><p>Belum ada pengguna.</p></div>
        <?php else: ?>
        <div style="overflow-x:auto"><table><thead><tr>
            <th>#</th><th>Pengguna</th><th>Email</th><th>Tgl Daftar</th><th>Total Kegiatan</th><th>Role</th><th>Aksi</th>
        </tr></thead><tbody>
        <?php foreach($user_rows as $i=>$r):
            $is_admin = $r['email'] === 'admin@kegiatanku.com';
        ?>
        <tr id="usr-<?=$r['id']?>">
            <td><?=$i+1?></td>
            <td><div class="user-cell">
                <div class="user-ava" <?=$is_admin?'style="background:linear-gradient(135deg,#f59e0b,#d97706)"':''?>><?=strtoupper(substr($r['nama'],0,1))?></div>
                <div><div class="uname"><?=htmlspecialchars($r['nama'])?></div></div>
            </div></td>
            <td><?=htmlspecialchars($r['email'])?></td>
            <td><?=date('d M Y',strtotime($r['created_at']))?></td>
            <td><span style="font-weight:600;color:var(--primary)"><?=$r['total_daftar']?></span> kegiatan</td>
            <td>
                <?php if($is_admin): ?>
                    <span class="sbadge" style="background:rgba(245,158,11,.15);color:#d97706">Admin</span>
                <?php else: ?>
                    <span class="sbadge" style="background:rgba(59,130,246,.12);color:#3b82f6">User</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if(!$is_admin): ?>
                    <button class="btn-reject" onclick="hapusUser(<?=$r['id']?>,'<?=addslashes($r['nama'])?>')"><i class="bi bi-trash3"></i> Hapus</button>
                <?php else: ?>
                    <span class="processed-text">Akun admin</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </div>

<?php endif; ?>
</main>

<!-- ========== MODAL: TAMBAH/EDIT KEGIATAN ========== -->
<div class="modal-overlay" id="tambahKegiatan">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modalKegTitle"><i class="bi bi-plus-circle me-2"></i>Tambah Kegiatan Baru</h3>
            <button class="modal-close" onclick="closeModal('tambahKegiatan')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formKegiatan" onsubmit="submitKegiatan(event)">
                <input type="hidden" id="keg_id" value="">
                <div class="form-group">
                    <label>Nama Kegiatan *</label>
                    <input type="text" id="keg_nama" placeholder="cth: Baksos Bersama Warga" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Lokasi *</label>
                        <input type="text" id="keg_lokasi" placeholder="cth: Balai Desa Sukamaju" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal *</label>
                        <input type="date" id="keg_tanggal" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Waktu Mulai</label>
                        <input type="time" id="keg_waktu_mulai">
                    </div>
                    <div class="form-group">
                        <label>Waktu Selesai</label>
                        <input type="time" id="keg_waktu_selesai">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kuota Peserta</label>
                        <input type="number" id="keg_kuota" placeholder="0 = tidak terbatas" min="0" value="0">
                    </div>
                    <div class="form-group" id="statusGroup" style="display:none">
                        <label>Status</label>
                        <select id="keg_status">
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="keg_deskripsi" placeholder="Deskripsi singkat kegiatan..."></textarea>
                </div>
                <button type="submit" class="modal-btn primary" id="btnSubmitKeg"><i class="bi bi-check-lg"></i> Simpan Kegiatan</button>
            </form>
        </div>
    </div>
</div>

<!-- CONFIRM DIALOG -->
<div class="modal-overlay" id="confirmDialog">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-head">
            <h3 id="confirmTitle"><i class="bi bi-exclamation-triangle me-2" style="color:#ef4444"></i>Konfirmasi</h3>
            <button class="modal-close" onclick="closeModal('confirmDialog')">&times;</button>
        </div>
        <div class="modal-body">
            <p id="confirmMsg" style="margin-bottom:20px;font-size:.92rem"></p>
            <div style="display:flex;gap:10px">
                <button class="modal-btn" style="background:var(--bg);color:var(--text);border:1px solid var(--border)" onclick="closeModal('confirmDialog')">Batal</button>
                <button class="modal-btn" style="background:#ef4444;color:#fff" id="confirmYes">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- TOAST -->
<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({duration:600,once:true});

// Dark mode
const dt=document.getElementById('darkToggle');
if(localStorage.getItem('adminTheme')==='dark'){document.body.classList.add('dark-mode');dt.checked=true}
dt.addEventListener('change',()=>{document.body.classList.toggle('dark-mode');localStorage.setItem('adminTheme',dt.checked?'dark':'light')});

// Sidebar mobile
document.getElementById('sidebarToggle')?.addEventListener('click',()=>document.getElementById('adminSidebar').classList.toggle('show'));

// Toast
function showToast(msg,type='success'){
    const c=document.getElementById('toastContainer'),t=document.createElement('div');
    t.className='toast-item '+type;
    t.innerHTML=`<i class="bi bi-${type==='success'?'check-circle-fill':'x-circle-fill'}"></i> ${msg}`;
    c.appendChild(t);setTimeout(()=>{t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(()=>t.remove(),300)},3000);
}

// Modal
function openModal(id){document.getElementById(id).classList.add('show')}
function closeModal(id){document.getElementById(id).classList.remove('show')}

// Tabs
function switchTab(name,btn){
    document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById('tab-'+name).classList.add('active');btn.classList.add('active');
}

// ========== VERIFIKASI ==========
function verifikasi(id,aksi){
    const fd=new FormData();fd.append('id',id);fd.append('aksi',aksi);
    fetch('verifikasi.php',{method:'POST',body:fd}).then(r=>{if(!r.ok)throw 0;return r.text()}).then(()=>{
        const pr=document.getElementById('row-'+id);
        if(pr){pr.style.transition='opacity .3s,transform .3s';pr.style.opacity='0';pr.style.transform='translateX(20px)';setTimeout(()=>{pr.remove();checkEmpty()},300)}
        const b=document.getElementById('badge-'+id);
        if(b){b.className='sbadge '+(aksi==='verified'?'sbadge-verified':'sbadge-rejected');b.textContent=aksi==='verified'?'Terverifikasi':'Ditolak';
            const ar=document.getElementById('row-all-'+id);if(ar){ar.querySelector('td:last-child').innerHTML='<span class="processed-text">Sudah diproses</span>'}
        }
        showToast('Berhasil '+(aksi==='verified'?'menyetujui':'menolak')+' pendaftaran.');
    }).catch(()=>showToast('Gagal. Coba lagi.','error'));
}
function checkEmpty(){const tb=document.querySelector('#tab-pending table tbody');if(tb&&!tb.querySelectorAll('tr').length)document.querySelector('#tab-pending').innerHTML='<div class="empty-state"><i class="bi bi-check-circle"></i><p>Semua sudah diverifikasi.</p></div>'}

// ========== KEGIATAN CRUD ==========
function resetFormKegiatan(){
    document.getElementById('keg_id').value='';
    document.getElementById('keg_nama').value='';
    document.getElementById('keg_lokasi').value='';
    document.getElementById('keg_tanggal').value='';
    document.getElementById('keg_waktu_mulai').value='';
    document.getElementById('keg_waktu_selesai').value='';
    document.getElementById('keg_kuota').value='0';
    document.getElementById('keg_deskripsi').value='';
    document.getElementById('keg_status').value='aktif';
    document.getElementById('statusGroup').style.display='none';
    document.getElementById('modalKegTitle').innerHTML='<i class="bi bi-plus-circle me-2"></i>Tambah Kegiatan Baru';
    document.getElementById('btnSubmitKeg').innerHTML='<i class="bi bi-check-lg"></i> Simpan Kegiatan';
}

function editKegiatan(id){
    fetch('admin_action.php?action=get_kegiatan&id='+id).then(r=>r.json()).then(d=>{
        if(d.error){showToast(d.error,'error');return}
        document.getElementById('keg_id').value=d.id;
        document.getElementById('keg_nama').value=d.nama_kegiatan;
        document.getElementById('keg_lokasi').value=d.lokasi;
        document.getElementById('keg_tanggal').value=d.tanggal;
        document.getElementById('keg_waktu_mulai').value=d.waktu_mulai||'';
        document.getElementById('keg_waktu_selesai').value=d.waktu_selesai||'';
        document.getElementById('keg_kuota').value=d.kuota||0;
        document.getElementById('keg_deskripsi').value=d.deskripsi||'';
        document.getElementById('keg_status').value=d.status;
        document.getElementById('statusGroup').style.display='block';
        document.getElementById('modalKegTitle').innerHTML='<i class="bi bi-pencil me-2"></i>Edit Kegiatan';
        document.getElementById('btnSubmitKeg').innerHTML='<i class="bi bi-check-lg"></i> Simpan Perubahan';
        openModal('tambahKegiatan');
    }).catch(()=>showToast('Gagal memuat data.','error'));
}

function submitKegiatan(e){
    e.preventDefault();
    const id=document.getElementById('keg_id').value;
    const fd=new FormData();
    fd.append('action',id?'edit_kegiatan':'tambah_kegiatan');
    if(id) fd.append('id',id);
    fd.append('nama_kegiatan',document.getElementById('keg_nama').value);
    fd.append('lokasi',document.getElementById('keg_lokasi').value);
    fd.append('tanggal',document.getElementById('keg_tanggal').value);
    fd.append('waktu_mulai',document.getElementById('keg_waktu_mulai').value);
    fd.append('waktu_selesai',document.getElementById('keg_waktu_selesai').value);
    fd.append('kuota',document.getElementById('keg_kuota').value);
    fd.append('deskripsi',document.getElementById('keg_deskripsi').value);
    if(id) fd.append('status',document.getElementById('keg_status').value);

    fetch('admin_action.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        if(d.error){showToast(d.error,'error');return}
        showToast(id?'Kegiatan berhasil diubah.':'Kegiatan berhasil ditambahkan.');
        closeModal('tambahKegiatan');resetFormKegiatan();
        setTimeout(()=>location.reload(),600);
    }).catch(()=>showToast('Terjadi kesalahan.','error'));
}

// Close modal on clicking overlay
document.getElementById('tambahKegiatan')?.addEventListener('click',function(e){
    if(e.target===this){closeModal('tambahKegiatan');resetFormKegiatan()}
});

// Reset form when modal opens for adding
document.querySelector('.btn-add')?.addEventListener('click',function(){resetFormKegiatan()});

// ========== HAPUS KEGIATAN ==========
let pendingDelete=null;
function hapusKegiatan(id,nama){
    document.getElementById('confirmMsg').textContent='Yakin mau hapus kegiatan "'+nama+'"? Semua pendaftaran terkait juga akan dihapus.';
    pendingDelete={type:'kegiatan',id:id};
    openModal('confirmDialog');
}
function hapusUser(id,nama){
    document.getElementById('confirmMsg').textContent='Yakin mau hapus pengguna "'+nama+'"? Semua data pendaftarannya juga akan dihapus.';
    pendingDelete={type:'user',id:id};
    openModal('confirmDialog');
}
document.getElementById('confirmYes')?.addEventListener('click',function(){
    if(!pendingDelete)return;
    const fd=new FormData();
    fd.append('action',pendingDelete.type==='kegiatan'?'hapus_kegiatan':'hapus_user');
    fd.append('id',pendingDelete.id);
    fetch('admin_action.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        if(d.error){showToast(d.error,'error');closeModal('confirmDialog');return}
        const rowId=pendingDelete.type==='kegiatan'?'keg-'+pendingDelete.id:'usr-'+pendingDelete.id;
        const row=document.getElementById(rowId);
        if(row){row.style.transition='opacity .3s,transform .3s';row.style.opacity='0';row.style.transform='translateX(20px)';setTimeout(()=>row.remove(),300)}
        showToast('Berhasil dihapus.');closeModal('confirmDialog');pendingDelete=null;
    }).catch(()=>{showToast('Gagal menghapus.','error');closeModal('confirmDialog')});
});
</script>
</body>
</html>
