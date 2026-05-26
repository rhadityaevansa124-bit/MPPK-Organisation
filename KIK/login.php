<?php
session_start();

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("location:admin/dashboard.php");
    } else {
        header("location:home/Home.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'koneksi/koneksi.php';
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        $stmt = $koneksi->prepare("SELECT id, username, password, role FROM tb_login WHERE username = ? LIMIT 1");
        
        if (!$stmt) {
            $error = "Database error: " . $koneksi->error;
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_array();
            $stmt->close();
            
            // Catatan: Disarankan menggunakan password_hash() dan password_verify() untuk keamanan
            if ($user && $password === $user['password']) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] == 'user') {
                    $stmt_email = $koneksi->prepare("SELECT email FROM tb_daftar WHERE nama = ? LIMIT 1");
                    $stmt_email->bind_param("s", $username);
                    $stmt_email->execute();
                    $res_email = $stmt_email->get_result();
                    if ($row_email = $res_email->fetch_assoc()) {
                        $_SESSION['email'] = $row_email['email'];
                    }
                    $stmt_email->close();
                }
                
                header("location:" . ($user['role'] == 'admin' ? "admin/dashboard.php" : "home/Home.php"));
                exit();
            } else {
                $error = "Username atau password salah!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MPPK SMKN 1 Cibinong</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: url('ya.png') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.9); /* Sedikit transparan */
            backdrop-filter: blur(10px); /* Efek blur kaca */
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 26px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .login-header p {
            color: #777;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #444; /* Diubah dari putih ke abu gelap agar terbaca */
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            background: #f9f9f9;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #00d9ff;
            background: #fff;
            box-shadow: 0 0 8px rgba(0, 217, 255, 0.2);
        }

        .error {
            background: #ffe0e0;
            color: #d8000c;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 5px solid #d8000c;
            animation: shake 0.2s ease-in-out 0s 2;
        }

        @keyframes shake {
            0% { margin-left: 0; }
            25% { margin-left: 5px; }
            75% { margin-left: -5px; }
            100% { margin-left: 0; }
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #00686b 0%, #00d9ff 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 217, 255, 0.3);
            filter: brightness(1.1);
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }

        .register-link a {
            color: #00686b;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Login MPPK</h1>
            <h3>SMK NEGERI 1 Cibinong</h3>
            <p>Masuk untuk mengakses akun anda</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Masukkan username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn-login">MASUK</button>
        </form>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar sekarang</a>
        </div>
    </div>
</body>
</html>