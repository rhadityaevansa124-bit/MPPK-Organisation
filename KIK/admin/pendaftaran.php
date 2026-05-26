<?php
include "../koneksi/koneksi.php";
session_start();
if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ACC Pendaftaran</title>
    <link rel="stylesheet" href="../style/admin.css">
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
        <h1>ACC Pendaftaran</h1>

        <div class="filter-section">
            <a href="pendaftaran.php" class="filter-btn active">Semua</a>
            <a href="pendaftaran.php?status=pending" class="filter-btn">Pending</a>
            <a href="pendaftaran.php?status=accepted" class="filter-btn">Diterima</a>
            <a href="pendaftaran.php?status=rejected" class="filter-btn">Ditolak</a>
        </div>

        <table>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No Telepon</th>
                <th>Jurusan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php
            $status_filter = $_GET['status'] ?? '';
            $query = "SELECT * FROM tb_daftar";
            
            if ($status_filter) {
                $status_filter = mysqli_real_escape_string($koneksi, $status_filter);
                $query .= " WHERE status = '$status_filter'";
            }
            
            $query .= " ORDER BY id DESC";
            $data = mysqli_query($koneksi, $query);
            $no = 1;
            
            while ($d = mysqli_fetch_array($data)) {
                $status_class = $d['status'] == 'accepted' ? 'accepted' : ($d['status'] == 'rejected' ? 'rejected' : 'pending');
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($d['nama']) ?></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><?= htmlspecialchars($d['no_telepon']) ?></td>
                <td><?= htmlspecialchars($d['jurusan']) ?></td>
                <td><span class="status <?= $status_class ?>"><?= ucfirst($d['status']) ?></span></td>
                <td>
                    <a href="detail_pendaftar.php?id=<?= $d['id'] ?>" class="btn-link">Lihat</a>
                    <?php if ($d['status'] == 'pending') { ?>
                        <a href="acc_pendaftar.php?id=<?= $d['id'] ?>&status=accepted" class="btn-accept" onclick="return confirm('Terima pendaftar ini?')">Terima</a>
                        <a href="acc_pendaftar.php?id=<?= $d['id'] ?>&status=rejected" class="btn-reject" onclick="return confirm('Tolak pendaftar ini?')">Tolak</a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
