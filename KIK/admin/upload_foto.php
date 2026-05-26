<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("location:../login.php");
    exit();
}

include "../koneksi/koneksi.php";

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo "<script>alert('File tidak valid atau gagal upload');history.back();</script>";
    exit();
}

$foto = $_FILES['foto'];
$nama_file = $foto['name'];
$tmp_name = $foto['tmp_name'];
$ukuran = $foto['size'];
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if (empty($title) || empty($description)) {
    echo "<script>alert('Judul dan deskripsi tidak boleh kosong');history.back();</script>";
    exit();
}

if ($ukuran > 5 * 1024 * 1024) {
    echo "<script>alert('Ukuran file terlalu besar (max 5MB)');history.back();</script>";
    exit();
}

$allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_ext)) {
    echo "<script>alert('Tipe file tidak diizinkan. Hanya JPG, PNG, GIF, WEBP');history.back();</script>";
    exit();
}

$nama_baru = uniqid('foto_') . '.' . $ext;
$folder = "../uploads/";

if (!file_exists($folder)) {
    mkdir($folder, 0755, true);
}

if (move_uploaded_file($tmp_name, $folder . $nama_baru)) {
    $stmt = $koneksi->prepare("INSERT INTO tb_galerry (file, title, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama_baru, $title, $description);
    
    if ($stmt->execute()) {
        echo "<script>alert('Foto berhasil diupload');window.location='galerry_manage.php';</script>";
    } else {
        unlink($folder . $nama_baru);
        echo "<script>alert('Gagal menyimpan ke database: " . $stmt->error . "');history.back();</script>";
    }
    $stmt->close();
} else {
    echo "<script>alert('Gagal upload file');history.back();</script>";
}
?>