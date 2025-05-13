<?php
// Cek session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Filter data
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-d', strtotime('-1 year'));
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

// Query untuk data kas
$whereClause = "";
if ($jenis != '') {
    $whereClause .= " AND jenis = '$jenis'";
}
if ($tanggal_mulai != '' && $tanggal_akhir != '') {
    $whereClause .= " AND tanggal BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'";
}

$query = "SELECT * FROM kas WHERE 1=1 $whereClause ORDER BY tanggal DESC";
$result = $conn->query($query);

// Ambil data ringkasan kas
$querySaldo = "SELECT 
    COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE 0 END), 0) as total_masuk,
    COALESCE(SUM(CASE WHEN jenis = 'keluar' THEN jumlah ELSE 0 END), 0) as total_keluar,
    COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE -jumlah END), 0) as saldo
    FROM kas";
$resultSaldo = $conn->query($querySaldo);
$saldo = $resultSaldo->fetch_assoc();

// Hitung saldo awal bulan
$bulanIni = date('Y-m-01');
$querySaldoAwal = "SELECT 
    COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE -jumlah END), 0) as saldo_awal
    FROM kas
    WHERE tanggal < '$bulanIni'";
$resultSaldoAwal = $conn->query($querySaldoAwal);
$saldoAwal = $resultSaldoAwal->fetch_assoc()['saldo_awal'];

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
    
    /* Info Box Styling */
    .info-box {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
    }
    
    .info-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .info-box-content {
        padding: 20px;
        position: relative;
        z-index: 10;
    }
    
    .info-box-icon {
        position: absolute;
        right: 20px;
        bottom: 20px;
        font-size: 60px;
        opacity: 0.2;
        z-index: 1;
        transition: all 0.3s ease;
    }
    
    .info-box:hover .info-box-icon {
        transform: scale(1.1) rotate(10deg);
        opacity: 0.3;
    }
    
    .info-box-text {
        font-size: 16px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 5px;
    }
    
    .info-box-number {
        font-size: 28px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .bg-income {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }
    
    .bg-expense {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }
    
    .bg-balance {
        background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
    }
    
    /* Filter Card */
    .filter-card {
        margin-bottom: 25px;
    }
    
    .filter-card .card-body {
        padding: 20px;
    }
    
    .filter-title {
        font-weight: 600;
        font-size: 16px;
        color: #2c3e50;
        margin-bottom: 15px;
    }
    
    .form-group label {
        font-weight: 500;
        color: #34495e;
        font-size: 14px;
    }
    
    .form-control {
        border-radius: 5px;
        border: 1px solid #e9ecef;
        padding: 10px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        color: #6c757d;
    }
    
    /* Tabel Styling - DITINGKATKAN */
    .table-container {
        padding: 0;
        overflow-x: auto;
    }
    
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .data-table th {
        background-color: #34495e;
        color: #ffffff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        padding: 15px;
        border: none;
        text-align: left;
    }
    
    .data-table td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
        color: #555;
        font-size: 14px;
        font-weight: 500;
    }
    
    .data-table tbody tr {
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }
    
    .data-table tbody tr:hover {
        background-color: rgba(52, 152, 219, 0.05);
        transform: translateX(3px);
        border-left: 3px solid #3498db;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.03);
    }
    
    .data-table .saldo-awal {
        background-color: #f8f9fa;
        font-weight: 600;
        border-left: 3px solid #f1c40f;
    }
    
    .data-table .saldo-awal:hover {
        border-left: 3px solid #f1c40f;
    }
    
    /* Kolom Angka */
    .data-table td.text-right,
    .data-table th.text-right {
        text-align: right;
    }
    
    /* Warna Saldo Negatif */
    .saldo-negatif {
        color: #e74c3c;
        font-weight: 600;
    }
    
    /* Warna Saldo Positif */
    .saldo-positif {
        color: #2ecc71;
        font-weight: 600;
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
    
    /* Tombol Aksi */
    .action-buttons {
        display: flex;
        justify-content: flex-end;
    }
    
    .action-buttons .btn {
        margin-right: 5px;
        padding: 6px 10px;
        border-radius: 4px;
    }
    
    .action-buttons .btn:last-child {
        margin-right: 0;
    }
    
    .btn-edit {
        background-color: #f39c12;
        color: white;
        border: none;
    }
    
    .btn-edit:hover {
        background-color: #e67e22;
    }
    
    .btn-hapus {
        background-color: #e74c3c;
        color: white;
        border: none;
    }
    
    .btn-hapus:hover {
        background-color: #c0392b;
    }
    
    /* Tombol Tambah Transaksi */
    .btn-tambah-transaksi {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-tambah-transaksi:hover {
        background: linear-gradient(135deg, #2980b9 0%, #2573a7 100%);
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
    }
    
    .btn-tambah-transaksi i {
        margin-right: 8px;
        font-size: 16px;
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
        
        .info-box-number {
            font-size: 24px;
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
                        <li class="breadcrumb-item active">Laporan Keuangan Kas</li>
                    </ol>
                    <h1 class="page-title">Laporan Keuangan Kas</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Info Boxes -->
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="info-box bg-income">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pemasukan</span>
                            <h3 class="info-box-number">Rp <?= number_format($saldo['total_masuk'], 0, ',', '.') ?></h3>
                        </div>
                        <i class="fas fa-arrow-down info-box-icon"></i>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="info-box bg-expense">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pengeluaran</span>
                            <h3 class="info-box-number">Rp <?= number_format($saldo['total_keluar'], 0, ',', '.') ?></h3>
                        </div>
                        <i class="fas fa-arrow-up info-box-icon"></i>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="info-box bg-balance">
                        <div class="info-box-content">
                            <span class="info-box-text">Saldo Saat Ini</span>
                            <h3 class="info-box-number">Rp <?= number_format($saldo['saldo'], 0, ',', '.') ?></h3>
                        </div>
                        <i class="fas fa-wallet info-box-icon"></i>
                    </div>
                </div>
            </div>
            
            <!-- Filter Card -->
            <div class="card filter-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-2"></i> Filter Laporan Kas
                    </h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tanggal_mulai">Tanggal Mulai:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?= $tanggal_mulai ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tanggal_akhir">Tanggal Akhir:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="<?= $tanggal_akhir ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="jenis">Jenis Transaksi:</label>
                                    <select class="form-control" id="jenis" name="jenis">
                                        <option value="" <?= $jenis == '' ? 'selected' : '' ?>>Semua</option>
                                        <option value="masuk" <?= $jenis == 'masuk' ? 'selected' : '' ?>>Pemasukan</option>
                                        <option value="keluar" <?= $jenis == 'keluar' ? 'selected' : '' ?>>Pengeluaran</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-sync-alt"></i> Reset
                                </a>
                                <a href="cetak.php?tanggal_mulai=<?= $tanggal_mulai ?>&tanggal_akhir=<?= $tanggal_akhir ?>&jenis=<?= $jenis ?>" target="_blank" class="btn btn-success">
                                    <i class="fas fa-print"></i> Cetak Laporan
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tabel Transaksi -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i> Daftar Transaksi Kas
                    </h3>
                    <div style="margin-left:auto;">
                        <a href="tambah.php" class="btn-tambah-transaksi">
                            <i class="fas fa-plus-circle"></i> Tambah Transaksi
                        </a>
                    </div>
                </div>
                <div class="card-body table-container p-0">
                    <table class="data-table table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Tanggal</th>
                                <th width="30%">Keterangan</th>
                                <th width="15%" class="text-right">Kas Masuk</th>
                                <th width="15%" class="text-right">Kas Keluar</th>
                                <th width="15%" class="text-right">Saldo</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="saldo-awal">
                                <td colspan="5" class="text-right">Saldo Awal Bulan</td>
                                <td class="text-right <?= $saldoAwal < 0 ? 'saldo-negatif' : 'saldo-positif' ?>">
                                    Rp <?= number_format($saldoAwal, 0, ',', '.') ?>
                                </td>
                                <td></td>
                            </tr>
                            <?php
                            if ($result->num_rows > 0) {
                                $no = 1;
                                $saldoBerjalan = $saldoAwal;
                                while ($row = $result->fetch_assoc()) {
                                    if ($row['jenis'] == 'masuk') {
                                        $saldoBerjalan += $row['jumlah'];
                                    } else {
                                        $saldoBerjalan -= $row['jumlah'];
                                    }
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                    <td class="text-right"><?= $row['jenis'] == 'masuk' ? 'Rp ' . number_format($row['jumlah'], 0, ',', '.') : '-' ?></td>
                                    <td class="text-right"><?= $row['jenis'] == 'keluar' ? 'Rp ' . number_format($row['jumlah'], 0, ',', '.') : '-' ?></td>
                                    <td class="text-right <?= $saldoBerjalan < 0 ? 'saldo-negatif' : 'saldo-positif' ?>">
                                        Rp <?= number_format($saldoBerjalan, 0, ',', '.') ?>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-edit btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-hapus btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">Tidak ada data transaksi</td>
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