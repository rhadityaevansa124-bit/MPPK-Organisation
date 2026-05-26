<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("location:../login.php");
    exit();
}

include "../koneksi/koneksi.php";

// ══ Handle toggle pendaftaran (AJAX dari halaman ini sendiri) ══
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    header('Content-Type: application/json');
    $aksi = trim($_POST['aksi']);
    if (!in_array($aksi, ['buka', 'tutup'])) {
        echo json_encode(['status' => 'error', 'msg' => 'Aksi tidak valid: ' . $aksi]);
        exit();
    }
    $admin = $_SESSION['username'];
    $cek   = mysqli_query($koneksi, "SELECT id FROM tb_pengaturan WHERE kunci='status_pendaftaran' LIMIT 1");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "UPDATE tb_pengaturan SET nilai='$aksi', updated_by='$admin', updated_at=NOW() WHERE kunci='status_pendaftaran'");
    } else {
        mysqli_query($koneksi, "INSERT INTO tb_pengaturan (kunci, nilai, updated_by) VALUES ('status_pendaftaran', '$aksi', '$admin')");
    }
    echo json_encode([
        'status' => 'ok',
        'nilai'  => $aksi,
        'msg'    => $aksi === 'buka' ? 'Pendaftaran berhasil dibuka' : 'Pendaftaran berhasil ditutup'
    ]);
    exit();
}

// Ambil status pendaftaran saat ini
$q_status = mysqli_query($koneksi, "SELECT nilai, updated_by, updated_at FROM tb_pengaturan WHERE kunci='status_pendaftaran' LIMIT 1");
$row_status   = $q_status ? mysqli_fetch_assoc($q_status) : null;
$status_daftar = $row_status['nilai']      ?? 'buka';
$updated_by    = $row_status['updated_by'] ?? '-';
$updated_at    = $row_status['updated_at'] ?? '-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style/admin.css">
    <style>
        /* ── Widget Status Pendaftaran ── */
        .pendaftaran-widget {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 24px 28px;
            margin-bottom: 28px;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .widget-left { display: flex; align-items: center; gap: 16px; }

        .widget-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; flex-shrink: 0;
        }
        .widget-icon.buka  { background: #f0fdf4; }
        .widget-icon.tutup { background: #fef2f2; }

        .widget-info h3 {
            font-size: 1rem; font-weight: 700;
            color: #1a1a2e; margin: 0 0 4px;
        }

        .widget-info .status-text {
            font-size: 0.88rem; color: #6b7280;
        }

        .badge-status {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px;
            font-size: 0.8rem; font-weight: 700;
            letter-spacing: 0.05em;
        }
        .badge-status.buka  { background: #dcfce7; color: #15803d; }
        .badge-status.tutup { background: #fee2e2; color: #b91c1c; }

        .badge-status .dot {
            width: 7px; height: 7px; border-radius: 50%;
        }
        .badge-status.buka  .dot { background: #16a34a; animation: pulse-green 1.5s infinite; }
        .badge-status.tutup .dot { background: #dc2626; }

        @keyframes pulse-green {
            0%,100% { box-shadow: 0 0 0 0 rgba(22,163,74,0.4); }
            50%      { box-shadow: 0 0 0 5px rgba(22,163,74,0); }
        }

        .widget-meta {
            font-size: 11px; color: #9ca3af; margin-top: 4px;
        }

        /* Toggle Switch */
        .toggle-wrap { display: flex; align-items: center; gap: 14px; }

        .toggle-label {
            font-size: 0.88rem; color: #6b7280; font-weight: 500;
        }

        .toggle-switch {
            position: relative; width: 64px; height: 32px;
            cursor: pointer;
        }

        .toggle-switch input { display: none; }

        .toggle-track {
            width: 64px; height: 32px;
            border-radius: 16px;
            background: #e5e7eb;
            transition: background 0.3s;
            position: relative;
        }

        .toggle-track::after {
            content: '';
            position: absolute;
            top: 4px; left: 4px;
            width: 24px; height: 24px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }

        .toggle-switch input:checked + .toggle-track {
            background: #22c55e;
        }

        .toggle-switch input:checked + .toggle-track::after {
            transform: translateX(32px);
        }

        .btn-toggle {
            padding: 10px 22px;
            border: none; border-radius: 10px;
            font-size: 0.88rem; font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.2s;
        }
        .btn-toggle:hover { transform: translateY(-1px); }
        .btn-toggle:active { transform: translateY(0); }

        .btn-toggle.buka-btn {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff;
            box-shadow: 0 4px 14px rgba(22,163,74,0.3);
        }
        .btn-toggle.tutup-btn {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            box-shadow: 0 4px 14px rgba(220,38,38,0.3);
        }
        .btn-toggle:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* Toast */
        .dash-toast {
            position: fixed; top: 20px; right: 20px;
            background: #fff; border-radius: 12px;
            padding: 14px 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.14);
            display: flex; align-items: center; gap: 10px;
            font-size: 0.88rem; font-weight: 500;
            z-index: 9999; min-width: 260px;
            transform: translateX(120%); opacity: 0;
            transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s;
            border-left: 4px solid #22c55e;
        }
        .dash-toast.show { transform: translateX(0); opacity: 1; }
        .dash-toast.error { border-left-color: #ef4444; }

        /* Konfirmasi Modal */
        .confirm-modal {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.45); z-index: 3000;
            align-items: center; justify-content: center;
        }
        .confirm-modal.open { display: flex; }
        .confirm-box {
            background: #fff; border-radius: 16px;
            padding: 28px 24px; max-width: 360px; width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            animation: popIn 0.3s ease;
        }
        @keyframes popIn { from{opacity:0;transform:scale(0.9)} to{opacity:1;transform:scale(1)} }
        .confirm-box .c-icon { font-size: 40px; margin-bottom: 12px; }
        .confirm-box h3 { font-size: 1.1rem; color: #1a1a2e; margin-bottom: 8px; }
        .confirm-box p  { font-size: 0.85rem; color: #6b7280; margin-bottom: 20px; line-height: 1.5; }
        .confirm-btns { display: flex; gap: 10px; }
        .confirm-btns button {
            flex: 1; padding: 10px; border: none; border-radius: 8px;
            font-size: 0.88rem; font-weight: 600; cursor: pointer;
        }
        .c-cancel { background: #f3f4f6; color: #374151; }
        .c-ok     { background: linear-gradient(135deg, #1a3c5e, #234f7a); color: #fff; }
        .c-ok.merah { background: linear-gradient(135deg, #dc2626, #b91c1c); }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="kelola_pengurus.php">Kelola Pengurus</a>
        <a href="galerry_manage.php">Kelola Foto</a>
        <a href="pendaftaran.php">ACC Pendaftaran</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Toast -->
    <div class="dash-toast" id="dashToast">
        <span id="dashToastIcon">✅</span>
        <span id="dashToastMsg"></span>
    </div>

    <!-- Modal Konfirmasi -->
    <div class="confirm-modal" id="confirmModal">
        <div class="confirm-box">
            <div class="c-icon" id="confirmIcon">❓</div>
            <h3 id="confirmTitle">Konfirmasi</h3>
            <p id="confirmDesc">Yakin?</p>
            <div class="confirm-btns">
                <button class="c-cancel" onclick="aksiPending=null;tutupConfirm()">Batal</button>
                <button class="c-ok" id="confirmOk" onclick="eksekusiToggle()">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <div class="content">
        <h1>Dashboard Admin</h1>

        <!-- ══ WIDGET STATUS PENDAFTARAN ══ -->
        <div class="pendaftaran-widget" id="widgetPendaftaran">
            <div class="widget-left">
                <div class="widget-icon <?= $status_daftar ?>" id="widgetIcon">
                    <?= $status_daftar === 'buka' ? '🟢' : '🔴' ?>
                </div>
                <div class="widget-info">
                    <h3>Status Pendaftaran</h3>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <span class="badge-status <?= $status_daftar ?>" id="badgeStatus">
                            <span class="dot"></span>
                            <?= $status_daftar === 'buka' ? 'TERBUKA' : 'DITUTUP' ?>
                        </span>
                    </div>
                    <div class="widget-meta" id="widgetMeta">
                        Terakhir diubah oleh <strong><?= htmlspecialchars($updated_by) ?></strong>
                        <?= $updated_at !== '-' ? 'pada ' . date('d M Y, H:i', strtotime($updated_at)) : '' ?>
                    </div>
                </div>
            </div>

            <div class="toggle-wrap">
                <span class="toggle-label" id="toggleLabel">
                    <?= $status_daftar === 'buka' ? 'Tutup Pendaftaran' : 'Buka Pendaftaran' ?>
                </span>
                <button
                    class="btn-toggle <?= $status_daftar === 'buka' ? 'tutup-btn' : 'buka-btn' ?>"
                    id="btnToggle"
                    onclick="bukaMKonfirmasi('<?= $status_daftar ?>')"
                >
                    <?= $status_daftar === 'buka' ? '🔒 Tutup' : '🔓 Buka' ?>
                </button>
            </div>
        </div>

        <!-- ══ STATISTIK ══ -->
        <div class="stats">
            <?php
            $total_foto      = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_galerry"));
            $total_pendaftar = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_daftar"));
            $belum_acc       = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_daftar WHERE status='pending'"));
            $diterima        = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_daftar WHERE status='accepted'"));
            ?>
            <div class="stat-card">
                <h3>Total Foto</h3>
                <p class="stat-number"><?= $total_foto['count'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Pendaftar</h3>
                <p class="stat-number"><?= $total_pendaftar['count'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Menunggu ACC</h3>
                <p class="stat-number"><?= $belum_acc['count'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Diterima</h3>
                <p class="stat-number"><?= $diterima['count'] ?></p>
            </div>
        </div>

        <!-- ══ TABEL PENDAFTAR TERBARU ══ -->
        <h2>Pendaftar Terbaru</h2>
        <table>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php
            $no   = 1;
            $data = mysqli_query($koneksi, "SELECT * FROM tb_daftar ORDER BY id DESC LIMIT 5");
            while ($d = mysqli_fetch_array($data)):
                $sc = $d['status'] === 'accepted' ? 'accepted' : ($d['status'] === 'rejected' ? 'rejected' : 'pending');
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($d['nama']) ?></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><span class="status <?= $sc ?>"><?= ucfirst($d['status']) ?></span></td>
                <td><a href="pendaftaran.php" class="btn-link">Lihat</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>
    let aksiPending = null;

    function bukaMKonfirmasi(statusSaatIni) {
        aksiPending = statusSaatIni === 'buka' ? 'tutup' : 'buka';

        const icon  = document.getElementById('confirmIcon');
        const title = document.getElementById('confirmTitle');
        const desc  = document.getElementById('confirmDesc');
        const okBtn = document.getElementById('confirmOk');

        if (aksiPending === 'tutup') {
            icon.textContent  = '🔒';
            title.textContent = 'Tutup Pendaftaran?';
            desc.textContent  = 'Pendaftar tidak bisa mengisi formulir saat pendaftaran ditutup. Kamu bisa membukanya kembali kapan saja.';
            okBtn.textContent = 'Ya, Tutup Sekarang';
            okBtn.className   = 'c-ok merah';
        } else {
            icon.textContent  = '🔓';
            title.textContent = 'Buka Pendaftaran?';
            desc.textContent  = 'Pendaftar akan bisa mengisi formulir pendaftaran kembali setelah ini.';
            okBtn.textContent = 'Ya, Buka Sekarang';
            okBtn.className   = 'c-ok';
        }

        document.getElementById('confirmModal').classList.add('open');
    }

    function tutupConfirm() {
        document.getElementById('confirmModal').classList.remove('open');
        // aksiPending hanya di-reset setelah dipakai di eksekusiToggle
    }

    function eksekusiToggle() {
        if (!aksiPending) return;

        // Simpan dulu sebelum tutupConfirm() me-null-kan aksiPending
        const aksi = aksiPending;
        tutupConfirm();

        const btn = document.getElementById('btnToggle');
        btn.disabled    = true;
        btn.textContent = '⏳ Memproses...';

        const formData = new FormData();
        formData.append('aksi', aksi);

        fetch('dashboard.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                updateUI(data.nilai);
                tampilToast('success', data.msg);
            } else {
                tampilToast('error', 'Gagal: ' + data.msg);
                btn.disabled = false;
            }
        })
        .catch(() => {
            tampilToast('error', 'Koneksi gagal. Coba lagi.');
            btn.disabled = false;
        });
    }

    function updateUI(nilai) {
        const isBuka = nilai === 'buka';

        // Icon widget
        const widgetIcon = document.getElementById('widgetIcon');
        widgetIcon.textContent  = isBuka ? '🟢' : '🔴';
        widgetIcon.className    = `widget-icon ${nilai}`;

        // Badge
        const badge = document.getElementById('badgeStatus');
        badge.className   = `badge-status ${nilai}`;
        badge.innerHTML   = `<span class="dot"></span>${isBuka ? 'TERBUKA' : 'DITUTUP'}`;

        // Meta
        document.getElementById('widgetMeta').innerHTML =
            `Terakhir diubah oleh <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> barusan`;

        // Tombol
        const btn   = document.getElementById('btnToggle');
        btn.className   = `btn-toggle ${isBuka ? 'tutup-btn' : 'buka-btn'}`;
        btn.innerHTML   = isBuka ? '🔒 Tutup' : '🔓 Buka';
        btn.disabled    = false;
        btn.onclick     = () => bukaMKonfirmasi(nilai);

        // Label
        document.getElementById('toggleLabel').textContent =
            isBuka ? 'Tutup Pendaftaran' : 'Buka Pendaftaran';
    }

    function tampilToast(type, msg) {
        const toast = document.getElementById('dashToast');
        const icon  = document.getElementById('dashToastIcon');
        const text  = document.getElementById('dashToastMsg');

        icon.textContent = type === 'success' ? '✅' : '❌';
        text.textContent = msg;
        toast.className  = `dash-toast ${type === 'error' ? 'error' : ''} show`;

        setTimeout(() => toast.classList.remove('show'), 3500);
    }
    </script>
</body>
</html>