<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("location:../login.php");
    exit();
}

include '../koneksi/koneksi.php';

$username = $_SESSION['username'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) die("ID pendaftaran tidak valid.");

// Pastikan data milik user yang login
$query = mysqli_query($koneksi, "
    SELECT td.*
    FROM tb_daftar td
    JOIN tb_login tl ON tl.email = td.email
    WHERE td.id = $id AND tl.username = '$username'
    LIMIT 1
");

$data = mysqli_fetch_assoc($query);
if (!$data) die("Data tidak ditemukan atau Anda tidak memiliki akses.");

// ── Generate / ambil kode resi ──
// Jika kolom kode_resi sudah ada dan sudah terisi, pakai itu
// Jika belum ada (migrasi lama), generate dari tanggal + nomor urut ID
if (!empty($data['kode_resi'])) {
    $kode_resi = $data['kode_resi'];
} else {
    // Fallback: hitung urutan ID ini dalam hari yang sama berdasarkan created_at atau id
    $tgl_daftar = !empty($data['created_at'])
        ? date('Ymd', strtotime($data['created_at']))
        : date('Ymd');

    // Nomor urut: berapa pendaftar sebelum id ini pada hari yang sama
    if (!empty($data['created_at'])) {
        $q_urut = mysqli_query($koneksi,
            "SELECT COUNT(*) AS total FROM tb_daftar
             WHERE DATE(created_at) = DATE('{$data['created_at']}')
             AND id <= {$data['id']}"
        );
    } else {
        $q_urut = mysqli_query($koneksi,
            "SELECT COUNT(*) AS total FROM tb_daftar WHERE id <= {$data['id']}"
        );
    }
    $urut_row  = mysqli_fetch_assoc($q_urut);
    $nomor_urut = intval($urut_row['total']);

    $kode_resi = 'MPPK-' . $tgl_daftar . '-' . str_pad($nomor_urut, 4, '0', STR_PAD_LEFT);

    // Simpan agar konsisten (jika kolom sudah ada)
    mysqli_query($koneksi, "UPDATE tb_daftar SET kode_resi='$kode_resi' WHERE id={$data['id']}");
}

// Format tanggal daftar untuk ditampilkan
$tgl_tampil = !empty($data['created_at'])
    ? date('d F Y, H:i', strtotime($data['created_at']))
    : '-';

// Nomor antrian dari kode resi (4 digit terakhir)
$antrian = substr($kode_resi, -4);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pendaftaran — MPPK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy:   #1a3c5e;
            --navy2:  #234f7a;
            --gold:   #c9a84c;
            --gold-l: #f0d98a;
            --bg:     #f5f0e8;
            --card:   #ffffff;
            --text:   #1a1a2e;
            --muted:  #6b7280;
            --border: #e2d9c8;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            background-image:
                radial-gradient(ellipse at 10% 20%, rgba(201,168,76,0.08) 0%, transparent 60%),
                radial-gradient(ellipse at 90% 80%, rgba(26,60,94,0.07) 0%, transparent 60%);
            min-height: 100vh;
            display: flex; flex-direction: column; align-items: center;
            padding: 48px 16px 64px;
        }

        /* ── Header ── */
        .page-header { text-align: center; margin-bottom: 36px; animation: fadeDown 0.6s ease both; }
        .page-header .label {
            font-size: 11px; font-weight: 500; letter-spacing: 0.22em;
            text-transform: uppercase; color: var(--gold); margin-bottom: 10px;
        }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 5vw, 2.8rem); color: var(--navy);
        }
        .page-header h1::after {
            content: ''; display: block; height: 3px; width: 60%;
            background: linear-gradient(90deg, var(--gold), transparent);
            margin: 10px auto 0; border-radius: 2px;
        }

        /* ── Card ── */
        .bukti-card {
            background: var(--card); border-radius: 20px;
            box-shadow: 0 8px 40px rgba(26,60,94,0.12);
            max-width: 640px; width: 100%; overflow: hidden;
            animation: fadeUp 0.7s ease 0.15s both;
        }

        /* Banner */
        .card-banner {
            background: linear-gradient(135deg, var(--navy), var(--navy2));
            padding: 28px 36px 24px;
            display: flex; align-items: center; gap: 16px;
        }
        .banner-icon {
            width: 52px; height: 52px;
            background: rgba(201,168,76,0.2);
            border: 1.5px solid rgba(201,168,76,0.5);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; flex-shrink: 0;
        }
        .banner-text span {
            display: block; font-size: 11px; letter-spacing: 0.18em;
            text-transform: uppercase; color: rgba(240,217,138,0.7); margin-bottom: 4px;
        }
        .banner-text strong { font-family: 'Playfair Display', serif; font-size: 1.3rem; color: #fff; }

        /* ══ KODE RESI ══ */
        .resi-section {
            margin: 0 36px;
            padding: 20px 0 0;
        }

        .resi-box {
            background: linear-gradient(135deg, #fdf8ef, #fff8e6);
            border: 2px solid var(--gold);
            border-radius: 16px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }
        .resi-box::before {
            content: '';
            position: absolute; top: 0; left: 0;
            width: 6px; height: 100%;
            background: linear-gradient(180deg, var(--gold), #a8873a);
            border-radius: 16px 0 0 16px;
        }

        .resi-left { padding-left: 8px; }
        .resi-label {
            font-size: 10px; font-weight: 600; letter-spacing: 0.2em;
            text-transform: uppercase; color: var(--muted); margin-bottom: 6px;
        }
        .resi-code {
            font-family: 'Courier New', monospace;
            font-size: 1.55rem; font-weight: 700;
            color: var(--navy); letter-spacing: 0.08em;
        }
        .resi-sub {
            font-size: 0.78rem; color: var(--muted); margin-top: 4px;
        }

        .resi-right { text-align: center; flex-shrink: 0; }
        .antrian-label {
            font-size: 10px; font-weight: 600; letter-spacing: 0.15em;
            text-transform: uppercase; color: var(--muted); margin-bottom: 4px;
        }
        .antrian-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem; font-weight: 700;
            color: var(--gold); line-height: 1;
        }
        .antrian-sub { font-size: 0.72rem; color: var(--muted); margin-top: 2px; }

        /* Barcode visual dummy */
        .resi-barcode {
            display: flex; align-items: flex-end; gap: 2px;
            margin-top: 10px;
        }
        .resi-barcode span {
            background: var(--navy);
            border-radius: 1px;
            width: 3px;
            opacity: 0.6;
        }

        /* Tabel info */
        .bukti-table-wrapper { padding: 24px 36px; }
        .section-label {
            font-size: 10px; font-weight: 600; letter-spacing: 0.18em;
            text-transform: uppercase; color: var(--muted);
            margin-bottom: 14px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-label::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }

        .bukti-table { width: 100%; border-collapse: collapse; }
        .bukti-table tr { border-bottom: 1px solid var(--border); transition: background 0.2s; }
        .bukti-table tr:last-child { border-bottom: none; }
        .bukti-table tr:hover { background: rgba(201,168,76,0.03); }
        .bukti-table td { padding: 13px 8px; vertical-align: top; }
        .bukti-table td:first-child {
            color: var(--muted); font-weight: 500; width: 38%; font-size: 0.82rem; padding-top: 15px;
        }
        .bukti-table td:last-child { color: var(--text); font-size: 0.9rem; line-height: 1.6; }

        /* Status badge */
        .status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 20px;
            font-size: 0.8rem; font-weight: 600;
        }
        .status-pending  { background: #fff8e1; color: #b45309; border: 1px solid #fde68a; }
        .status-accepted { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
        .status-rejected { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }

        /* Tanggal daftar */
        .tgl-daftar {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.82rem; color: var(--muted);
            background: #f5f0e8; padding: 5px 12px; border-radius: 8px;
        }

        /* Actions */
        .card-actions { padding: 0 36px 32px; display: flex; gap: 12px; flex-wrap: wrap; }
        .btn-cetak {
            flex: 1; min-width: 140px;
            background: linear-gradient(135deg, var(--gold), #a8873a);
            color: #fff; border: none; border-radius: 10px;
            padding: 13px 24px; font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem; font-weight: 500; letter-spacing: 0.05em;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 16px rgba(201,168,76,0.35);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-cetak:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.45); }
        .btn-kembali {
            flex: 1; min-width: 140px;
            background: transparent; color: var(--navy);
            border: 1.5px solid var(--border); border-radius: 10px;
            padding: 13px 24px; font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem; font-weight: 500; letter-spacing: 0.05em;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
            text-decoration: none; transition: border-color 0.2s, background 0.2s;
        }
        .btn-kembali:hover { border-color: var(--navy); background: rgba(26,60,94,0.04); }

        /* Animations */
        /* ── Banner Pemberitahuan ── */
        .info-banner {
            margin: 0 36px 20px;
            background: linear-gradient(135deg, #fef9ec, #fef3c7);
            border: 1.5px solid rgba(201,168,76,0.5);
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            position: relative;
            overflow: hidden;
        }

        .info-banner::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
            background: linear-gradient(180deg, #c9a84c, #a8873a);
            border-radius: 14px 0 0 14px;
        }

        .info-banner-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #c9a84c, #a8873a);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .info-banner-body {}

        .info-banner-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 5px;
        }

        .info-banner-text {
            font-size: 0.88rem;
            color: #78350f;
            line-height: 1.55;
            font-weight: 500;
        }

        .info-banner-text strong {
            color: #92400e;
            font-weight: 700;
        }

        @media print {
            /* Banner tetap tampil saat dicetak */
            .info-banner {
                border: 1.5px solid #c9a84c !important;
                background: #fef9ec !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .info-banner-icon {
                background: #c9a84c !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Print */
        @media print {
            body { background: #fff; padding: 0; }
            .card-actions { display: none; }
            .bukti-card { box-shadow: none; border: 1px solid #ddd; border-radius: 0; max-width: 100%; }
            .card-banner, .resi-barcode span {
                -webkit-print-color-adjust: exact; print-color-adjust: exact;
            }
            .card-banner { background: var(--navy) !important; }
            .resi-box { border: 2px solid var(--gold) !important; }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <p class="label">MPPK SMKN 1 Cibinong</p>
        <h1>Bukti Pendaftaran</h1>
    </div>

    <div class="bukti-card">

        <!-- Banner -->
        <div class="card-banner">
            <div class="banner-icon">🎓</div>
            <div class="banner-text">
                <span>Bukti Pendaftaran Resmi</span>
                <strong><?= htmlspecialchars($data['nama']) ?></strong>
            </div>
        </div>

        <!-- Kode Resi -->
        <div class="resi-section">
            <div class="resi-box">
                <div class="resi-left">
                    <div class="resi-label">Kode Resi / Nomor Pendaftaran</div>
                    <div class="resi-code"><?= htmlspecialchars($kode_resi) ?></div>
                    <div class="resi-sub">Terdaftar: <?= $tgl_tampil ?></div>
                    <!-- Barcode visual -->
                    <div class="resi-barcode">
                        <?php
                        // Generate barcode visual dari karakter kode resi
                        $heights = [18,10,22,8,16,12,20,6,14,18,10,24,8,16,12,20,6,14,18,10];
                        foreach ($heights as $h) {
                            echo "<span style='height:{$h}px;'></span>";
                        }
                        ?>
                    </div>
                </div>
                <div class="resi-right">
                    <div class="antrian-label">No. Antrian</div>
                    <div class="antrian-number"><?= ltrim($antrian, '0') ?: '1' ?></div>
                    <div class="antrian-sub">Pendaftar ke-<?= intval($antrian) ?> hari ini</div>
                </div>
            </div>
        </div>

        <!-- Data Pendaftar -->
        <div class="bukti-table-wrapper">
            <div class="section-label">Data Pendaftar</div>
            <table class="bukti-table">
                <tr>
                    <td>Nama Lengkap</td>
                    <td><?= htmlspecialchars($data['nama']) ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?= htmlspecialchars($data['email']) ?></td>
                </tr>
                <tr>
                    <td>No. Telepon</td>
                    <td><?= htmlspecialchars($data['no_telepon']) ?></td>
                </tr>
                <tr>
                    <td>Tanggal Lahir</td>
                    <td><?= htmlspecialchars($data['tgl_lahir']) ?></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td><?= htmlspecialchars($data['alamat']) ?></td>
                </tr>
                <tr>
                    <td>Jurusan</td>
                    <td><?= htmlspecialchars($data['jurusan']) ?></td>
                </tr>
                <tr>
                    <td>Alasan</td>
                    <td><?= htmlspecialchars($data['alasan']) ?></td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <?php
                        $st = $data['status'] ?? 'pending';
                        $icon = $st === 'accepted' ? '✅' : ($st === 'rejected' ? '❌' : '⏳');
                        $label = $st === 'accepted' ? 'Diterima' : ($st === 'rejected' ? 'Ditolak' : 'Menunggu Review');
                        echo "<span class='status-badge status-{$st}'>{$icon} {$label}</span>";
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Tanggal Daftar</td>
                    <td><span class="tgl-daftar">📅 <?= $tgl_tampil ?></span></td>
                </tr>
            </table>
        </div>

        <!-- Banner Pemberitahuan Permanen -->
        <div class="info-banner">
            <div class="info-banner-icon">📌</div>
            <div class="info-banner-body">
                <div class="info-banner-title">Pemberitahuan Penting</div>
                <div class="info-banner-text">
                    Silahkan bawa bukti pendaftaran ini untuk
                    <strong>tes offline</strong> di
                    <strong>Sekretariat MPPK</strong>
                    SMKN 1 Cibinong.
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="card-actions">
            <button class="btn-cetak" onclick="window.print()">🖨️ Cetak Bukti</button>
            <a href="Home.php" class="btn-kembali">← Kembali</a>
        </div>

    </div>
</body>
</html>