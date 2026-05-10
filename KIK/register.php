<?php
session_start();

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] == 'admin') {
        header("location:admin/dashboard.php");
    } else {
        header("location:home/Home.php");
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'koneksi/koneksi.php';
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    
    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif (strlen($username) < 3) {
        $error = "Username minimal 3 karakter!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Cek username sudah terdaftar
        $stmt_check = $koneksi->prepare("SELECT id FROM tb_login WHERE username = ? LIMIT 1");
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        
        if ($res_check->num_rows > 0) {
            $error = "Username sudah terdaftar! Gunakan username lain.";
            $stmt_check->close();
        } else {
            $stmt_check->close();
            
            // Insert ke tb_login dengan role 'user'
            // Catatan Keamanan: Disarankan menggunakan password_hash($password, PASSWORD_DEFAULT)
            $stmt = $koneksi->prepare("INSERT INTO tb_login (username, email, password, role) VALUES (?, ?, ?, 'user')");
            
            if (!$stmt) {
                $error = "Database error: " . $koneksi->error;
            } else {
                $stmt->bind_param("sss", $username, $email, $password);
                
                if ($stmt->execute()) {
                    $success = "Akun berhasil dibuat! Silakan login.";
                    // Kosongkan post agar input di form hilang setelah sukses
                    $username = $email = "";
                } else {
                    $error = "Gagal membuat akun: " . $stmt->error;
                }
                $stmt->close();
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
    <title>Register - MPPK SMKN 1 Cibinong</title>
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
            padding: 40px 20px;
        }

        /* Overlay Background */
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .register-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .register-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .register-header h1 {
            color: #333;
            font-size: 26px;
            font-weight: 600;
        }

        .register-header p {
            color: #777;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: #444;
            font-weight: 500;
            margin-bottom: 5px;
            font-size: 13px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            background: #f9f9f9;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #00d9ff;
            background: #fff;
            box-shadow: 0 0 8px rgba(0, 217, 255, 0.2);
        }

        .error, .success {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 5px solid;
        }

        .error {
            background: #ffe0e0;
            color: #d8000c;
            border-left-color: #d8000c;
        }

        .success {
            background: #e7f9ee;
            color: #28a745;
            border-left-color: #28a745;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 217, 255, 0.3);
            filter: brightness(1.1);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #00686b;
            text-decoration: none;
            font-weight: 600;
        }

        .info-box {
            background: rgba(0, 104, 107, 0.05);
            border: 1px dashed #00686b;
            padding: 12px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 11px;
            color: #444;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Daftar Akun</h1>
            <p>MPPK</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Nama Lengkap</label>
                <input type="text" name="username" id="username" placeholder="Min. 3 karakter" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="contoh@gmail.com" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Min. 6 karakter" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Ulangi password" required>
            </div>

            <button type="submit" class="btn-register">DAFTAR SEKARANG</button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>

        <div class="info-box">
            <strong>Keamanan Data:</strong><br>
            • Pastikan email aktif untuk keperluan verifikasi/pemulihan.<br>
            • Jangan berikan password Anda kepada siapapun.
        </div>
    </div>
</body>
</html>