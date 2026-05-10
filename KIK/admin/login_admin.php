<?php
session_start();
if (isset($_SESSION['username']) && $_SESSION['role'] == 'admin') {
    header("location:dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../koneksi/koneksi.php';
    
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    
    // Query admin dari tabel terpisah atau gunakan hardcoded
    $query = mysqli_query($koneksi, "SELECT * FROM tb_admin WHERE username='$username' LIMIT 1");
    $admin = mysqli_fetch_array($query);
    
    if ($admin && $admin['password'] === $password) {
        $_SESSION['username'] = $admin['username'];
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['role'] = 'admin';
        
        header("location:dashboard.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <style>
    </style>
</head>
<body>
    <div class="login-form">
        <h1>Login Admin</h1>
        <p>Masuk ke panel admin</p>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="back-link">
            <a href="../login.php">← Kembali ke halaman login</a>
        </div>
    </div>
</body>
</html>