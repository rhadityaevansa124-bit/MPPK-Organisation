<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../koneksi/koneksi.php';

$username = $_SESSION['username'];

// Ambil semua pendaftaran milik user berdasarkan email atau username
// Sesuaikan query ini dengan struktur tabel tb_daftar dan tb_login kamu
$query = mysqli_query($koneksi, "
    SELECT td.* 
    FROM tb_daftar td
    JOIN tb_login tl ON tl.username = '$username'
    WHERE td.email = tl.email
    ORDER BY td.id DESC
");

$pendaftaran = [];
while ($row = mysqli_fetch_assoc($query)) {
    $pendaftaran[] = $row;
}

header('Content-Type: application/json');
echo json_encode($pendaftaran);
?>