<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" href="../../logout.php" role="button">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="../../modules/dashboard/index.php" class="brand-link">
        <img src="../../assets/img/logo.png" alt="DAWIS RT 3 Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">DAWIS RT 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="../../assets/img/user.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= $_SESSION['nama'] ?? 'Administrator' ?></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent nav-legacy" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="../../modules/dashboard/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/modules/dashboard/') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <!-- Anggota -->
                <li class="nav-item">
                    <a href="../../modules/anggota/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/modules/anggota/') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Anggota</p>
                    </a>
                </li>
                
                <!-- Kas -->
                <li class="nav-item">
                    <a href="../../modules/kas/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/modules/kas/') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-money-bill-wave"></i>
                        <p>Kas</p>
                    </a>
                </li>
                
                <!-- Piknik -->
                <li class="nav-item">
                    <a href="../../modules/piknik/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/modules/piknik/') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-bus"></i>
                        <p>Piknik</p>
                    </a>
                </li>
                
                <!-- Arisan -->
                <li class="nav-item">
                    <a href="../../modules/arisan/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/modules/arisan/') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-gift"></i>
                        <p>Arisan</p>
                    </a>
                </li>
                
                <!-- Laporan -->
                <li class="nav-item">
                    <a href="../../modules/laporan/index.php" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/modules/laporan/') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Laporan</p>
                    </a>
                </li>
                
                
                
                <!-- Logout -->
                <li class="nav-item mt-4">
                    <a href="../../logout.php" class="nav-link text-danger">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Keluar</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<style>
/* Upgrade Tampilan Sidebar */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

.main-sidebar {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2530 100%);
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    overflow: hidden;
}

.sidebar {
    font-family: 'Poppins', sans-serif;
    padding: 0;
}

/* Brand Logo */
.brand-link {
    background: rgba(0, 0, 0, 0.2);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 15px;
    transition: all 0.3s ease;
}

.brand-link:hover {
    background: rgba(0, 0, 0, 0.3);
}

.brand-text {
    font-weight: 600 !important;
    letter-spacing: 1px;
    color: #fff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.brand-image {
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.brand-link:hover .brand-image {
    transform: rotate(5deg);
}

/* User Panel */
.user-panel {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 15px 10px;
    background: rgba(0, 0, 0, 0.1);
}

.user-panel .image img {
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.user-panel:hover .image img {
    transform: scale(1.1);
}

.user-panel .info a {
    color: #fff;
    font-weight: 500;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.user-panel .info a:hover {
    color: #3498db;
    text-decoration: none;
}

/* Nav Items */
.nav-sidebar .nav-item {
    margin-bottom: 3px;
}

.nav-sidebar .nav-link {
    color: rgba(255, 255, 255, 0.8);
    border-radius: 8px;
    margin: 0 8px;
    padding: 12px 15px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    overflow: hidden;
}

.nav-sidebar .nav-link:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transition: all 0.5s ease;
}

.nav-sidebar .nav-link:hover:before {
    left: 100%;
}

.nav-sidebar .nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.nav-sidebar .nav-link.active {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    font-weight: 500;
}

.nav-sidebar .nav-link.active:hover {
    transform: translateX(5px) scale(1.02);
}

.nav-sidebar .nav-icon {
    margin-right: 10px;
    font-size: 18px;
    text-align: center;
    width: 25px;
    transition: all 0.3s ease;
}

.nav-sidebar .nav-link:hover .nav-icon {
    transform: scale(1.2);
}

/* Animasi untuk menu aktif */
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
    100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
}

.nav-sidebar .nav-link.active {
    animation: pulse 2s infinite;
}

/* Tombol Logout */
.nav-sidebar .nav-link.text-danger {
    background: rgba(231, 76, 60, 0.1);
    border: 1px solid rgba(231, 76, 60, 0.3);
    color: #e74c3c;
}

.nav-sidebar .nav-link.text-danger:hover {
    background: rgba(231, 76, 60, 0.2);
    color: #fff;
    border-color: #e74c3c;
}

/* Responsif */
@media (max-width: 992px) {
    .sidebar-mini.sidebar-collapse .main-sidebar:hover {
        box-shadow: 0 0 50px rgba(0, 0, 0, 0.3);
    }
    
    .sidebar-mini.sidebar-collapse .nav-sidebar .nav-link {
        margin: 0 5px;
        padding: 10px;
    }
}
</style>
