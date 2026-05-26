<?php 
session_start(); 
if(!isset($_SESSION['username'])){
    header("location:../admin/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Pendaftaran</title>
    <link rel="stylesheet" href="">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap');

        :root {
        --primary: #1a3c5e;
        --accent: #c9a84c;
        --accent-light: #f0d98a;
        --bg: #f5f0e8;
        --card-bg: #ffffff;
        --text: #1a1a2e;
        --text-muted: #6b7280;
        --border: #e2d9c8;
        --shadow: 0 8px 40px rgba(26, 60, 94, 0.12);
        }

        *, *::before, *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        }

        body {
        font-family: 'DM Sans', sans-serif;
        background-color: var(--bg);
        background-image:
            radial-gradient(ellipse at 10% 20%, rgba(201,168,76,0.08) 0%, transparent 60%),
            radial-gradient(ellipse at 90% 80%, rgba(26,60,94,0.07) 0%, transparent 60%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 48px 16px 64px;
        }

        /* ── Header ── */
        .page-header {
        text-align: center;
        margin-bottom: 40px;
        animation: fadeDown 0.6s ease both;
        }

        .page-header .label {
        font-family: 'DM Sans', sans-serif;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 10px;
        }

        .page-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(2rem, 5vw, 3rem);
        color: var(--primary);
        line-height: 1.15;
        position: relative;
        display: inline-block;
        }

        .page-header h1::after {
        content: '';
        display: block;
        height: 3px;
        width: 60%;
        background: linear-gradient(90deg, var(--accent), transparent);
        margin: 10px auto 0;
        border-radius: 2px;
        }

        /* ── Card ── */
        .bukti-card {
        background: var(--card-bg);
        border-radius: 20px;
        box-shadow: var(--shadow);
        max-width: 620px;
        width: 100%;
        overflow: hidden;
        animation: fadeUp 0.7s ease 0.15s both;
        }

        .card-banner {
        background: linear-gradient(135deg, var(--primary) 0%, #234f7a 100%);
        padding: 28px 36px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        }

        .card-banner .icon {
        width: 48px;
        height: 48px;
        background: rgba(201,168,76,0.2);
        border: 1.5px solid rgba(201,168,76,0.5);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        }

        .card-banner .banner-text span {
        display: block;
        font-size: 11px;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--accent-light);
        opacity: 0.75;
        margin-bottom: 4px;
        }

        .card-banner .banner-text strong {
        font-family: 'Playfair Display', serif;
        font-size: 1.25rem;
        color: #fff;
        }

        /* ── Table ── */
        .bukti-table-wrapper {
        padding: 32px 36px;
        }

        .bukti-table {
        width: 100%;
        border-collapse: collapse;
        }

        .bukti-table tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
        }

        .bukti-table tr:last-child {
        border-bottom: none;
        }

        .bukti-table tr:hover {
        background: rgba(201,168,76,0.04);
        }

        .bukti-table td {
        padding: 14px 8px;
        vertical-align: top;
        font-size: 0.9rem;
        }

        .bukti-table td:first-child {
        color: var(--text-muted);
        font-weight: 500;
        width: 40%;
        font-size: 0.82rem;
        letter-spacing: 0.01em;
        padding-top: 16px;
        }

        .bukti-table td:last-child {
        color: var(--text);
        font-weight: 400;
        line-height: 1.6;
        }

        /* ── Actions ── */
        .card-actions {
        padding: 0 36px 32px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        }

        .btn-cetak {
        flex: 1;
        min-width: 140px;
        background: linear-gradient(135deg, var(--accent) 0%, #a8873a 100%);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 13px 24px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.88rem;
        font-weight: 500;
        letter-spacing: 0.05em;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 16px rgba(201,168,76,0.35);
        transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-cetak:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(201,168,76,0.45);
        }

        .btn-cetak:active {
        transform: translateY(0);
        }

        .btn-kembali {
        flex: 1;
        min-width: 140px;
        background: transparent;
        color: var(--primary);
        border: 1.5px solid var(--border);
        border-radius: 10px;
        padding: 13px 24px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.88rem;
        font-weight: 500;
        letter-spacing: 0.05em;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        transition: border-color 0.2s, background 0.2s;
        }

        .btn-kembali:hover {
        border-color: var(--primary);
        background: rgba(26,60,94,0.04);
        }

        /* ── Animations ── */
        @keyframes fadeDown {
        from { opacity: 0; transform: translateY(-18px); }
        to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Print ── */
        @media print {
        body {
            background: #fff;
            padding: 0;
        }

        .card-actions { display: none; }

        .bukti-card {
            box-shadow: none;
            border: 1px solid #ddd;
            border-radius: 0;
            max-width: 100%;
        }

        .card-banner {
            background: #1a3c5e !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page-header h1::after {
            background: var(--accent);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        }
    </style>
</head>
<body>

    <div class="page-header">
        <p class="label">Formulir Resmi</p>
        <h1>Cetak Pendaftaran</h1>
    </div>

    <?php
        include "../koneksi/koneksi.php";
        $tampil = mysqli_query($koneksi, "SELECT * FROM tb_daftar ORDER BY id DESC LIMIT 1");
        while ($data = mysqli_fetch_array($tampil)) :
    ?>

    <div class="bukti-card">

        <div class="card-banner">
            <div class="icon">🎓</div>
            <div class="banner-text">
                <span>Bukti Pendaftaran</span>
                <strong><?php echo htmlspecialchars($data['nama']); ?></strong>
            </div>
        </div>

        <div class="bukti-table-wrapper">
            <table class="bukti-table">
                <tr>
                    <td>Nama Lengkap</td>
                    <td><?php echo htmlspecialchars($data['nama']); ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?php echo htmlspecialchars($data['email']); ?></td>
                </tr>
                <tr>
                    <td>No. Telepon</td>
                    <td><?php echo htmlspecialchars($data['no_telepon']); ?></td>
                </tr>
                <tr>
                    <td>Tanggal Lahir</td>
                    <td><?php echo htmlspecialchars($data['tgl_lahir']); ?></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td><?php echo htmlspecialchars($data['alamat']); ?></td>
                </tr>
                <tr>
                    <td>Jurusan</td>
                    <td><?php echo htmlspecialchars($data['jurusan']); ?></td>
                </tr>
                <tr>
                    <td>Alasan</td>
                    <td><?php echo htmlspecialchars($data['alasan']); ?></td>
                </tr>
            </table>
        </div>

        <div class="card-actions">
            <button class="btn-cetak" onclick="window.print()">
                🖨️ Cetak Pendaftaran
            </button>
            <a href="Home.php" class="btn-kembali">
                ← Kembali
            </a>
        </div>

    </div>

    <?php endwhile; ?>

</body>
</html>