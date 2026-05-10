<?php 
session_start(); 
if(!isset($_SESSION['username'])){
    header("location:../admin/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Data Siswa</title>
    <link rel="stylesheet" href="../style/tampil.css">
</head>
<body>
    <h1>Cetak Pendaftaran</h1>
    <?php
            include "../koneksi/koneksi.php";
            $tampil=mysqli_query($koneksi,"SELECT * From tb_daftar ORDER BY id DESC LIMIT 1");
            while($data=mysqli_fetch_array($tampil)){
                ?>
        <div class="bukti-card">
            <table class="bukti-table">
                <tr>
                    <td>Nama Lengkap:</td>
                    <td><?php echo $data['nama'];?></td>
                </tr>
                <tr>
                    <td>Kelas dan Jurusan:</td>
                    <td><?php echo $data['email'];?></td>
                </tr>
                <tr>
                    <td>Visi dan Misi:</td>
                    <td><?php echo $data['no_telepon'];?></td>
                </tr>
                <tr>
                    <td>Link pengumpulan cv:</td>
                    <td><?php echo $data['tgl_lahir'];?></td>
                </tr>
                <tr>
                    <td>Link pengumpulan soal essay:</td>
                    <td><?php echo $data['alamat'];?></td>
                </tr>
                <tr>
                    <td>Link pengumpulan surat izin orang tua:</td>
                    <td><?php echo $data['jurusan'];?></td>
                </tr>
                <tr>
                    <td>Link pengumpulan surat penyataan LDKS:</td>
                    <td><?php echo $data['alasan'];?></td>
                </tr>
            </table>
                
            <?php } ?>
            </tr>

            <button class="btn-cetak" onclick="window.print()">Cetak Pendaftaran</button>
        </div>
    <a href="Home.php"><button>Kembali</button></a>
</body>
</html>