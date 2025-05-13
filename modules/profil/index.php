<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$user_id = $_SESSION['user_id'];

// Ambil data user
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

// Proses form update profil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $nama = clean($conn, $_POST['nama']);
    $email = clean($conn, $_POST['email']);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = "Nama tidak boleh kosong";
    }
    
    if (empty($email)) {
        $errors[] = "Email tidak boleh kosong";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Cek apakah email sudah digunakan oleh user lain
    $checkEmail = "SELECT * FROM users WHERE email = '$email' AND id != $user_id";
    $resultEmail = $conn->query($checkEmail);
    if ($resultEmail->num_rows > 0) {
        $errors[] = "Email sudah digunakan oleh pengguna lain";
    }
    
    // Jika tidak ada error, update data
    if (empty($errors)) {
        $query = "UPDATE users SET nama = '$nama', email = '$email' WHERE id = $user_id";
        
        if ($conn->query($query)) {
            // Update session data
            $_SESSION['nama'] = $nama;
            $success_profile = "Profil berhasil diperbarui!";
        } else {
            $error_profile = "Gagal memperbarui profil: " . $conn->error;
        }
    }
}

// Proses form update password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    $errors_password = [];
    
    if (empty($current_password)) {
        $errors_password[] = "Password saat ini tidak boleh kosong";
    }
    
    if (empty($new_password)) {
        $errors_password[] = "Password baru tidak boleh kosong";
    } elseif (strlen($new_password) < 6) {
        $errors_password[] = "Password baru minimal 6 karakter";
    }
    
    if ($new_password != $confirm_password) {
        $errors_password[] = "Konfirmasi password tidak sesuai";
    }
    
    // Verifikasi password saat ini
    if (!password_verify($current_password, $user['password'])) {
        $errors_password[] = "Password saat ini tidak sesuai";
    }
    
    // Jika tidak ada error, update password
    if (empty($errors_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
        
        if ($conn->query($query)) {
            $success_password = "Password berhasil diperbarui!";
        } else {
            $error_password = "Gagal memperbarui password: " . $conn->error;
        }
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Profil Pengguna</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle" src="../../dist/img/user.png" alt="User profile picture">
                            </div>
                            <h3 class="profile-username text-center"><?php echo $user['nama']; ?></h3>
                            <p class="text-muted text-center"><?php echo ucfirst($user['level']); ?></p>
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Username</b> <a class="float-right"><?php echo $user['username']; ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b>Email</b> <a class="float-right"><?php echo $user['email']; ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b>Status</b> <a class="float-right">
                                        <span class="badge badge-success">Aktif</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#profile" data-toggle="tab">Profil</a></li>
                                <li class="nav-item"><a class="nav-link" href="#password" data-toggle="tab">Password</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="profile">
                                    <?php if (isset($error_profile)): ?>
                                        <div class="alert alert-danger alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                                            <?php echo $error_profile; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($success_profile)): ?>
                                        <div class="alert alert-success alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                                            <?php echo $success_profile; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($errors) && !empty($errors)): ?>
                                        <div class="alert alert-danger alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                                            <ul>
                                                <?php foreach ($errors as $error): ?>
                                                    <li><?php echo $error; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form class="form-horizontal" method="post" action="">
                                        <div class="form-group row">
                                            <label for="username" class="col-sm-2 col-form-label">Username</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                                                <small class="form-text text-muted">Username tidak dapat diubah.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="nama" class="col-sm-2 col-form-label">Nama</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo $user['nama']; ?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="email" class="col-sm-2 col-form-label">Email</label>
                                            <div class="col-sm-10">
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="level" class="col-sm-2 col-form-label">Level</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="level" value="<?php echo ucfirst($user['level']); ?>" disabled>
                                                <small class="form-text text-muted">Level pengguna tidak dapat diubah.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="tab-pane" id="password">
                                    <?php if (isset($error_password)): ?>
                                        <div class="alert alert-danger alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                                            <?php echo $error_password; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($success_password)): ?>
                                        <div class="alert alert-success alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                                            <?php echo $success_password; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($errors_password) && !empty($errors_password)): ?>
                                        <div class="alert alert-danger alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                                            <ul>
                                                <?php foreach ($errors_password as $error): ?>
                                                    <li><?php echo $error; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form class="form-horizontal" method="post" action="">
                                        <div class="form-group row">
                                            <label for="current_password" class="col-sm-3 col-form-label">Password Saat Ini</label>
                                            <div class="col-sm-9">
                                                <input type="password" class="form-control" id="current_password" name="current_password">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="new_password" class="col-sm-3 col-form-label">Password Baru</label>
                                            <div class="col-sm-9">
                                                <input type="password" class="form-control" id="new_password" name="new_password">
                                                <small class="form-text text-muted">Password minimal 6 karakter.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="confirm_password" class="col-sm-3 col-form-label">Konfirmasi Password</label>
                                            <div class="col-sm-9">
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-3 col-sm-9">
                                                <button type="submit" name="update_password" class="btn btn-primary">Ubah Password</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../../includes/footer.php'; ?>