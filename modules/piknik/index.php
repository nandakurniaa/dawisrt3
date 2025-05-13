<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Proses hapus piknik (hanya untuk admin)
if (isset($_GET['delete']) && $_SESSION['level'] == 'admin') {
    $id = clean($conn, $_GET['delete']);
    
    // Cek apakah piknik terkait dengan data lain
    $checkQuery = "SELECT COUNT(*) as total FROM piknik_peserta WHERE id_piknik = $id";
    $checkResult = $conn->query($checkQuery);
    $checkData = $checkResult->fetch_assoc();
    
    if ($checkData['total'] > 0) {
        $error = "Data piknik tidak dapat dihapus karena masih terkait dengan data peserta!";
    } else {
        $deleteQuery = "DELETE FROM piknik WHERE id = $id";
        if ($conn->query($deleteQuery)) {
            $success = "Data piknik berhasil dihapus!";
        } else {
            $error = "Gagal menghapus data piknik: " . $conn->error;
        }
    }
}

// Ambil data piknik
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM piknik_peserta WHERE id_piknik = p.id) as jumlah_peserta,
          (SELECT SUM(biaya_total) FROM piknik_peserta WHERE id_piknik = p.id) as total_biaya
          FROM piknik p
          ORDER BY p.tanggal_berangkat DESC";
$result = $conn->query($query);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Data Piknik / Kegiatan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Piknik</li>
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
                        <i class="fas fa-calendar-alt mr-2"></i> Daftar Piknik / Kegiatan
                    </h3>
                    <div style="margin-left:auto;">
                        <a href="tambah.php" class="btn btn-primary btn-tambah-piknik">
                            <i class="fas fa-plus"></i> Tambah Piknik
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTable" class="table table-bordered table-striped table-elegant">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Kegiatan</th>
                                    <th>Tanggal</th>
                                    <th>Tujuan</th>
                                    <th>Biaya per Orang</th>
                                    <th>Jumlah Peserta</th>
                                    <th>Total Biaya</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($row = $result->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $row['nama_kegiatan']; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($row['tanggal_berangkat'])); ?></td>
                                    <td><?php echo $row['tujuan']; ?></td>
                                    <td>Rp <?php echo number_format($row['biaya_per_orang'], 0, ',', '.'); ?></td>
                                    <td><?php echo $row['jumlah_peserta']; ?> orang</td>
                                    <td>Rp <?php echo number_format($row['total_biaya'] ?? 0, 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="detail.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <?php if ($_SESSION['level'] == 'admin'): ?>
                                        <a href="index.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data piknik ini?');">
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
            <style>
            /* Font dan tipografi */
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
            .btn-tambah-piknik {
                font-weight: 500;
                border-radius: 6px;
                padding: 10px 20px;
                background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
                border: none;
                box-shadow: 0 2px 8px rgba(52,152,219,0.13);
                transition: all 0.3s;
            }
            .btn-tambah-piknik:hover {
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
                .btn-tambah-piknik {
                    width: 100%;
                    margin-top: 10px;
                }
                .table-elegant thead th, .table-elegant tbody td {
                    font-size: 12px;
                    padding: 8px;
                }
            }
            </style>
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