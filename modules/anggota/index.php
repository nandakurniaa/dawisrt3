<?php
// Cek session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Ambil data anggota
$query = "SELECT * FROM anggota ORDER BY nama ASC";
$result = $conn->query($query);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- CSS Kustom untuk Tampilan Elegan -->
<style>
    /* Font dan Tipografi */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    .content-wrapper {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fc;
    }
    
    .page-title {
        font-weight: 600;
        color: #2c3e50;
        letter-spacing: 0.5px;
        border-left: 4px solid #3498db;
        padding-left: 15px;
        margin-bottom: 20px;
    }
    
    /* Card Styling */
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: none;
        overflow: hidden;
        margin-bottom: 25px;
        transition: all 0.3s ease;
    }
    
    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .card-title {
        font-weight: 600;
        font-size: 18px;
        color: #2c3e50;
        margin-bottom: 0;
    }
    
    /* Tabel Styling */
    .table-container {
        padding: 0;
        overflow-x: auto;
    }
    
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .data-table th {
        background-color: #f8f9fa;
        color: #2c3e50;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        padding: 15px;
        border-bottom: 2px solid #e9ecef;
        text-align: left;
    }
    
    .data-table td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f1f1;
        color: #555;
        font-size: 14px;
    }
    
    .data-table tbody tr {
        transition: all 0.3s ease;
    }
    
    .data-table tbody tr:hover {
        background-color: rgba(52, 152, 219, 0.05);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.03);
    }
    
    /* Status Badge */
    .badge-status {
        padding: 6px 12px;
        border-radius: 30px;
        font-weight: 500;
        font-size: 12px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    
    .badge-aktif {
        background-color: #2ecc71;
        color: white;
    }
    
    .badge-nonaktif {
        background-color: #e74c3c;
        color: white;
    }
    
    /* Tombol Styling */
    .btn {
        border-radius: 5px;
        font-weight: 500;
        padding: 8px 15px;
        transition: all 0.3s ease;
        letter-spacing: 0.3px;
        font-size: 13px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        border: none;
        box-shadow: 0 2px 5px rgba(52, 152, 219, 0.2);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #2980b9 0%, #2573a7 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
    }
    
    .btn-warning {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        border: none;
        box-shadow: 0 2px 5px rgba(243, 156, 18, 0.2);
        color: white;
    }
    
    .btn-warning:hover {
        background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
        color: white;
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        border: none;
        box-shadow: 0 2px 5px rgba(231, 76, 60, 0.2);
    }
    
    .btn-danger:hover {
        background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
    }
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .btn i {
        margin-right: 5px;
    }
    
    .action-buttons .btn {
        margin-right: 5px;
    }
    
    .action-buttons .btn:last-child {
        margin-right: 0;
    }
    
    /* Breadcrumb Styling */
    .breadcrumb {
        background-color: transparent;
        padding: 0;
        margin-bottom: 20px;
    }
    
    .breadcrumb-item {
        font-size: 14px;
        font-weight: 500;
    }
    
    .breadcrumb-item a {
        color: #3498db;
        transition: all 0.3s ease;
    }
    
    .breadcrumb-item a:hover {
        color: #2980b9;
        text-decoration: none;
    }
    
    .breadcrumb-item.active {
        color: #7f8c8d;
    }
    
    /* Tambah Anggota Button */
    .btn-tambah {
        padding: 8px 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-tambah i {
        margin-right: 8px;
        font-size: 14px;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .btn-tambah {
            margin-top: 10px;
            align-self: flex-start;
        }
        
        .data-table th, 
        .data-table td {
            padding: 10px;
        }
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../../modules/dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Anggota</li>
                    </ol>
                    <h1 class="page-title">Data Anggota</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i> Daftar Anggota
                    </h3>
                    <a href="tambah.php" class="btn btn-primary btn-tambah">
                        <i class="fas fa-plus"></i> Tambah Anggota
                    </a>
                </div>
                <div class="card-body table-container p-0">
                    <table class="data-table table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Nama</th>
                                <th width="15%">Jabatan</th>
                                <th width="20%">No. HP</th>
                                <th width="15%">Status</th>
                                <th width="25%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                $no = 1;
                                while ($row = $result->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['jabatan']) ?></td>
                                    <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                    <td>
                                        <span class="badge-status <?= $row['status'] == 'Aktif' ? 'badge-aktif' : 'badge-nonaktif' ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">Tidak ada data anggota</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../../includes/footer.php'; ?>