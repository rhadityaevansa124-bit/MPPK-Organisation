<?php
session_start();
include '../koneksi/koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit();
}

$id = intval($_SESSION['user_id']);

// Ambil data user
$query = mysqli_query($koneksi, "SELECT username FROM tb_login WHERE id = $id");
$user = mysqli_fetch_assoc($query);

// Proses update
if (isset($_POST['update'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);

    mysqli_query($koneksi, "UPDATE tb_login SET username='$username' WHERE id=$id");

    $_SESSION['username'] = $username;

    echo "<script>alert('Profile berhasil diupdate');location='Home.php';</script>";
}

if ($old === $data['password']) {
    $new_plain = $_POST['new_password'];

    mysqli_query($koneksi, "UPDATE tb_login SET password='$new_plain' WHERE id=$id");

    echo "<script>alert('Password berhasil diubah');</script>";
} else {
    echo "<script>alert('Password lama salah');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
</head>
<body>

<h2>Edit Profile</h2>

<form method="post">
    <label>Username</label><br>
    <input type="text" name="username" value="<?= $user['username'] ?>" required><br><br>

    <button type="submit" name="update">Update</button>
</form>

<h3>Ubah Password</h3>

<form method="post">
    <input type="password" name="old_password" placeholder="Password lama" required><br><br>
    <input type="password" name="new_password" placeholder="Password baru" required><br><br>
    <button type="submit" name="change_password">Ubah Password</button>
</form>

<br>
<a href="Home.php">← Kembali</a>

</body>
</html>