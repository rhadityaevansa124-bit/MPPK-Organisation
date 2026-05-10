<?php
session_start();
if (isset($_SESSION['username']) && $_SESSION['role'] == 'user') {
    header("location:Home.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../koneksi/koneksi.php';
    
    // Validasi input
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        // Gunakan prepared statement untuk prevent SQL Injection
        $stmt = $koneksi->prepare("SELECT * FROM tb_login WHERE username = ? AND role = 'user' LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_array();
        $stmt->close();
        
        if ($user && $password === $user['password']) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'user';
            
            header("location:Home.php");
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login User - MPPK</title>
    
</head>
<body>
    <div class="login-form">
        <h1>Login Anggota MPPK</h1>
        <p>Masuk dengan username dan password Anda</p>

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