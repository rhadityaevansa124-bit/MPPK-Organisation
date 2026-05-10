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
    <title>Document</title>
    <link rel="stylesheet" href="../style/Crud.css">
</head>
<body>
    <form method="post" action="simpan.php" id="daftarForm">
        <h2><center>Formulir Pendaftaran</center></h2><br>
        <label for="nama">Nama Lengkap </label>
        <input type="text" name="nama" size="10" maxlength="50" required><br>
        <label for="email">Email </label>
        <input type="text" name="email" size="10" maxlength="50" required><br>
        <label for="no_telpon">no telepon </label>
        <input type="text" name="no_telpon" size="10" min="50" required><br>
        <label for="tgl_lahir">tanggal lahir </label>
        <input type="date" name="tgl_lahir" size="10" maxlength="50" required><br>
        <label for="alamat">alamat </label>
        <input type="text" name="alamat" size="10" maxlength="500" required><br>
        <label for="jurusan">jurusan </label>
        <input type="text" name="jurusan" size="10" maxlength="50" required><br>
        <label for="alasan">alasan masuk MPPK </label>
        <input type="text" name="alasan" size="10" maxlength="50" required><br>
        <input type="submit" name="simpan" value="simpan">
        <input type="reset" value="Batal">
    </form>

<script>
    // konfirmasi sebelum submit
    document.getElementById('daftarForm').addEventListener('submit', function(e){
        const ok = confirm('Yakin ingin menyimpan data?');
        if(!ok){
            e.preventDefault();
        }
    });
</script>
</body>
</html>