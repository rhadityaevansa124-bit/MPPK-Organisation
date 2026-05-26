<?php
include "../koneksi/koneksi.php";
session_start();
if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location:pendaftaran.php");
    exit();
}

$id = intval($_GET['id']);
$data = mysqli_query($koneksi, "SELECT * FROM tb_daftar WHERE id = $id");
$d = mysqli_fetch_array($data);

if (!$d) {
    echo "<script>alert('Data tidak ditemukan');window.location='pendaftaran.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Pendaftar</title>
    <link rel="stylesheet" href="../style/admin.css">
    <style>
        .detail-container {
            max-width: 600px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .detail-item {
            margin-bottom: 15px;
        }
        .detail-item label {
            font-weight: 600;
            color: #0b57a4;
            display: block;
            margin-bottom: 5px;
        }
        .detail-item p {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            margin: 0;
        }
        .btn-group {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn-group a {
            flex: 1;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-back {
            background: #6c757d;
            color: #fff;
        }
        .btn-accept {
            background: #28a745;
            color: #fff;
        }
        .btn-reject {
            background: #dc3545;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="kelola_pengurus.php">Kelola Pengurus</a>
        <a href="galerry_manage.php">Kelola Foto</a>
        <a href="pendaftaran.php" class="active">ACC Pendaftaran</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <h1>Detail Pendaftar</h1>

        <div class="detail-container">
            <div class="detail-item">
                <label>Nama Lengkap</label>
                <p><?= htmlspecialchars($d['nama']) ?></p>
            </div>

            <div class="detail-item">
                <label>Email</label>
                <p><?= htmlspecialchars($d['email']) ?></p>
            </div>

            <div class="detail-item">
                <label>No Telepon</label>
                <p><?= htmlspecialchars($d['no_telepon']) ?></p>
            </div>

            <div class="detail-item">
                <label>Tanggal Lahir</label>
                <p><?= htmlspecialchars($d['tgl_lahir']) ?></p>
            </div>

            <div class="detail-item">
                <label>Alamat</label>
                <p><?= htmlspecialchars($d['alamat']) ?></p>
            </div>

            <div class="detail-item">
                <label>Jurusan</label>
                <p><?= htmlspecialchars($d['jurusan']) ?></p>
            </div>

            <div class="detail-item">
                <label>Alasan Daftar</label>
                <p><?= htmlspecialchars($d['alasan']) ?></p>
            </div>

            <div class="detail-item">
                <label>Status</label>
                <p><?= ucfirst($d['status']) ?></p>
            </div>

            <div class="btn-group">
                <a href="pendaftaran.php" class="btn-back">Kembali</a>
                <?php if ($d['status'] == 'pending') { ?>
                    <a href="acc_pendaftar.php?id=<?= $d['id'] ?>&status=accepted" class="btn-accept" onclick="return confirm('Terima pendaftar ini?')">Terima</a>
                    <a href="acc_pendaftar.php?id=<?= $d['id'] ?>&status=rejected" class="btn-reject" onclick="return confirm('Tolak pendaftar ini?')">Tolak</a>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>