<?php
session_start();
require_once 'config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['level'] = $user['level'];
            header("Location: modules/dashboard/index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DAWIS RT 3</title>
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
            --accent-color: #e74c3c;
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
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
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
            background: rgba(52, 152, 219, 0.1);
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
        
        .login-container {
            width: 400px;
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
        
        .login-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .login-header h1 {
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
        
        .login-header p {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
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
            border-color: var(--secondary-color);
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.1);
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
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
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
        
        .btn-register {
            background: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-register:hover {
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
    </style>
</head>
<body>
    <div class="background-animation">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>
    
    <div class="login-container">
        <div class="login-header">
            <h1>DAWIS RT 3</h1>
            <p>Sistem Pengelolaan Arisan DAWIS</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
                    <i class="fas fa-user"></i>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    <i class="fas fa-lock"></i>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="divider">
                <span>atau</span>
            </div>
            
            <a href="register.php" class="btn btn-register">
                <i class="fas fa-user-plus"></i> Daftar Akun Baru
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
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('label').style.color = '#3498db';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('label').style.color = '#333';
                });
            });
        });
    </script>
</body>
</html>