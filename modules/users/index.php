<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: ../../index.php");
    exit;
}
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>
<!-- Tambahkan animate.css CDN jika belum ada di header.php -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<div class="content-wrapper bg-light" style="min-height:100vh;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 animate__animated animate__fadeInDown">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold">Manajemen Pengguna</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right bg-white rounded shadow-sm animate__animated animate__fadeInRight">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php" class="text-primary">Dashboard</a></li>
                        <li class="breadcrumb-item active text-secondary">Pengguna</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Info Akun -->
                <div class="col-md-4">
                    <div class="card card-outline card-secondary shadow animate__animated animate__fadeInLeft" style="border-radius: 12px;">
                        <div class="card-header bg-gradient-secondary text-white" style="border-radius: 12px 12px 0 0;">
                            <h3 class="card-title"><i class="fas fa-user"></i> Info Akun Anda</h3>
                        </div>
                        <div class="card-body bg-white" style="border-radius: 0 0 12px 12px;">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><b>Nama Lengkap:</b> <?php echo $_SESSION['nama_lengkap']; ?></li>
                                <li class="list-group-item"><b>Username:</b> <?php echo $_SESSION['username']; ?></li>
                                <li class="list-group-item"><b>Level:</b> <?php echo ucfirst($_SESSION['level']); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Placeholder Manajemen Pengguna -->
                <div class="col-md-8">
                    <div class="card card-outline card-primary shadow animate__animated animate__fadeInRight" style="border-radius: 12px;">
                        <div class="card-header bg-gradient-primary text-white" style="border-radius: 12px 12px 0 0;">
                            <h3 class="card-title"><i class="fas fa-user-cog"></i> Daftar Pengguna</h3>
                        </div>
                        <div class="card-body bg-white" style="border-radius: 0 0 12px 12px;">
                            <p class="text-secondary">Modul manajemen pengguna belum dikembangkan.<br>Silakan tambahkan fitur sesuai kebutuhan Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php include '../../includes/footer.php'; ?>