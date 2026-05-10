<?php
session_start(); 
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("location:../login.php");
    exit();
}

include '../koneksi/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("location:daftar.php");
    exit();
}

$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$no_telepon = isset($_POST['no_telpon']) ? trim($_POST['no_telpon']) : '';
$tgl_lahir = isset($_POST['tgl_lahir']) ? trim($_POST['tgl_lahir']) : '';
$alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
$jurusan = isset($_POST['jurusan']) ? trim($_POST['jurusan']) : '';
$alasan = isset($_POST['alasan']) ? trim($_POST['alasan']) : '';

if (empty($nama) || empty($email) || empty($no_telepon) || empty($tgl_lahir) || empty($alamat) || empty($jurusan) || empty($alasan)) {
    echo "<script>alert('Semua field harus diisi!');history.back();</script>";
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Format email tidak valid!');history.back();</script>";
    exit();
}

$check_email = mysqli_query($koneksi, "SELECT email FROM tb_daftar WHERE email='$email' LIMIT 1");
if (mysqli_num_rows($check_email) > 0) {
    echo "<script>alert('Email sudah terdaftar!');history.back();</script>";
    exit();
}

$stmt = $koneksi->prepare("INSERT INTO tb_daftar (nama, email, no_telepon, tgl_lahir, alamat, jurusan, alasan, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");

if (!$stmt) {
    echo "<script>alert('Error prepare statement: " . $koneksi->error . "');history.back();</script>";
    exit();
}

$stmt->bind_param("sssssss", $nama, $email, $no_telepon, $tgl_lahir, $alamat, $jurusan, $alasan);

if ($stmt->execute()) {
    echo "<script>alert('Data berhasil disimpan!\\nMohon menunggu sampai admin mengkonfirmasi data Anda');window.location='tampildaftar.php';</script>";
} else {
    echo "<script>alert('Gagal menyimpan data: " . $stmt->error . "');history.back();</script>";
}

$stmt->close();
?>