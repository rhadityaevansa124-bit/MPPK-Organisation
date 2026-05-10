<?php
session_start();
include '../koneksi/koneksi.php';

$username = $_SESSION['username'];

$result = mysqli_query($koneksi, "
    SELECT id, message, created_at 
    FROM tb_notifications 
    WHERE recipient_username = '$username'
    ORDER BY id DESC
");

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>