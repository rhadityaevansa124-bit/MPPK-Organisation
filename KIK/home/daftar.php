<?php 
session_start(); 
if(!isset($_SESSION['username'])){
    header("location:../admin/login.php");
    exit();
}

include '../koneksi/koneksi.php';

// Cek status pendaftaran
$q_st = mysqli_query($koneksi, "SELECT nilai FROM tb_pengaturan WHERE kunci='status_pendaftaran' LIMIT 1");
$row_st = $q_st ? mysqli_fetch_assoc($q_st) : null;
$status_pendaftaran = $row_st['nilai'] ?? 'buka';
$pendaftaran_tutup  = ($status_pendaftaran === 'tutup');

// Cek apakah user ini sudah pernah daftar
$username_session = $_SESSION['username'];
$cek_sudah = $koneksi->prepare(
    "SELECT td.id, td.kode_resi, td.status
     FROM tb_daftar td
     JOIN tb_login tl ON tl.email = td.email
     WHERE tl.username = ?
     LIMIT 1"
);
$cek_sudah->bind_param("s", $username_session);
$cek_sudah->execute();
$res_sudah       = $cek_sudah->get_result();
$data_sudah      = $res_sudah->fetch_assoc();
$sudah_daftar    = ($data_sudah !== null);
$cek_sudah->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran — MPPK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy:    #1a3c5e;
            --navy2:   #234f7a;
            --gold:    #c9a84c;
            --gold-l:  #f0d98a;
            --bg:      #f5f0e8;
            --card:    #ffffff;
            --text:    #1a1a2e;
            --muted:   #6b7280;
            --border:  #e2d9c8;
            --nav-bg:  linear-gradient(90deg, #3005f1, #000000);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            background-image:
                radial-gradient(ellipse at 10% 20%, rgba(201,168,76,0.07) 0%, transparent 60%),
                radial-gradient(ellipse at 90% 80%, rgba(26,60,94,0.06) 0%, transparent 60%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ══════════════════════
           NAVBAR
        ══════════════════════ */
        .navbar {
            position: fixed;
            top: 0; left: 0; width: 100%;
            background: var(--nav-bg);
            z-index: 999;
            transition: top 0.3s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            height: 64px;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .navbar-brand img {
            height: 44px;
            border-radius: 50%;
            border: 2px solid rgba(201,168,76,0.5);
        }

        .navbar-brand-text {
            line-height: 1.2;
        }

        .navbar-brand-text strong {
            display: block;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
        }

        .navbar-brand-text span {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.65);
        }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
        }

        .navbar-links li a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 8px;
            transition: background 0.2s, color 0.2s;
        }

        .navbar-links li a:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        .navbar-links li a.active {
            background: rgba(201,168,76,0.2);
            color: var(--gold-l);
            border: 1px solid rgba(201,168,76,0.35);
        }

        /* ══════════════════════
           MAIN CONTENT
        ══════════════════════ */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 96px 16px 48px;
        }

        /* ══════════════════════
           TOAST NOTIFICATION
        ══════════════════════ */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fff;
            border-radius: 14px;
            padding: 16px 18px;
            min-width: 300px;
            max-width: 360px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.14), 0 2px 8px rgba(0,0,0,0.06);
            border-left: 4px solid var(--gold);
            pointer-events: all;
            transform: translateX(120%);
            opacity: 0;
            transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.hide {
            transform: translateX(120%);
            opacity: 0;
        }

        .toast.success { border-left-color: #22c55e; }
        .toast.error   { border-left-color: #ef4444; }
        .toast.warning { border-left-color: #f59e0b; }
        .toast.info    { border-left-color: #3b82f6; }

        .toast-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }

        .toast.success .toast-icon { background: #f0fdf4; }
        .toast.error   .toast-icon { background: #fef2f2; }
        .toast.warning .toast-icon { background: #fffbeb; }
        .toast.info    .toast-icon { background: #eff6ff; }

        .toast-body { flex: 1; min-width: 0; }

        .toast-title {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 3px;
        }

        .toast-msg {
            font-size: 0.8rem;
            color: var(--muted);
            line-height: 1.4;
        }

        .toast-close {
            background: none; border: none;
            color: #9ca3af; cursor: pointer;
            font-size: 18px; line-height: 1;
            padding: 0; flex-shrink: 0;
            transition: color 0.15s;
        }
        .toast-close:hover { color: #374151; }

        /* Progress bar */
        .toast-progress {
            position: absolute;
            bottom: 0; left: 0;
            height: 3px;
            border-radius: 0 0 14px 14px;
            animation: toastProgress 3.5s linear forwards;
        }

        .toast { position: relative; overflow: hidden; }

        .toast.success .toast-progress { background: #22c55e; }
        .toast.error   .toast-progress { background: #ef4444; }
        .toast.warning .toast-progress { background: #f59e0b; }
        .toast.info    .toast-progress { background: #3b82f6; }

        @keyframes toastProgress {
            from { width: 100%; }
            to   { width: 0%; }
        }

        /* ══════════════════════
           MODAL KONFIRMASI
        ══════════════════════ */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 3000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.active { display: flex; animation: fadeIn 0.25s ease; }

        .modal-box {
            background: #fff;
            border-radius: 20px;
            padding: 32px 28px;
            max-width: 380px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease;
        }

        .modal-icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fef9ec, #fef3c7);
            border: 2px solid rgba(201,168,76,0.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            margin: 0 auto 18px;
        }

        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: var(--navy);
            margin-bottom: 10px;
        }

        .modal-desc {
            font-size: 0.88rem;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
        }

        .btn-modal-cancel {
            flex: 1;
            padding: 11px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: transparent;
            color: var(--navy);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-modal-cancel:hover { background: #f5f0e8; border-color: var(--navy); }

        .btn-modal-confirm {
            flex: 1;
            padding: 11px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--navy), var(--navy2));
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(26,60,94,0.3);
            transition: opacity 0.2s, transform 0.15s;
        }
        .btn-modal-confirm:hover { opacity: 0.9; transform: translateY(-1px); }

        /* ══════════════════════
           FORM CARD
        ══════════════════════ */
        .form-card {
            background: var(--card);
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(26,60,94,0.1);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
            animation: fadeUp 0.6s ease both;
        }

        .form-card-header {
            background: linear-gradient(135deg, var(--navy), var(--navy2));
            padding: 32px 36px 28px;
            text-align: center;
            position: relative;
        }

        .form-card-header .logo-wrap {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: rgba(201,168,76,0.2);
            border: 2px solid rgba(201,168,76,0.5);
            margin: 0 auto 14px;
            overflow: hidden;
            display: flex; align-items: center; justify-content: center;
        }

        .form-card-header .logo-wrap img {
            width: 100%; height: 100%; object-fit: cover;
        }

        .form-card-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 6px;
        }

        .form-card-header p {
            font-size: 0.82rem;
            color: rgba(240,217,138,0.75);
            letter-spacing: 0.05em;
        }

        .form-body {
            padding: 32px 36px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            color: var(--text);
            background: #fafaf8;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--navy);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(26,60,94,0.08);
        }

        /* Row dua kolom */
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-divider {
            height: 1px;
            background: var(--border);
            margin: 24px 0;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .btn-submit {
            flex: 2;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--gold), #a8873a);
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 16px rgba(201,168,76,0.35);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(201,168,76,0.45);
        }

        .btn-reset {
            flex: 1;
            padding: 13px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: transparent;
            color: var(--navy);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-reset:hover { background: #f5f0e8; border-color: var(--navy); }

        /* ══════════════════════
           FOOTER
        ══════════════════════ */
        .site-footer {
            background: linear-gradient(90deg, #3005f1, #000000);
            color: #fff;
            padding: 40px 40px 0;
        }

        .footer-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
            flex-wrap: wrap;
            padding-bottom: 32px;
        }

        .footer-brand { display: flex; align-items: flex-start; gap: 16px; max-width: 280px; }

        .footer-logo {
            width: 56px; height: 56px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
            overflow: hidden; flex-shrink: 0;
            background: rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center; font-size: 24px;
        }
        .footer-logo img { width: 100%; height: 100%; object-fit: cover; }
        .footer-brand-info h3 { font-size: 1rem; font-weight: 700; color: #fff; margin: 0 0 4px; }
        .footer-brand-info p { font-size: 0.78rem; color: rgba(255,255,255,0.6); line-height: 1.5; margin: 0; }

        .footer-kontak h4 { font-size: 0.9rem; font-weight: 700; color: #fff; margin: 0 0 14px; }
        .footer-kontak-list { display: flex; flex-direction: column; gap: 10px; }
        .footer-kontak-item {
            display: flex; align-items: center; gap: 10px;
            font-size: 0.82rem; color: rgba(255,255,255,0.8); text-decoration: none;
            transition: color 0.2s;
        }
        .footer-kontak-item:hover { color: #fff; }
        .footer-kontak-icon {
            width: 28px; height: 28px; border-radius: 50%;
            background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; flex-shrink: 0;
        }

        .footer-divider { border: none; border-top: 1px solid rgba(255,255,255,0.15); margin: 0; }
        .footer-bottom {
            padding: 14px 0; text-align: center;
            font-size: 0.78rem; color: rgba(255,255,255,0.5);
        }
        .footer-bottom span { color: rgba(255,255,255,0.8); font-weight: 500; }

        /* ══════════════════════
           ANIMATIONS
        ══════════════════════ */
        @keyframes fadeIn  { from{opacity:0} to{opacity:1} }
        @keyframes fadeUp  { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        @keyframes slideUp { from{opacity:0;transform:translateY(32px)} to{opacity:1;transform:translateY(0)} }

        @media (max-width: 540px) {
            .form-body { padding: 24px 20px; }
            .form-card-header { padding: 28px 20px 22px; }
            .form-row-2 { grid-template-columns: 1fr; }
            .navbar { padding: 0 16px; }
            .navbar-links { display: none; }
        }
    </style>
</head>
<body>

    <!-- ══ NAVBAR ══ -->
    <nav class="navbar">
        <a href="Home.php" class="navbar-brand">
            <img src="MPPK.jpg" alt="Logo"
                 onerror="this.style.display='none'">
            <div class="navbar-brand-text">
                <strong>MPPK</strong>
                <span>SMKN 1 Cibinong</span>
            </div>
        </a>
        <ul class="navbar-links">
            <li><a href="Home.php">Home</a></li>
            <li><a href="Home.php#program">Kegiatan</a></li>
            <li><a href="Home.php#pengurus">Pengurus</a></li>
            <li><a href="daftar.php" class="active">Daftar</a></li>
            <li><a href="../admin/logout.php">LogOut</a></li>
        </ul>
    </nav>

    <!-- ══ TOAST CONTAINER ══ -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- ══ MODAL KONFIRMASI ══ -->
    <div class="modal-overlay" id="modalKonfirmasi">
        <div class="modal-box">
            <div class="modal-icon">📋</div>
            <h3 class="modal-title">Konfirmasi Pendaftaran</h3>
            <p class="modal-desc">
                Pastikan semua data yang kamu isi sudah benar.<br>
                Data tidak bisa diubah setelah disimpan.
            </p>
            <div class="modal-actions">
                <button class="btn-modal-cancel" onclick="tutupModal()">Periksa Lagi</button>
                <button class="btn-modal-confirm" onclick="submitForm()">Ya, Daftar!</button>
            </div>
        </div>
    </div>

    <!-- ══ MAIN ══ -->
    <main>
        <div class="form-card">

            <div class="form-card-header">
                <div class="logo-wrap">
                    <img src="MPPK.jpg" alt="Logo MPPK"
                         onerror="this.parentElement.innerHTML='🎓'">
                </div>
                <h2>Formulir Pendaftaran</h2>
                <p>Majelis Perwakilan Program Kejuruan</p>
            </div>

            <div class="form-body">

                <?php if ($sudah_daftar): ?>
                <!-- ══ SUDAH PERNAH DAFTAR ══ -->
                <?php
                $st = $data_sudah['status'];
                $st_icon  = match($st) {
                    'accepted' => '✅',
                    'rejected' => '❌',
                    default    => '⏳'
                };
                $st_label = match($st) {
                    'accepted' => 'Diterima',
                    'rejected' => 'Ditolak',
                    default    => 'Menunggu Konfirmasi'
                };
                $st_color = match($st) {
                    'accepted' => '#16a34a',
                    'rejected' => '#dc2626',
                    default    => '#d97706'
                };
                $st_bg = match($st) {
                    'accepted' => '#f0fdf4',
                    'rejected' => '#fef2f2',
                    default    => '#fffbeb'
                };
                ?>
                <div style="text-align:center;padding:32px 16px;">
                    <div style="font-size:56px;margin-bottom:16px;"><?= $st_icon ?></div>
                    <h3 style="font-family:'Playfair Display',serif;font-size:1.3rem;
                                color:var(--navy);margin-bottom:10px;">
                        Kamu Sudah Mendaftar
                    </h3>
                    <p style="font-size:0.88rem;color:var(--muted);line-height:1.6;margin-bottom:16px;">
                        Setiap akun hanya bisa mendaftar <strong>satu kali</strong>.<br>
                        Kamu sudah memiliki pendaftaran aktif.
                    </p>

                    <!-- Info kode resi & status -->
                    <div style="background:var(--bg);border:1.5px solid var(--border);
                                border-radius:12px;padding:16px;margin-bottom:20px;text-align:left;">
                        <div style="display:flex;justify-content:space-between;
                                    align-items:center;gap:12px;flex-wrap:wrap;">
                            <div>
                                <div style="font-size:10px;font-weight:600;letter-spacing:0.1em;
                                            text-transform:uppercase;color:var(--muted);margin-bottom:4px;">
                                    Kode Resi
                                </div>
                                <div style="font-family:'Courier New',monospace;font-size:1rem;
                                            font-weight:700;color:var(--navy);">
                                    <?= htmlspecialchars($data_sudah['kode_resi'] ?? '-') ?>
                                </div>
                            </div>
                            <div style="background:<?= $st_bg ?>;color:<?= $st_color ?>;
                                        padding:6px 14px;border-radius:20px;
                                        font-size:0.82rem;font-weight:700;
                                        border:1px solid <?= $st_color ?>33;">
                                <?= $st_icon ?> <?= $st_label ?>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                        <a href="cetak_pendaftaran_user.php?id=<?= $data_sudah['id'] ?>"
                           target="_blank"
                           style="display:inline-flex;align-items:center;gap:8px;
                                  background:linear-gradient(135deg,var(--gold),#a8873a);
                                  color:#fff;text-decoration:none;padding:11px 20px;
                                  border-radius:10px;font-size:0.85rem;font-weight:600;">
                            🖨️ Cetak Bukti
                        </a>
                        <a href="Home.php"
                           style="display:inline-flex;align-items:center;gap:8px;
                                  border:1.5px solid var(--border);color:var(--navy);
                                  text-decoration:none;padding:11px 20px;
                                  border-radius:10px;font-size:0.85rem;font-weight:500;">
                            ← Beranda
                        </a>
                    </div>
                </div>

                <?php elseif ($pendaftaran_tutup): ?>
                <!-- ══ PENDAFTARAN DITUTUP ══ -->
                <div style="text-align:center;padding:32px 16px;">
                    <div style="font-size:56px;margin-bottom:16px;">🔒</div>
                    <h3 style="font-family:'Playfair Display',serif;font-size:1.4rem;
                                color:var(--navy);margin-bottom:10px;">
                        Pendaftaran Ditutup
                    </h3>
                    <p style="font-size:0.88rem;color:var(--muted);line-height:1.6;margin-bottom:24px;">
                        Maaf, pendaftaran MPPK sedang tidak dibuka.<br>
                        Pantau terus informasi terbaru melalui Instagram kami.
                    </p>
                    <a href="https://www.instagram.com/mppksmkn1cbn"
                       target="_blank"
                       style="display:inline-flex;align-items:center;gap:8px;
                              background:linear-gradient(135deg,var(--navy),var(--navy2));
                              color:#fff;text-decoration:none;padding:11px 24px;
                              border-radius:10px;font-size:0.88rem;font-weight:600;">
                        📸 @mppksmkn1cbn
                    </a>
                    <div style="margin-top:20px;">
                        <a href="Home.php"
                           style="font-size:0.82rem;color:var(--muted);text-decoration:none;">
                            ← Kembali ke Beranda
                        </a>
                    </div>
                </div>

                <?php else: ?>
                <!-- ══ FORM PENDAFTARAN ══ -->
                <form method="post" action="simpan.php" id="daftarForm">

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama"
                               placeholder="Masukkan nama lengkap kamu" required>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email"
                                   placeholder="contoh@email.com" required>
                        </div>
                        <div class="form-group">
                            <label>No. Telepon</label>
                            <input type="text" name="no_telpon"
                                   placeholder="08xx-xxxx-xxxx" required>
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" required>
                        </div>
                        <div class="form-group">
                            <label>Jurusan</label>
                            <input type="text" name="jurusan"
                                   placeholder="Nama jurusan" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" name="alamat"
                               placeholder="Alamat lengkap kamu" required>
                    </div>

                    <div class="form-group">
                        <label>Alasan Masuk MPPK</label>
                        <input type="text" name="alasan"
                               placeholder="Ceritakan alasanmu bergabung..." required>
                    </div>

                    <div class="form-divider"></div>

                    <div class="btn-group">
                        <button type="button" class="btn-submit" onclick="bukaModal()">
                            ✍️ Daftar Sekarang
                        </button>
                        <button type="reset" class="btn-reset" onclick="resetNotif()">
                            Batal
                        </button>
                    </div>

                </form>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <!-- ══ FOOTER ══ -->
    <footer class="site-footer">
        <div class="footer-top">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="MPPK.jpg" alt="Logo MPPK"
                         onerror="this.parentElement.innerHTML='🎓'">
                </div>
                <div class="footer-brand-info">
                    <h3>MPPK</h3>
                    <p>Majelis Perwakilan Program Kejuruan<br>SMKN 1 Cibinong</p>
                </div>
            </div>
            <div class="footer-kontak">
                <h4>Kontak</h4>
                <div class="footer-kontak-list">
                    <a href="https://wa.me/6285719583295" target="_blank" class="footer-kontak-item">
                        <div class="footer-kontak-icon">📱</div>
                        0857-1958-3295
                    </a>
                    <a href="https://www.instagram.com/mppksmkn1cbn" target="_blank" class="footer-kontak-item">
                        <div class="footer-kontak-icon">📸</div>
                        @mppksmkn1cbn
                    </a>
                    <a href="mailto:mppk@smkn1cibinong.sch.id" class="footer-kontak-item">
                        <div class="footer-kontak-icon">✉️</div>
                        mppk@smkn1cibinong.sch.id
                    </a>
                </div>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="footer-bottom">
            © 2026 <span>MPPK SMKN 1 Cibinong</span> — Developed by Team 7
        </div>
    </footer>

    <script>
    /* ════ TOAST SYSTEM ════ */
    const icons = {
        success: '✅',
        error:   '❌',
        warning: '⚠️',
        info:    'ℹ️',
    };

    const titles = {
        success: 'Berhasil',
        error:   'Gagal',
        warning: 'Perhatian',
        info:    'Informasi',
    };

    function showToast(type, msg, duration = 3500) {
        const container = document.getElementById('toastContainer');

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${icons[type]}</div>
            <div class="toast-body">
                <div class="toast-title">${titles[type]}</div>
                <div class="toast-msg">${msg}</div>
            </div>
            <button class="toast-close" onclick="dismissToast(this.parentElement)">×</button>
            <div class="toast-progress"></div>
        `;

        container.appendChild(toast);

        // Animasi masuk
        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });

        // Auto dismiss
        setTimeout(() => dismissToast(toast), duration);
    }

    function dismissToast(toast) {
        if (!toast) return;
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 400);
    }

    /* ════ MODAL KONFIRMASI ════ */
    function bukaModal() {
        // Validasi dulu sebelum buka modal
        const form = document.getElementById('daftarForm');
        const inputs = form.querySelectorAll('[required]');
        let valid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                valid = false;
                input.style.borderColor = '#ef4444';
                input.style.boxShadow   = '0 0 0 3px rgba(239,68,68,0.1)';
                input.addEventListener('input', function() {
                    this.style.borderColor = '';
                    this.style.boxShadow   = '';
                }, { once: true });
            }
        });

        if (!valid) {
            showToast('warning', 'Lengkapi semua field yang wajib diisi terlebih dahulu.');
            return;
        }

        // Validasi email
        const email = form.querySelector('[name="email"]').value;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showToast('error', 'Format email tidak valid. Contoh: nama@email.com');
            form.querySelector('[name="email"]').focus();
            return;
        }

        document.getElementById('modalKonfirmasi').classList.add('active');
    }

    function tutupModal() {
        document.getElementById('modalKonfirmasi').classList.remove('active');
    }

    function submitForm() {
        tutupModal();
        showToast('info', 'Menyimpan data pendaftaran...');
        setTimeout(() => {
            document.getElementById('daftarForm').submit();
        }, 600);
    }

    function resetNotif() {
        showToast('info', 'Formulir telah direset.');
    }

    // Tutup modal klik luar
    document.getElementById('modalKonfirmasi').addEventListener('click', function(e) {
        if (e.target === this) tutupModal();
    });

    // Tutup modal dengan Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') tutupModal();
    });

    // Navbar hide on scroll
    let lastST = 0;
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        const st = window.pageYOffset;
        navbar.style.top = st > lastST && st > 80 ? '-70px' : '0';
        lastST = st;
    });
    </script>

</body>
</html>