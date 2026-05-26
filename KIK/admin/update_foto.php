<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include "../koneksi/koneksi.php";

if (!isset($_POST['id'])) {
    echo "<script>alert('ID tidak ditemukan');history.back();</script>";
    exit();
}

$id = intval($_POST['id']);
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if (empty($title) || empty($description)) {
    echo "<script>alert('Judul dan deskripsi tidak boleh kosong');history.back();</script>";
    exit();
}

$stmt_check = $koneksi->prepare("SELECT file FROM tb_galerry WHERE id = ?");
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();

if ($res_check->num_rows == 0) {
    echo "<script>alert('Foto tidak ditemukan');history.back();</script>";
    $stmt_check->close();
    exit();
}

$row_check = $res_check->fetch_assoc();
$old_file = $row_check['file'];
$stmt_check->close();

$new_file = $old_file;

// Jika ada file baru
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $foto = $_FILES['foto'];
    $nama_file = $foto['name'];
    $tmp_name = $foto['tmp_name'];
    $ukuran = $foto['size'];

    if ($ukuran > 5 * 1024 * 1024) {
        echo "<script>alert('Ukuran file terlalu besar (max 5MB)');history.back();</script>";
        exit();
    }

    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        echo "<script>alert('Tipe file tidak diizinkan');history.back();</script>";
        exit();
    }

    $new_file = uniqid('foto_') . '.' . $ext;
    $folder = "../uploads/";

    if (move_uploaded_file($tmp_name, $folder . $new_file)) {
        $old_path = $folder . $old_file;
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    } else {
        echo "<script>alert('Gagal upload file');history.back();</script>";
        exit();
    }
}

// Update database
$stmt = $koneksi->prepare("UPDATE tb_galerry SET file = ?, title = ?, description = ? WHERE id = ?");
$stmt->bind_param("sssi", $new_file, $title, $description, $id);

if ($stmt->execute()) {
    echo "<script>alert('Foto berhasil diperbarui');window.location='galerry_manage.php';</script>";
} else {
    if ($new_file !== $old_file) {
        $new_path = "../uploads/" . $new_file;
        if (file_exists($new_path)) {
            unlink($new_path);
        }
    }
    echo "<script>alert('Gagal memperbarui database');history.back();</script>";
}

$stmt->close();
?>