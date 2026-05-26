<?php
include "../koneksi/koneksi.php";
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if (!$id || !in_array($status, ['accepted', 'rejected'])) {
    header("Location: pendaftaran.php");
    exit();
}

// Ambil data user
$q = mysqli_query($koneksi, "SELECT nama FROM tb_daftar WHERE id = $id");
$user = mysqli_fetch_assoc($q);

if (!$user) {
    exit("User tidak ditemukan");
}

// Update status
mysqli_query($koneksi, "UPDATE tb_daftar SET status='$status' WHERE id=$id");

// 🔥 BUAT NOTIF
$message = ($status == 'accepted')
    ? "Halo {$user['nama']}, pendaftaran kamu DITERIMA"
    : "Halo {$user['nama']}, pendaftaran kamu DITOLAK";

// 🔥 INSERT NOTIF
mysqli_query($koneksi, "
    INSERT INTO tb_notifications (recipient_username, message, is_read)
    VALUES ('{$user['nama']}', '$message', 0)
");

// redirect
header("Location: pendaftaran.php");