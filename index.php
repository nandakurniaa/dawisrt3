<?php
session_start();

// Redirect ke dashboard jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: modules/dashboard/index.php");
    exit;
}

// Cek jika form login disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'config/database.php';
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password (menggunakan password_verify untuk password yang di-hash)
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['level'] = $user['level'];
            
            // Redirect ke dashboard
            header("Location: modules/dashboard/index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem DAWIS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-logo h1 {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <h1>DAWIS</h1>
            <p class="text-muted">Sistem Pengelolaan Arisan DAWIS</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
        
        <div class="mt-3 text-center">
            <small class="text-muted">© <?php echo date('Y'); ?> Sistem DAWIS</small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
