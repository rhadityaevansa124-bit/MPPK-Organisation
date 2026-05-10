<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("location:../login.php");
    exit();
}

include "../koneksi/koneksi.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style/admin.css">
</head>
<body>
    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="galerry_manage.php">Kelola Foto</a>
        <a href="pendaftaran.php">ACC Pendaftaran</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <h1>Dashboard Admin</h1>
        
        <div class="stats">
            <?php
            $total_foto = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_galerry");
            $foto = mysqli_fetch_array($total_foto);
            
            $total_pendaftar = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_daftar");
            $pendaftar = mysqli_fetch_array($total_pendaftar);
            
            $belum_acc = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM tb_daftar WHERE status='pending'");
            $acc_data = mysqli_fetch_array($belum_acc);
            ?>
            
            <div class="stat-card">
                <h3>Total Foto</h3>
                <p class="stat-number"><?= $foto['count'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Pendaftar</h3>
                <p class="stat-number"><?= $pendaftar['count'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Belum di-ACC</h3>
                <p class="stat-number"><?= $acc_data['count'] ?></p>
            </div>
        </div>

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
            $no = 1;
            $data = mysqli_query($koneksi, "SELECT * FROM tb_daftar ORDER BY id DESC LIMIT 5");
            while ($d = mysqli_fetch_array($data)) {
                $status_class = $d['status'] == 'accepted' ? 'accepted' : 'pending';
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($d['nama']) ?></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><span class="status <?= $status_class ?>"><?= ucfirst($d['status']) ?></span></td>
                <td>
                    <a href="pendaftaran.php" class="btn-link">Lihat</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
