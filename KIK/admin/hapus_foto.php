<?php
include "../koneksi/koneksi.php";

$id = $_GET['id'];

$data = mysqli_query($koneksi, "SELECT * FROM tb_galerry WHERE id='$id'");
$d = mysqli_fetch_array($data);
unlink("../uploads/".$d['file']); // Hapus file fisik

mysqli_query($koneksi, "DELETE FROM tb_galerry WHERE id='$id'");

header("location:galerry_manage.php");
?>
