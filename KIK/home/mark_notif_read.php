<?php
include '../koneksi/koneksi.php';

$id = intval($_POST['id']);

mysqli_query($koneksi, "
    UPDATE tb_notifications 
    SET is_read = 1 
    WHERE id = $id
");
?>