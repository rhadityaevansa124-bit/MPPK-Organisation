<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'user'){
    header("location:../login.php");
    exit();
}

include '../koneksi/koneksi.php';

$username = $_SESSION['username'];

$queryUser = mysqli_query($koneksi, "SELECT * FROM tb_login WHERE username='$username'");
$user = mysqli_fetch_assoc($queryUser);

$photos = [];
$stmt_photos = $koneksi->prepare("SELECT id, file, title, description FROM tb_galerry ORDER BY id DESC LIMIT 5");
$stmt_photos->execute();
$res_photos = $stmt_photos->get_result();
while ($row_photo = $res_photos->fetch_assoc()) {
    $photos[] = $row_photo;
}
$stmt_photos->close();

// Ambil data pengurus dari tabel tb_pengurus
$pengurus = [];
$stmt_pengurus = $koneksi->prepare("SELECT id, nama, jabatan, foto, instagram FROM tb_pengurus ORDER BY urutan ASC, id ASC");
$stmt_pengurus->execute();
$res_pengurus = $stmt_pengurus->get_result();
while ($row_pengurus = $res_pengurus->fetch_assoc()) {
    $pengurus[] = $row_pengurus;
}
$stmt_pengurus->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPPK</title>
    <link rel="stylesheet" href="../style/utama.css">
    <style>
        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333;
            min-height: 100vh;
            background: url('../ya.png') no-repeat center center fixed;
            background-size: cover;
            overflow-x: hidden;
            padding-top: 80px;
        }

        .navbar {
            position: fixed;
            top: 0; left: 0; width: 95%;
            background: linear-gradient(90deg, #3005f1, #000000);
            z-index: 999;
            transition: top 0.3s;
        }

        /* Modal base */
        .modal-overlay {
            display: none; position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); z-index: 2000;
        }
        .modal-overlay.active {
            display: flex; align-items: center; justify-content: center;
            animation: fadeIn 0.3s ease;
        }
        .modal-content {
            background: #fff; border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 600px; width: 90%; max-height: 80vh;
            overflow-y: auto; animation: slideUp 0.3s ease;
        }
        .modal-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 24px; border-bottom: 1px solid #eee;
        }
        .modal-header h2 { margin: 0; color: #00796b; font-size: 1.6em; }
        .modal-close-btn {
            background: none; border: none; font-size: 28px; color: #999;
            cursor: pointer; width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            transition: color 0.2s;
        }
        .modal-close-btn:hover { color: #333; }
        .modal-body { padding: 24px; }
        .modal-img { width: 100%; height: 300px; object-fit: cover; border-radius: 12px; margin-bottom: 16px; }
        .modal-description { font-size: 1.1em; color: #333; line-height: 1.6; margin: 0; }
        .menu-item { cursor: pointer; }

        /* Keyframes */
        @keyframes fadeIn { from{opacity:0} to{opacity:1} }
        @keyframes slideUp { from{transform:translateY(40px);opacity:0} to{transform:translateY(0);opacity:1} }
        @keyframes notifDrop {
            from { opacity:0; transform: translateY(-12px) scale(0.96); }
            to   { opacity:1; transform: translateY(0) scale(1); }
        }
        @keyframes badgePop {
            0%{transform:scale(1)} 40%{transform:scale(1.55)} 100%{transform:scale(1)}
        }
        @keyframes bellRing {
            0%,100%{transform:rotate(0)} 20%{transform:rotate(15deg)}
            40%{transform:rotate(-12deg)} 60%{transform:rotate(9deg)}
            80%{transform:rotate(-5deg)}
        }

        /* ══════════════════════
           NOTIFIKASI
        ══════════════════════ */
        .notif-wrapper { position: relative; display: flex; align-items: center; }

        .notif-bell-btn {
            background: rgba(255,255,255,0.12);
            border: 1.5px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            width: 42px; height: 42px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 20px; position: relative;
            transition: background 0.2s, border-color 0.2s;
        }
        .notif-bell-btn:hover {
            background: rgba(255,255,255,0.22);
            border-color: rgba(255,255,255,0.4);
        }
        .notif-bell-btn.ring { animation: bellRing 0.5s ease; }

        #notif-count {
            position: absolute; top: -6px; right: -6px;
            background: linear-gradient(135deg, #ff5252, #c0392b);
            color: #fff; font-size: 10px; font-weight: 700;
            min-width: 18px; height: 18px; padding: 0 4px;
            border-radius: 9px; display: none;
            align-items: center; justify-content: center;
            border: 2px solid rgba(0,0,0,0.3);
            box-shadow: 0 2px 6px rgba(192,57,43,0.45);
        }
        #notif-count.show { display: flex; animation: badgePop 0.35s ease; }

        #notif-dropdown {
            display: none; position: absolute;
            right: 0; top: calc(100% + 12px);
            width: 340px; background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 48px rgba(0,0,0,0.18), 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden; z-index: 1001;
            border: 1px solid rgba(0,0,0,0.05);
        }
        #notif-dropdown.open {
            display: block;
            animation: notifDrop 0.25s ease both;
        }

        .notif-panel-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px;
            background: linear-gradient(135deg, #1a3c5e, #234f7a);
        }
        .notif-head-left { display: flex; align-items: center; gap: 8px; }
        .notif-head-icon { font-size: 18px; }
        .notif-head-title { font-size: 0.88rem; font-weight: 700; color: #fff; letter-spacing: 0.03em; }
        .notif-pill {
            background: rgba(201,168,76,0.25); border: 1px solid rgba(201,168,76,0.45);
            color: #f0d98a; font-size: 10px; font-weight: 700;
            padding: 2px 9px; border-radius: 20px; display: none;
        }
        .notif-mark-all-btn {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.8); font-size: 11px; cursor: pointer;
            padding: 5px 10px; border-radius: 8px; transition: background 0.2s;
        }
        .notif-mark-all-btn:hover { background: rgba(255,255,255,0.2); color: #fff; }

        #notif-list { max-height: 290px; overflow-y: auto; padding: 6px 0; }
        #notif-list::-webkit-scrollbar { width: 4px; }
        #notif-list::-webkit-scrollbar-thumb { background: #e2d9c8; border-radius: 4px; }

        .notif-item {
            display: flex; align-items: flex-start; gap: 11px;
            padding: 11px 16px; cursor: pointer;
            transition: background 0.15s;
            border-bottom: 1px solid #f5f0ea;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: #faf8f4; }
        .notif-item.read { opacity: 0.5; }

        .notif-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #3059f1; flex-shrink: 0; margin-top: 7px;
        }
        .notif-item.read .notif-dot { visibility: hidden; }

        .notif-item-ico {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #eef2ff, #dbeafe);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .notif-item-body { flex: 1; min-width: 0; }
        .notif-item-msg {
            font-size: 0.82rem; color: #1a1a2e;
            line-height: 1.45; margin: 0 0 5px; word-break: break-word;
        }
        .notif-item-time { font-size: 0.71rem; color: #b0b7c3; }

        .notif-empty {
            text-align: center; padding: 36px 20px 28px;
        }
        .notif-empty-icon { font-size: 40px; margin-bottom: 10px; opacity: 0.55; }
        .notif-empty-title { font-size: 0.88rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
        .notif-empty-sub { font-size: 0.78rem; color: #9ca3af; }

        .notif-panel-footer {
            padding: 10px 18px; border-top: 1px solid #f0ece4;
            text-align: center; background: #fafaf9;
        }
        .notif-footer-txt {
            font-size: 0.78rem; color: #c9a84c; cursor: pointer; font-weight: 500;
        }
        .notif-footer-txt:hover { text-decoration: underline; }

        /* ══════════════════════
           Profile Modal
        ══════════════════════ */
        #profileModal .modal-content { max-width: 520px; overflow: hidden; }
        .profile-banner {
            background: linear-gradient(135deg, #1a3c5e, #234f7a);
            padding: 32px 28px 24px; display: flex; align-items: center; gap: 18px;
        }
        .profile-avatar {
            width: 64px; height: 64px; border-radius: 50%;
            background: rgba(201,168,76,0.25); border: 2px solid rgba(201,168,76,0.6);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; flex-shrink: 0;
        }
        .profile-banner-info span {
            display: block; font-size: 11px; letter-spacing: 0.16em;
            text-transform: uppercase; color: rgba(240,217,138,0.7); margin-bottom: 4px;
        }
        .profile-banner-info strong { font-size: 1.2rem; font-weight: 600; color: #fff; }

        .profile-info-list { padding: 20px 28px 4px; display: flex; flex-direction: column; }
        .profile-info-row {
            display: flex; align-items: center; gap: 12px;
            padding: 13px 0; border-bottom: 1px solid #f0ece4;
        }
        .profile-info-row:last-child { border-bottom: none; }
        .profile-info-icon {
            width: 34px; height: 34px; background: #f5f0e8; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .profile-info-text label {
            display: block; font-size: 11px; font-weight: 500;
            color: #9ca3af; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2px;
        }
        .profile-info-text span { font-size: 0.92rem; color: #1a1a2e; }

        .pendaftaran-section { padding: 0 28px 24px; }
        .pendaftaran-section-title {
            font-size: 12px; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.14em; color: #9ca3af; margin-bottom: 12px;
            padding-top: 16px; border-top: 1px solid #f0ece4;
            display: flex; align-items: center; gap: 8px;
        }
        .pendaftaran-section-title::before {
            content:''; display:inline-block; width:16px; height:2px;
            background:#c9a84c; border-radius:2px;
        }
        .pendaftaran-loading { text-align:center; padding:20px; color:#9ca3af; font-size:0.85rem; }
        .pendaftaran-empty {
            text-align:center; padding:20px 16px; background:#faf8f4;
            border-radius:12px; border:1.5px dashed #e2d9c8;
        }
        .pendaftaran-empty .empty-icon { font-size:32px; margin-bottom:8px; }
        .pendaftaran-empty p { font-size:0.85rem; color:#9ca3af; margin-bottom:12px; }
        .btn-daftar-now {
            display:inline-block; background:linear-gradient(135deg,#1a3c5e,#234f7a);
            color:#fff; text-decoration:none; padding:9px 20px;
            border-radius:8px; font-size:0.82rem; font-weight:500; transition:opacity 0.2s;
        }
        .btn-daftar-now:hover { opacity:0.85; }
        .pendaftaran-card {
            display:flex; align-items:center; gap:12px; background:#faf8f4;
            border:1px solid #e2d9c8; border-radius:12px; padding:14px 16px;
            margin-bottom:10px; transition:border-color 0.2s, box-shadow 0.2s;
        }
        .pendaftaran-card:last-child { margin-bottom:0; }
        .pendaftaran-card:hover { border-color:#c9a84c; box-shadow:0 4px 16px rgba(201,168,76,0.12); }
        .pendaftaran-card-icon {
            width:40px; height:40px; background:linear-gradient(135deg,#1a3c5e,#234f7a);
            border-radius:10px; display:flex; align-items:center;
            justify-content:center; font-size:18px; flex-shrink:0;
        }
        .pendaftaran-card-info { flex:1; min-width:0; }
        .pendaftaran-card-info strong {
            display:block; font-size:0.88rem; font-weight:600; color:#1a1a2e;
            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
        }
        .pendaftaran-card-info small { font-size:0.78rem; color:#9ca3af; }
        .btn-cetak-kecil {
            background:linear-gradient(135deg,#c9a84c,#a8873a); color:#fff; border:none;
            border-radius:8px; padding:8px 14px; font-size:0.78rem; font-weight:500;
            cursor:pointer; white-space:nowrap; text-decoration:none;
            display:inline-flex; align-items:center; gap:5px;
            transition:opacity 0.2s, transform 0.15s; flex-shrink:0;
        }
        .btn-cetak-kecil:hover { opacity:0.88; transform:translateY(-1px); }

        .site-footer {
            background: linear-gradient(90deg, #3005f1, #000000);
            color: #fff;
            padding: 52px 40px 0;
            position: relative;
            overflow: hidden;
            margin-bottom: 0;
        }

        /* Hapus efek sparkle */
        .site-footer::before { display: none; }

        .footer-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
            padding-bottom: 40px;
        }

        /* Brand kiri */
        .footer-brand {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            max-width: 280px;
        }
        .footer-logo {
            width: 64px; height: 64px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
            object-fit: cover;
            flex-shrink: 0;
            background: rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            overflow: hidden;
        }
        .footer-logo img { width: 100%; height: 100%; object-fit: cover; }
        .footer-brand-info h3 {
            font-size: 1.2rem; font-weight: 700; color: #fff;
            margin: 0 0 6px; letter-spacing: 0.02em;
        }
        .footer-brand-info p {
            font-size: 0.82rem; color: rgba(255,255,255,0.65);
            line-height: 1.5; margin: 0;
        }

        /* Kolom kontak kanan */
        .footer-kontak h4 {
            font-size: 1rem; font-weight: 700; color: #fff;
            margin: 0 0 18px; letter-spacing: 0.03em;
        }
        .footer-kontak-list {
            display: flex; flex-direction: column; gap: 12px;
        }
        .footer-kontak-item {
            display: flex; align-items: center; gap: 10px;
            font-size: 0.88rem; color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-kontak-item:hover { color: #fff; }
        .footer-kontak-icon {
            width: 32px; height: 32px; border-radius: 50%;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; flex-shrink: 0;
            transition: background 0.2s;
        }
        .footer-kontak-item:hover .footer-kontak-icon {
            background: rgba(255,255,255,0.22);
        }

        /* Garis pemisah */
        .footer-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.15);
            margin: 0;
            position: relative; z-index: 1;
        }

        /* Copyright bawah */
        .footer-bottom {
            padding: 16px 0;
            text-align: center;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.55);
            position: relative; z-index: 1;
        }
        .footer-bottom span { color: rgba(255,255,255,0.8); font-weight: 500; }


        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap');

        .pengurus-section {
            padding: 60px 24px 70px;
            position: relative;
        }
        .pengurus-section-head { text-align: center; margin-bottom: 48px; }
        .pengurus-section-head .sub-label {
            display: inline-block; font-size: 11px; font-weight: 600;
            letter-spacing: 0.25em; text-transform: uppercase;
            color: #c9a84c; background: rgba(201,168,76,0.1);
            border: 1px solid rgba(201,168,76,0.3);
            padding: 5px 16px; border-radius: 20px; margin-bottom: 14px;
        }
        .pengurus-section-head h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.6rem, 4vw, 2.4rem);
            color: #fff; text-shadow: 0 2px 12px rgba(0,0,0,0.4);
            margin: 0 0 10px;
        }
        .pengurus-section-head p { font-size: 0.9rem; color: rgba(255,255,255,0.6); margin: 0 auto; }

        .ornamen-divider {
            display: flex; align-items: center; justify-content: center;
            gap: 10px; margin-bottom: 40px;
        }
        .ornamen-divider::before {
            content: ''; flex: 1; max-width: 80px; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201,168,76,0.5));
        }
        .ornamen-divider::after {
            content: ''; flex: 1; max-width: 80px; height: 1px;
            background: linear-gradient(90deg, rgba(201,168,76,0.5), transparent);
        }
        .ornamen-divider span { font-size: 18px; opacity: 0.7; }

        /* ── Wrapper utama seluruh struktur ── */
        .pengurus-wrapper {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }

        /* Baris 1: Ketua sendiri di tengah */
        .row-ketua {
            display: flex;
            justify-content: center;
            margin-bottom: 8px;
            position: relative;
        }

        /* Garis konektor dari ketua ke bawah */
        .row-ketua::after {
            content: '';
            position: absolute;
            bottom: -28px; left: 50%;
            transform: translateX(-50%);
            width: 2px; height: 28px;
            background: linear-gradient(180deg, rgba(201,168,76,0.7), rgba(201,168,76,0.2));
        }

        /* Baris 2: Wakil — bisa 1 atau 2 orang */
        .row-wakil {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 28px;
            margin-bottom: 8px;
            position: relative;
        }

        /* Garis horizontal wakil */
        .row-wakil::before {
            content: '';
            position: absolute;
            top: -28px; left: 50%;
            transform: translateX(-50%);
            width: 2px; height: 28px;
            background: linear-gradient(180deg, rgba(201,168,76,0.2), rgba(201,168,76,0.5));
        }

        /* Garis ke anggota dari wakil */
        .row-wakil::after {
            content: '';
            position: absolute;
            bottom: -28px; left: 50%;
            transform: translateX(-50%);
            width: 2px; height: 28px;
            background: linear-gradient(180deg, rgba(201,168,76,0.5), rgba(201,168,76,0.15));
        }

        /* Baris 3: Anggota lainnya - flex wrap rata tengah */
        .row-anggota {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            width: 100%;
            margin-top: 28px;
            position: relative;
            padding-top: 8px;
        }

        /* Tiap card anggota lebar tetap agar konsisten */
        .row-anggota .pengurus-card {
            width: 175px;
            flex-shrink: 0;
        }

        /* Garis atas row anggota */
        .row-anggota::before {
            content: '';
            position: absolute;
            top: -20px; left: 50%;
            transform: translateX(-50%);
            width: 2px; height: 20px;
            background: linear-gradient(180deg, rgba(201,168,76,0.15), rgba(201,168,76,0.4));
        }

        /* ── Card base ── */
        .pengurus-card {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 20px; padding: 28px 20px 22px;
            text-align: center; backdrop-filter: blur(10px);
            transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s, background 0.3s;
            position: relative; overflow: hidden;
            opacity: 0;
            animation: pengurusIn 0.6s ease forwards;
        }
        .pengurus-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #c9a84c, #f0d98a, #c9a84c);
            opacity: 0; transition: opacity 0.3s;
        }
        .pengurus-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 48px rgba(0,0,0,0.3);
            border-color: rgba(201,168,76,0.4);
            background: rgba(255,255,255,0.12);
        }
        .pengurus-card:hover::before { opacity: 1; }

        /* Ketua — lebih besar, paling menonjol */
        .pengurus-card.ketua {
            background: rgba(201,168,76,0.12);
            border-color: rgba(201,168,76,0.4);
            width: 200px;
            padding: 32px 24px 26px;
        }
        .pengurus-card.ketua::before { opacity: 1; }

        /* Wakil — sedikit lebih kecil dari ketua */
        .pengurus-card.wakil {
            background: rgba(255,255,255,0.09);
            border-color: rgba(201,168,76,0.2);
            width: 175px;
        }
        .pengurus-card.wakil::before { opacity: 0.6; }

        .pengurus-foto-wrap { position: relative; width: 88px; height: 88px; margin: 0 auto 16px; }
        .pengurus-foto {
            width: 88px; height: 88px; border-radius: 50%; object-fit: cover;
            border: 3px solid rgba(201,168,76,0.5); display: block;
            transition: border-color 0.3s;
        }
        .pengurus-card:hover .pengurus-foto { border-color: #c9a84c; }

        /* Foto ketua lebih besar */
        .pengurus-card.ketua .pengurus-foto-wrap { width: 110px; height: 110px; }
        .pengurus-card.ketua .pengurus-foto {
            width: 110px; height: 110px; border-color: #c9a84c;
            box-shadow: 0 0 0 6px rgba(201,168,76,0.18), 0 0 0 12px rgba(201,168,76,0.06);
        }

        /* Foto wakil sedikit lebih besar dari anggota */
        .pengurus-card.wakil .pengurus-foto-wrap { width: 96px; height: 96px; }
        .pengurus-card.wakil .pengurus-foto {
            width: 96px; height: 96px; border-color: rgba(201,168,76,0.6);
        }

        .pengurus-foto-placeholder {
            width: 88px; height: 88px; border-radius: 50%;
            background: linear-gradient(135deg, #1a3c5e, #234f7a);
            border: 3px solid rgba(201,168,76,0.4);
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; margin: 0 auto 16px;
            transition: border-color 0.3s;
        }
        .pengurus-card.ketua .pengurus-foto-placeholder {
            width: 110px; height: 110px; font-size: 44px;
            border-color: #c9a84c;
            box-shadow: 0 0 0 6px rgba(201,168,76,0.18);
        }
        .pengurus-card.wakil .pengurus-foto-placeholder { width: 96px; height: 96px; font-size: 38px; }
        .pengurus-card:hover .pengurus-foto-placeholder { border-color: #c9a84c; }

        .pengurus-jabatan {
            display: inline-block; font-size: 10px; font-weight: 700;
            letter-spacing: 0.15em; text-transform: uppercase;
            color: #c9a84c; background: rgba(201,168,76,0.12);
            border: 1px solid rgba(201,168,76,0.3);
            padding: 3px 10px; border-radius: 20px; margin-bottom: 8px;
        }
        .pengurus-card.ketua .pengurus-jabatan {
            background: rgba(201,168,76,0.22); border-color: rgba(201,168,76,0.6);
            font-size: 11px;
        }
        .pengurus-card.wakil .pengurus-jabatan {
            background: rgba(201,168,76,0.15); border-color: rgba(201,168,76,0.4);
        }
        .pengurus-nama {
            font-size: 0.9rem; font-weight: 600; color: #fff;
            margin: 0 0 12px; line-height: 1.3;
        }
        .pengurus-card.ketua .pengurus-nama { font-size: 1.05rem; }
        .pengurus-card.wakil .pengurus-nama { font-size: 0.95rem; }

        .btn-instagram {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11px; color: rgba(255,255,255,0.6); text-decoration: none;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);
            padding: 4px 12px; border-radius: 20px;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
        }
        .btn-instagram:hover {
            background: rgba(201,168,76,0.2); color: #f0d98a;
            border-color: rgba(201,168,76,0.4);
        }

        .pengurus-empty {
            text-align: center; padding: 40px;
            color: rgba(255,255,255,0.4); font-size: 0.9rem;
            grid-column: 1 / -1;
        }

        @keyframes pengurusIn {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }

        @media (max-width: 600px) {
            .row-wakil { gap: 14px; }
            .pengurus-card.ketua { width: 170px; }
            .pengurus-card.wakil { width: 150px; }
            .row-anggota .pengurus-card { width: 145px; }
            .row-ketua::after, .row-wakil::before,
            .row-wakil::after, .row-anggota::before { display: none; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            <img src="MPPK.jpg" alt="Logo" style="height:48px;vertical-align:middle;">
            <span style="margin-left:12px;font-weight:bold;font-size:1.2em;">
                MPPK<br>
                <span style="font-size:0.9em;font-weight:normal;">SMKN 1 Cibinong</span>
                <span style="font-size:0.8em;font-weight:bold;display:block;margin-top:2px;">
                    Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?> 👋
                </span>
            </span>
        </div>
        <ul>
            <li>
                <div class="notif-wrapper">
                    <!-- Tombol lonceng -->
                    <button class="notif-bell-btn" onclick="toggleNotif(event)" id="notif-bell" title="Notifikasi">
                        🔔
                        <span id="notif-count">0</span>
                    </button>

                    <!-- Panel dropdown -->
                    <div id="notif-dropdown">
                        <div class="notif-panel-head">
                            <div class="notif-head-left">
                                <span class="notif-head-icon">🔔</span>
                                <span class="notif-head-title">Notifikasi</span>
                                <span class="notif-pill" id="notif-pill">0</span>
                            </div>
                            <button class="notif-mark-all-btn" onclick="markAllRead()">Tandai Semua</button>
                        </div>

                        <div id="notif-list">
                            <div class="notif-empty">
                                <div class="notif-empty-icon">🔕</div>
                                <div class="notif-empty-title">Tidak ada notifikasi</div>
                                <div class="notif-empty-sub">Semua sudah terbaca</div>
                            </div>
                        </div>

                        <div class="notif-panel-footer">
                            <span class="notif-footer-txt" onclick="markAllRead()">✓ Tandai semua sudah dibaca</span>
                        </div>
                    </div>
                </div>
            </li>

            <li><a href="#" onclick="openProfile(); return false;">Profile</a></li>
            <li><a href="Home.php">Home</a></li>
            <li><a href="#program">Kegiatan</a></li>
            <li><a href="#pengurus">Pengurus</a></li>
            <li><a href="https://www.instagram.com/mppksmkn1cbn?igsh=MXNyaTZ6MHFycm93ag==">Tentang Kami</a></li>
            <li><a href="../admin/logout.php">LogOut</a></li>
        </ul>
    </nav>

    <section class="rpl">
        <h1>MPPK<br>Organization</h1>
        <p>(Majelis Perwakilan Program Kejuruan)</p>
    </section>

    <section id="program" class="menu-section">
        <h2>Program Kerja MPPK</h2>
        <div class="menu-items">
            <?php for ($i = 0; $i < 5; $i++):
                $foto        = isset($photos[$i]) ? $photos[$i] : null;
                $title       = $foto ? htmlspecialchars($foto['title'])       : 'Program';
                $description = $foto ? htmlspecialchars($foto['description']) : 'Deskripsi program';
                $img_src     = $foto ? '../uploads/' . htmlspecialchars($foto['file']) : 'placeholder.jpg';
            ?>
            <div class="menu-item" onclick="openModal(<?= $i ?>, '<?= $title ?>', '<?= addslashes($description) ?>', '<?= $img_src ?>')">
                <img src="<?= $img_src ?>" alt="<?= $title ?>" style="object-fit:cover;width:100%;height:200px;">
                <h3><?= $title ?></h3>
                <p><?= substr($description, 0, 100) . (strlen($description) > 100 ? '...' : '') ?></p>
            </div>
            <?php endfor; ?>
        </div>
    </section>

    <a href="daftar.php" class="floating-btn">DAFTAR SEKARANG</a>

    <!-- ── Section Struktur Pengurus ── -->
    <section id="pengurus" class="pengurus-section">
        <div class="pengurus-section-head">
            <div class="sub-label">✦ Kepengurusan ✦</div>
            <h2>Struktur Pengurus MPPK</h2>
            <p>Majelis Perwakilan Program Kejuruan<br>SMKN 1 Cibinong</p>
        </div>

        <div class="ornamen-divider"><span>❧</span></div>

        <?php
        // Pisahkan berdasarkan tier jabatan
        $row_ketua   = [];
        $row_wakil   = [];
        $row_anggota = [];
        $src_list    = !empty($pengurus) ? $pengurus : [
            ['id'=>0,'nama'=>'Nama Ketua',       'jabatan'=>'Ketua Umum',       'foto'=>'','instagram'=>'','urutan'=>1],
            ['id'=>0,'nama'=>'Nama Wakil I',      'jabatan'=>'Wakil Ketua I',    'foto'=>'','instagram'=>'','urutan'=>2],
            ['id'=>0,'nama'=>'Nama Wakil II',     'jabatan'=>'Wakil Ketua II',   'foto'=>'','instagram'=>'','urutan'=>3],
            ['id'=>0,'nama'=>'Nama Sekretaris',   'jabatan'=>'Sekretaris I',     'foto'=>'','instagram'=>'','urutan'=>4],
            ['id'=>0,'nama'=>'Nama Bendahara',    'jabatan'=>'Bendahara I',      'foto'=>'','instagram'=>'','urutan'=>5],
            ['id'=>0,'nama'=>'Nama Koordinator',  'jabatan'=>'Koordinator Dept.','foto'=>'','instagram'=>'','urutan'=>6],
        ];
        $is_placeholder = empty($pengurus);
        foreach ($src_list as $p) {
            $jab = strtolower($p['jabatan']);
            if (strpos($jab, 'ketua umum') !== false)   $row_ketua[]   = $p;
            elseif (strpos($jab, 'wakil') !== false)     $row_wakil[]   = $p;
            else                                         $row_anggota[] = $p;
        }
        ?>

        <div class="pengurus-wrapper">

            <!-- ══ BARIS 1: KETUA UMUM ══ -->
            <?php if (!empty($row_ketua)): ?>
            <div class="row-ketua">
                <?php foreach ($row_ketua as $p):
                    $fs = !empty($p['foto']) ? '../uploads/'.htmlspecialchars($p['foto']) : '';
                ?>
                <div class="pengurus-card ketua" style="animation-delay:0s">
                    <?php if ($fs): ?>
                        <div class="pengurus-foto-wrap">
                            <img class="pengurus-foto" src="<?= $fs ?>"
                                 alt="<?= htmlspecialchars($p['nama']) ?>"
                                 onerror="this.parentElement.outerHTML='<div class=&quot;pengurus-foto-placeholder&quot;>👤</div>'">
                        </div>
                    <?php else: ?><div class="pengurus-foto-placeholder">👤</div><?php endif; ?>
                    <div class="pengurus-jabatan"><?= htmlspecialchars($p['jabatan']) ?></div>
                    <p class="pengurus-nama"><?= htmlspecialchars($p['nama']) ?></p>
                    <?php if ($is_placeholder): ?>
                        <span style="font-size:11px;color:rgba(255,255,255,0.3);font-style:italic;">Data belum diisi</span>
                    <?php elseif (!empty($p['instagram'])): ?>
                        <a href="https://instagram.com/<?= htmlspecialchars(ltrim($p['instagram'],'@')) ?>"
                           target="_blank" class="btn-instagram">📸 @<?= htmlspecialchars(ltrim($p['instagram'],'@')) ?></a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- ══ BARIS 2: WAKIL KETUA ══ -->
            <?php if (!empty($row_wakil)): ?>
            <div class="row-wakil">
                <?php foreach ($row_wakil as $idx => $p):
                    $fs    = !empty($p['foto']) ? '../uploads/'.htmlspecialchars($p['foto']) : '';
                    $delay = 0.12 + $idx * 0.08;
                ?>
                <div class="pengurus-card wakil" style="animation-delay:<?= $delay ?>s">
                    <?php if ($fs): ?>
                        <div class="pengurus-foto-wrap">
                            <img class="pengurus-foto" src="<?= $fs ?>"
                                 alt="<?= htmlspecialchars($p['nama']) ?>"
                                 onerror="this.parentElement.outerHTML='<div class=&quot;pengurus-foto-placeholder&quot;>👤</div>'">
                        </div>
                    <?php else: ?><div class="pengurus-foto-placeholder">👤</div><?php endif; ?>
                    <div class="pengurus-jabatan"><?= htmlspecialchars($p['jabatan']) ?></div>
                    <p class="pengurus-nama"><?= htmlspecialchars($p['nama']) ?></p>
                    <?php if ($is_placeholder): ?>
                        <span style="font-size:11px;color:rgba(255,255,255,0.3);font-style:italic;">Data belum diisi</span>
                    <?php elseif (!empty($p['instagram'])): ?>
                        <a href="https://instagram.com/<?= htmlspecialchars(ltrim($p['instagram'],'@')) ?>"
                           target="_blank" class="btn-instagram">📸 @<?= htmlspecialchars(ltrim($p['instagram'],'@')) ?></a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- ══ BARIS 3: ANGGOTA LAINNYA ══ -->
            <?php if (!empty($row_anggota)): ?>
            <div class="row-anggota">
                <?php foreach ($row_anggota as $idx => $p):
                    $fs    = !empty($p['foto']) ? '../uploads/'.htmlspecialchars($p['foto']) : '';
                    $delay = 0.28 + $idx * 0.07;
                ?>
                <div class="pengurus-card" style="animation-delay:<?= $delay ?>s">
                    <?php if ($fs): ?>
                        <div class="pengurus-foto-wrap">
                            <img class="pengurus-foto" src="<?= $fs ?>"
                                 alt="<?= htmlspecialchars($p['nama']) ?>"
                                 onerror="this.parentElement.outerHTML='<div class=&quot;pengurus-foto-placeholder&quot;>👤</div>'">
                        </div>
                    <?php else: ?><div class="pengurus-foto-placeholder">👤</div><?php endif; ?>
                    <div class="pengurus-jabatan"><?= htmlspecialchars($p['jabatan']) ?></div>
                    <p class="pengurus-nama"><?= htmlspecialchars($p['nama']) ?></p>
                    <?php if ($is_placeholder): ?>
                        <span style="font-size:11px;color:rgba(255,255,255,0.3);font-style:italic;">Data belum diisi</span>
                    <?php elseif (!empty($p['instagram'])): ?>
                        <a href="https://instagram.com/<?= htmlspecialchars(ltrim($p['instagram'],'@')) ?>"
                           target="_blank" class="btn-instagram">📸 @<?= htmlspecialchars(ltrim($p['instagram'],'@')) ?></a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </section>
    <div class="modal-overlay" id="photoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Judul Foto</h2>
                <button class="modal-close-btn" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <img id="modalImage" class="modal-img" src="" alt="Foto">
                <p id="modalDescription" class="modal-description"></p>
            </div>
        </div>
    </div>

    <!-- Modal Profil -->
    <div id="profileModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header" style="padding:20px 28px 16px;border-bottom:1px solid #f0ece4;">
                <h2 style="font-size:1.15rem;color:#1a3c5e;margin:0;">Profil Saya</h2>
                <button class="modal-close-btn" onclick="closeProfile()">×</button>
            </div>
            <div class="profile-banner">
                <div class="profile-avatar">👤</div>
                <div class="profile-banner-info">
                    <span>Pengguna Aktif</span>
                    <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                </div>
            </div>
            <div class="profile-info-list">
                <div class="profile-info-row">
                    <div class="profile-info-icon">👤</div>
                    <div class="profile-info-text">
                        <label>Username</label>
                        <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                    </div>
                </div>
                <div class="profile-info-row">
                    <div class="profile-info-icon">✉️</div>
                    <div class="profile-info-text">
                        <label>Email</label>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                </div>
            </div>
            <div class="pendaftaran-section">
                <div class="pendaftaran-section-title">Riwayat Pendaftaran</div>
                <div id="pendaftaran-loading" class="pendaftaran-loading">Memuat data...</div>
                <div id="pendaftaran-list" style="display:none;"></div>
            </div>
        </div>
    </div>

    <!-- ── FOOTER ── -->
    <footer class="site-footer">
        <div class="footer-top">

            <!-- Brand / Info -->
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

            <!-- Kontak -->
            <div class="footer-kontak">
                <h4>Kontak</h4>
                <div class="footer-kontak-list">
                    <a href="https://api.whatsapp.com/send/?phone=6285881580209&text=Halo+saya+ingin+bertanya+tentang+MPPK&type=phone_number&app_absent=0" target="_blank" class="footer-kontak-item">
                        <div class="footer-kontak-icon">📱</div>
                        0858-8158-0209
                    </a>
                    <a href="https://www.instagram.com/mppksmkn1cbn?igsh=MXNyaTZ6MHFycm93ag==" target="_blank" class="footer-kontak-item">
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
    /* ════ NOTIFIKASI ════ */
    let notifOpen = false;
    let prevNotifCount = -1;

    function toggleNotif(e) {
        e.stopPropagation();
        notifOpen = !notifOpen;
        document.getElementById("notif-dropdown").classList.toggle("open", notifOpen);
    }

    document.addEventListener("click", function(e) {
        const wrapper = document.querySelector(".notif-wrapper");
        if (notifOpen && wrapper && !wrapper.contains(e.target)) {
            notifOpen = false;
            document.getElementById("notif-dropdown").classList.remove("open");
        }
    });

    function loadNotif() {
        fetch('get_notif.php')
            .then(r => r.json())
            .then(data => {
                const count = data ? data.length : 0;

                // Animasi lonceng jika ada notif baru masuk
                if (prevNotifCount >= 0 && count > prevNotifCount) {
                    const bell = document.getElementById("notif-bell");
                    bell.classList.remove("ring");
                    void bell.offsetWidth;
                    bell.classList.add("ring");
                }
                prevNotifCount = count;

                renderNotif(data || []);
            })
            .catch(() => {});
    }

    function renderNotif(data) {
        const list  = document.getElementById("notif-list");
        const badge = document.getElementById("notif-count");
        const pill  = document.getElementById("notif-pill");

        list.innerHTML = "";

        if (data.length === 0) {
            badge.classList.remove("show");
            pill.style.display = "none";
            list.innerHTML = `
                <div class="notif-empty">
                    <div class="notif-empty-icon">🔕</div>
                    <div class="notif-empty-title">Tidak ada notifikasi</div>
                    <div class="notif-empty-sub">Semua sudah terbaca</div>
                </div>`;
            return;
        }

        badge.textContent = data.length;
        badge.classList.add("show");
        pill.textContent = data.length;
        pill.style.display = "inline";

        data.forEach(n => {
            const item = document.createElement("div");
            item.className = "notif-item";
            item.dataset.id = n.id;
            item.innerHTML = `
                <div class="notif-dot"></div>
                <div class="notif-item-ico">📢</div>
                <div class="notif-item-body">
                    <p class="notif-item-msg">${escHtml(n.message)}</p>
                    <span class="notif-item-time">🕐 ${escHtml(n.created_at)}</span>
                </div>`;
            item.onclick = () => {
                item.classList.add("read");
                markRead(n.id);
            };
            list.appendChild(item);
        });
    }

    function markRead(id) {
        fetch('mark_notif_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id
        });
    }

    function markAllRead() {
        document.querySelectorAll(".notif-item").forEach(item => {
            item.classList.add("read");
            markRead(item.dataset.id);
        });
        document.getElementById("notif-count").classList.remove("show");
        document.getElementById("notif-pill").style.display = "none";
    }

    setInterval(loadNotif, 3000);
    loadNotif();

    /* ════ NAVBAR SCROLL ════ */
    let lastScrollTop = 0;
    const navbar = document.querySelector(".navbar");
    window.addEventListener("scroll", function () {
        const st = window.pageYOffset || document.documentElement.scrollTop;
        navbar.style.top = st > lastScrollTop ? "-100px" : "0";
        lastScrollTop = st;
    });

    /* ════ MODAL GALERI ════ */
    function openModal(id, title, description, imgSrc) {
        document.getElementById('modalTitle').textContent       = title;
        document.getElementById('modalDescription').textContent = description;
        document.getElementById('modalImage').src               = imgSrc;
        document.getElementById('photoModal').classList.add('active');
    }
    function closeModal() { document.getElementById('photoModal').classList.remove('active'); }
    document.getElementById('photoModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    /* ════ MODAL PROFIL ════ */
    function openProfile() {
        document.getElementById("profileModal").classList.add("active");
        loadPendaftaran();
    }
    function closeProfile() { document.getElementById("profileModal").classList.remove("active"); }
    document.getElementById("profileModal").addEventListener("click", function(e) {
        if (e.target === this) closeProfile();
    });

    function loadPendaftaran() {
        const loading = document.getElementById("pendaftaran-loading");
        const list    = document.getElementById("pendaftaran-list");
        loading.style.display = "block";
        list.style.display    = "none";
        list.innerHTML        = "";

        fetch('get_pendaftaran.php')
            .then(r => r.json())
            .then(data => {
                loading.style.display = "none";
                list.style.display    = "block";
                if (!data || data.length === 0) {
                    list.innerHTML = `
                        <div class="pendaftaran-empty">
                            <div class="empty-icon">📋</div>
                            <p>Kamu belum memiliki pendaftaran.</p>
                            <a href="daftar.php" class="btn-daftar-now">Daftar Sekarang</a>
                        </div>`;
                    return;
                }
                data.forEach(d => {
                    list.innerHTML += `
                        <div class="pendaftaran-card">
                            <div class="pendaftaran-card-icon">🎓</div>
                            <div class="pendaftaran-card-info">
                                <strong>${escHtml(d.nama)}</strong>
                                ${d.jurusan ? `<small>${escHtml(d.jurusan)}</small>` : ''}
                            </div>
                            <a href="cetak_pendaftaran_user.php?id=${d.id}" target="_blank" class="btn-cetak-kecil">
                                🖨️ Cetak
                            </a>
                        </div>`;
                });
            })
            .catch(() => {
                loading.style.display = "none";
                list.style.display    = "block";
                list.innerHTML = `<div class="pendaftaran-empty"><p>Gagal memuat data. Coba lagi.</p></div>`;
            });
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g,"&amp;").replace(/</g,"&lt;")
            .replace(/>/g,"&gt;").replace(/"/g,"&quot;");
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeModal(); closeProfile(); }
    });
    </script>
</body>
</html>