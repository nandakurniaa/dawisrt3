<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = clean($conn, $_GET['id']);

// Ambil data piknik berdasarkan id
$query = "SELECT * FROM piknik WHERE id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Data piknik tidak ditemukan!";
    header("Location: index.php");
    exit;
}

$piknik = $result->fetch_assoc();

// Ambil data peserta piknik
$queryPeserta = "SELECT pp.*, a.nama, a.no_hp 
                FROM piknik_peserta pp 
                LEFT JOIN anggota a ON pp.id_anggota = a.id 
                WHERE pp.id_piknik = $id
                ORDER BY a.nama ASC";
$resultPeserta = $conn->query($queryPeserta);

// Hitung total peserta dan biaya
$queryTotal = "SELECT 
                SUM(jumlah_anggota) as total_peserta,
                SUM(biaya_total) as total_biaya
              FROM piknik_peserta 
              WHERE id_piknik = $id";
$resultTotal = $conn->query($queryTotal);
$total = $resultTotal->fetch_assoc();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detail Piknik / Kegiatan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Piknik</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informasi Piknik</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 200px">Nama Kegiatan</th>
                                    <td><?php echo $piknik['nama_kegiatan']; ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Berangkat</th>
                                    <td><?php echo date('d-m-Y', strtotime($piknik['tanggal_berangkat'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Pulang</th>
                                    <td><?php echo $piknik['tanggal_pulang'] ? date('d-m-Y', strtotime($piknik['tanggal_pulang'])) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <th>Tujuan</th>
                                    <td><?php echo $piknik['tujuan']; ?></td>
                                </tr>
                                <tr>
                                    <th>Biaya per Orang</th>
                                    <td>Rp <?php echo number_format($piknik['biaya_per_orang'], 0, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <th>Keterangan</th>
                                    <td><?php echo nl2br($piknik['keterangan']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ringkasan Peserta</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Peserta</span>
                                            <span class="info-box-number"><?php echo $total['total_peserta'] ?? 0; ?> orang</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-money-bill"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Biaya</span>
                                            <span class="info-box-number">Rp <?php echo number_format($total['total_biaya'] ?? 0, 0, ',', '.'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <a href="peserta.php?id=<?php echo $id; ?>" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> Kelola Peserta
                                </a>
                                <a href="cetak.php?id=<?php echo $id; ?>" class="btn btn-info" target="_blank">
                                    <i class="fas fa-print"></i> Cetak Daftar Peserta
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Peserta</h3>
                </div>
                <div class="card-body">
                    <table id="dataTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <!-- Hapus kolom Alamat dari tabel HTML -->
                                <th>Nama Peserta</th>
                                <!-- <th>Alamat</th> -->
                                <th>No. HP</th>
                                <th>Jumlah Anggota</th>
                                <th>Biaya Total</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($peserta = $resultPeserta->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $peserta['nama']; ?></td>
                                <!-- <td><?php echo $peserta['alamat']; ?></td> -->
                                <td><?php echo $peserta['no_hp']; ?></td>
                                <td><?php echo $peserta['jumlah_anggota']; ?> orang</td>
                                <td>Rp <?php echo number_format($peserta['biaya_total'], 0, ',', '.'); ?></td>
                                <td><?php echo $peserta['keterangan']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <a href="index.php" class="btn btn-secondary">Kembali ke Daftar Piknik</a>
                            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Data Piknik
                            </a>
                        </div>
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