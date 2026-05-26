<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    echo json_encode(['status' => 'error']);
    exit();
}

include '../koneksi/koneksi.php';

$username = $_POST['username']; // username pendaftar
$status   = $_POST['status'];   // 'diterima' atau 'ditolak'

if ($status == 'diterima') {
    $message = "Selamat! Pendaftaran kamu telah DITERIMA oleh admin. 🎉";
} else {
    $message = "Mohon maaf, pendaftaran kamu DITOLAK oleh admin.";
}

$stmt = $koneksi->prepare("INSERT INTO tb_notifikasi (username, message) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $message);
$stmt->execute();
$stmt->close();

echo json_encode(['status' => 'ok']);
?>