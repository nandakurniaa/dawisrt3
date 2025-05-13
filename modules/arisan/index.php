<?php
// Cek session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Proses hapus arisan
if (isset($_GET['delete']) && $_SESSION['level'] == 'admin') {
    $id = clean($conn, $_GET['delete']);
    
    $deleteQuery = "DELETE FROM arisan WHERE id = $id";
    if ($conn->query($deleteQuery)) {
        $success = "Data arisan berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data arisan: " . $conn->error;
    }
}

// Ambil data arisan
$query = "SELECT a.*, ang.nama as nama_penerima 
          FROM arisan a 
          LEFT JOIN anggota ang ON a.id_penerima = ang.id 
          ORDER BY a.tanggal DESC";
$result = $conn->query($query);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Data Arisan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Arisan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                    <h3 class="card-title" style="font-weight:600;font-size:18px;">
                        <i class="fas fa-calendar-alt mr-2"></i> Daftar Arisan
                    </h3>
                    <div style="margin-left:auto;">
                        <a href="tambah.php" class="btn btn-primary btn-tambah-arisan">
                            <i class="fas fa-plus"></i> Tambah Arisan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-elegant">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Penerima</th>
                                    <th>Jumlah Hadir</th>
                                    <th>Jumlah Gula</th>
                                    <th>Jumlah Uang</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($row = $result->fetch_assoc()): 
                                    $status = (strtotime($row['tanggal']) > time()) ? 'Akan Datang' : 'Selesai';
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><?php 
                                        $timestamp = strtotime($row['tanggal']);
                                        $hariIndonesia = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
                                        $namaHari = $hariIndonesia[date('w', $timestamp)];
                                        echo $namaHari . ', ' . date('d-m-Y', $timestamp); 
                                    ?></td>
                                    <td><?php echo $row['nama_penerima']; ?></td>
                                    <td><?php echo $row['jumlah_hadir']; ?> orang</td>
                                    <td><?php echo $row['jumlah_gula']; ?> kg</td>
                                    <td>Rp <?php echo number_format($row['jumlah_uang'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php if ($status == 'Akan Datang'): ?>
                                            <span class="badge badge-info">Akan Datang</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="detail.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <?php if ($status == 'Akan Datang'): ?>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($_SESSION['level'] == 'admin'): ?>
                                        <a href="index.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data arisan ini?');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$(function () {
    $("#dataTable").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
.content-wrapper {
    font-family: 'Poppins', sans-serif;
    background: #f8f9fc;
}
.card {
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(44,62,80,0.07);
    border: none;
}
.card-header {
    background: linear-gradient(90deg, #f8f9fa 60%, #e9ecef 100%);
    border-bottom: 1.5px solid #e9ecef;
    padding: 18px 24px;
}
.card-title {
    color: #2c3e50;
    letter-spacing: 0.5px;
}
.btn-tambah-arisan {
    font-weight: 500;
    border-radius: 6px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border: none;
    box-shadow: 0 2px 8px rgba(52,152,219,0.13);
    transition: all 0.3s;
}
.btn-tambah-arisan:hover {
    background: linear-gradient(135deg, #2980b9 0%, #2573a7 100%);
    transform: translateY(-2px);
}
.table-elegant {
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    margin-bottom: 0;
}
.table-elegant thead th {
    background: #34495e;
    color: #fff;
    font-weight: 600;
    font-size: 13px;
    border-bottom: 2.5px solid #2980b9;
    text-align: center;
    vertical-align: middle;
}
.table-elegant tbody td {
    background: #fff;
    color: #2c3e50;
    font-size: 14px;
    vertical-align: middle;
    border-color: #e9ecef;
    font-weight: 500;
}
.table-elegant tbody tr:nth-child(odd) td {
    background: #f4f6fa;
}
.table-elegant tbody tr:hover td {
    background: #eaf1fb;
    transition: background 0.2s;
}
.table-elegant td, .table-elegant th {
    border: 1.5px solid #e9ecef !important;
}
.btn-info, .btn-warning, .btn-danger {
    font-weight: 500;
    border-radius: 5px;
    box-shadow: 0 1px 4px rgba(44,62,80,0.07);
    margin-bottom: 3px;
}
.btn-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border: none;
    color: #fff;
}
.btn-info:hover {
    background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
}
.btn-warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border: none;
    color: #fff;
}
.btn-warning:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
}
.btn-danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border: none;
    color: #fff;
}
.btn-danger:hover {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
}
@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .btn-tambah-arisan {
        width: 100%;
        margin-top: 10px;
    }
    .table-elegant thead th, .table-elegant tbody td {
        font-size: 12px;
        padding: 8px;
    }
}
</style>