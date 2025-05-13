<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $level = 'anggota';

    if (empty($username) || empty($nama_lengkap) || empty($password) || empty($password2)) {
        $error = "Semua field harus diisi!";
    } elseif ($password !== $password2) {
        $error = "Konfirmasi password tidak sesuai!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, nama_lengkap, password, level) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $nama_lengkap, $hashed, $level);
            if ($stmt->execute()) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Registrasi gagal. Silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DAWIS RT 3</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #27ae60;
            --text-color: #333;
            --light-color: #f5f5f5;
            --dark-color: #2c3e50;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 0;
            position: relative;
            overflow-x: hidden;
        }
        
        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(39, 174, 96, 0.1);
            animation: float 15s infinite ease-in-out;
        }
        
        .circle:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }
        
        .circle:nth-child(2) {
            width: 400px;
            height: 400px;
            top: 50%;
            right: -200px;
            animation-delay: 2s;
        }
        
        .circle:nth-child(3) {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: 30%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0) rotate(0deg); }
        }
        
        .register-container {
            width: 450px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeIn 1s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .register-header {
            background: linear-gradient(to right, var(--accent-color), #2ecc71);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .register-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            margin-bottom: 5px;
            letter-spacing: 1px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .register-header p {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .register-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            background-color: #f9f9f9;
        }
        
        .form-group input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 10px rgba(39, 174, 96, 0.1);
            outline: none;
            background-color: white;
        }
        
        .form-group i {
            position: absolute;
            right: 15px;
            top: 40px;
            color: #aaa;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, var(--accent-color), #2ecc71);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn:after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .btn:hover:after {
            left: 100%;
        }
        
        .btn-login {
            background: white;
            color: var(--accent-color);
            border: 1px solid var(--accent-color);
        }
        
        .btn-login:hover {
            background: #f5f5f5;
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 14px;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .alert-danger {
            background-color: #fde8e8;
            color: #e53e3e;
            border-left: 4px solid #e53e3e;
        }
        
        .alert-success {
            background-color: #e6f7ef;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            padding: 0 10px;
            color: #777;
            font-size: 14px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
            font-size: 12px;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 5px;
            transition: all 0.3s;
        }
    </style>
</head>
<body>
    <div class="background-animation">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>
    
    <div class="register-container">
        <div class="register-header">
            <h1>DAWIS RT 3</h1>
            <p>Pendaftaran Akun Baru</p>
        </div>
        
        <div class="register-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                    <i class="fas fa-user"></i>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required>
                    <i class="fas fa-user-tag"></i>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    <i class="fas fa-lock"></i>
                    <div class="password-strength" id="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="password2">Konfirmasi Password</label>
                    <input type="password" id="password2" name="password2" placeholder="Konfirmasi password" required>
                    <i class="fas fa-lock"></i>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-user-plus"></i> Daftar
                </button>
            </form>
            
            <div class="divider">
                <span>atau</span>
            </div>
            
            <a href="login.php" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Sudah Punya Akun? Login
            </a>
            
            <div class="footer">
                &copy; <?php echo date('Y'); ?> Sistem DAWIS RT 3. All rights reserved.
            </div>
        </div>
    </div>
    
    <script>
        // Animasi tambahan untuk form
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input');
            const passwordInput = document.getElementById('password');
            const passwordStrength = document.getElementById('password-strength');
            const password2Input = document.getElementById('password2');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('label').style.color = '#27ae60';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('label').style.color = '#333';
                });
            });
            
            // Password strength indicator
            passwordInput.addEventListener('input', function() {
                const value = this.value;
                let strength = 0;
                
                if (value.length >= 8) strength += 25;
                if (value.match(/[a-z]+/)) strength += 25;
                if (value.match(/[A-Z]+/)) strength += 25;
                if (value.match(/[0-9]+/)) strength += 25;
                
                passwordStrength.style.width = strength + '%';
                
                if (strength <= 25) {
                    passwordStrength.style.backgroundColor = '#e53e3e';
                } else if (strength <= 50) {
                    passwordStrength.style.backgroundColor = '#ed8936';
                } else if (strength <= 75) {
                    passwordStrength.style.backgroundColor = '#ecc94b';
                } else {
                    passwordStrength.style.backgroundColor = '#27ae60';
                }
            });
            
            // Password match check
            password2Input.addEventListener('input', function() {
                if (this.value === passwordInput.value) {
                    this.style.borderColor = '#27ae60';
                } else {
                    this.style.borderColor = '#e53e3e';
                }
            });
        });
    </script>
</body>
</html>