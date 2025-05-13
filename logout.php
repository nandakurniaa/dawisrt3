<?php
session_start();

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Hancurkan session
session_destroy();

// Set pesan logout berhasil
$message = "Anda telah berhasil logout dari sistem.";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - DAWIS RT 3</title>
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
            background: rgba(231, 76, 60, 0.1);
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
        
        .logout-container {
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
        
        .logout-header {
            background: linear-gradient(to right, var(--accent-color), #c0392b);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .logout-header h1 {
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
        
        .logout-header p {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .logout-body {
            padding: 30px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 60px;
            color: var(--accent-color);
            margin-bottom: 20px;
            animation: bounceIn 1s;
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            60% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(1); }
        }
        
        .logout-message {
            font-size: 18px;
            color: var(--text-color);
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(to right, var(--accent-color), #c0392b);
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
            text-decoration: none;
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
        
        .countdown {
            font-size: 14px;
            color: #777;
            margin-top: 20px;
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
    
    <div class="logout-container">
        <div class="logout-header">
            <h1>DAWIS RT 3</h1>
            <p>Sistem Pengelolaan Arisan DAWIS</p>
        </div>
        
        <div class="logout-body">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <div class="logout-message">
                <?php echo $message; ?>
            </div>
            
            <a href="login.php" class="btn">
                <i class="fas fa-sign-in-alt"></i> Kembali ke Login
            </a>
            
            <div class="countdown">
                Anda akan dialihkan ke halaman login dalam <span id="timer">5</span> detik.
            </div>
            
            <div class="footer">
                &copy; <?php echo date('Y'); ?> Sistem DAWIS RT 3. All rights reserved.
            </div>
        </div>
    </div>
    
    <script>
        // Countdown timer dan redirect otomatis
        document.addEventListener('DOMContentLoaded', function() {
            let seconds = 5;
            const timerElement = document.getElementById('timer');
            
            const countdown = setInterval(function() {
                seconds--;
                timerElement.textContent = seconds;
                
                if (seconds <= 0) {
                    clearInterval(countdown);
                    window.location.href = 'login.php';
                }
            }, 1000);
            
            // Animasi tambahan
            const logoutContainer = document.querySelector('.logout-container');
            
            setTimeout(function() {
                logoutContainer.style.animation = 'pulse 2s';
            }, 1500);
        });
    </script>
</body>
</html>