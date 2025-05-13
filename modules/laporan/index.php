<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

// Cek level akses (hanya admin dan operator)
if ($_SESSION['level'] == 'anggota') {
    header("Location: ../../modules/dashboard/index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Filter berdasarkan bulan dan tahun
$bulan = isset($_GET['bulan']) ? clean($conn, $_GET['bulan']) : date('m');
$tahun = isset($_GET['tahun']) ? clean($conn, $_GET['tahun']) : date('Y');

// Ambil data kas berdasarkan bulan dan tahun
$queryKas = "SELECT 
              SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE 0 END) as total_masuk,
              SUM(CASE WHEN jenis = 'keluar' THEN jumlah ELSE 0 END) as total_keluar
            FROM kas 
            WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'";
$resultKas = $conn->query($queryKas);
$dataKas = $resultKas->fetch_assoc();

// Hitung saldo awal bulan
$queryAwal = "SELECT 
              SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE 0 END) as total_masuk,
              SUM(CASE WHEN jenis = 'keluar' THEN jumlah ELSE 0 END) as total_keluar
            FROM kas 
            WHERE (YEAR(tanggal) < '$tahun' OR (YEAR(tanggal) = '$tahun' AND MONTH(tanggal) < '$bulan'))";
$resultAwal = $conn->query($queryAwal);
$saldoAwal = $resultAwal->fetch_assoc();
$saldoAwalBulan = $saldoAwal['total_masuk'] - $saldoAwal['total_keluar'];

// Ambil data piknik berdasarkan bulan dan tahun
$queryPiknik = "SELECT COUNT(*) as total_piknik,
                SUM((SELECT COUNT(*) FROM piknik_peserta WHERE id_piknik = p.id)) as total_peserta,
                SUM((SELECT SUM(biaya_total) FROM piknik_peserta WHERE id_piknik = p.id)) as total_biaya
                FROM piknik p
                WHERE MONTH(p.tanggal_berangkat) = '$bulan' AND YEAR(p.tanggal_berangkat) = '$tahun'";
$resultPiknik = $conn->query($queryPiknik);
$dataPiknik = $resultPiknik->fetch_assoc();

// Ambil data anggota
$queryAnggota = "SELECT 
                  COUNT(*) as total_anggota,
                  SUM(CASE WHEN status = 'Aktif' THEN 1 ELSE 0 END) as anggota_aktif,
                  SUM(CASE WHEN status = 'Tidak Aktif' THEN 1 ELSE 0 END) as anggota_tidak_aktif
                FROM anggota";
$resultAnggota = $conn->query($queryAnggota);
$dataAnggota = $resultAnggota->fetch_assoc();

// Nama bulan dalam bahasa Indonesia
$namaBulan = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Bulanan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Laporan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Laporan</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Bulan</label>
                                    <select class="form-control" name="bulan">
                                        <?php foreach ($namaBulan as $key => $value): ?>
                                            <option value="<?php echo $key; ?>" <?php echo ($bulan == $key) ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Tahun</label>
                                    <select class="form-control" name="tahun">
                                        <?php 
                                        $tahunSekarang = date('Y');
                                        for ($i = $tahunSekarang - 5; $i <= $tahunSekarang; $i++): 
                                        ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($tahun == $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ringkasan Laporan -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>Rp <?php echo number_format($dataKas['total_masuk'] ?? 0, 0, ',', '.'); ?></h3>
                            <p>Total Kas Masuk</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <a href="../kas/index.php?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>Rp <?php echo number_format($dataKas['total_keluar'] ?? 0, 0, ',', '.'); ?></h3>
                            <p>Total Kas Keluar</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <a href="../kas/index.php?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>Rp <?php echo number_format(($saldoAwalBulan + $dataKas['total_masuk'] - $dataKas['total_keluar']) ?? 0, 0, ',', '.'); ?></h3>
                            <p>Saldo Akhir Bulan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <a href="../kas/index.php?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $dataPiknik['total_piknik'] ?? 0; ?></h3>
                            <p>Kegiatan Piknik</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-bus"></i>
                        </div>
                        <a href="../piknik/index.php" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Laporan Kas -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Laporan Kas Bulan <?php echo $namaBulan[$bulan] . ' ' . $tahun; ?></h3>
                            <div class="card-tools">
                                <a href="../kas/cetak.php?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-print"></i> Cetak
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Saldo Awal Bulan</th>
                                        <td class="text-right">Rp <?php echo number_format($saldoAwalBulan, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Kas Masuk</th>
                                        <td class="text-right">Rp <?php echo number_format($dataKas['total_masuk'] ?? 0, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Kas Keluar</th>
                                        <td class="text-right">Rp <?php echo number_format($dataKas['total_keluar'] ?? 0, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Saldo Akhir Bulan</th>
                                        <td class="text-right">Rp <?php echo number_format(($saldoAwalBulan + $dataKas['total_masuk'] - $dataKas['total_keluar']) ?? 0, 0, ',', '.'); ?></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                <a href="../kas/index.php?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Lihat Detail Kas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Laporan Piknik -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Laporan Piknik Bulan <?php echo $namaBulan[$bulan] . ' ' . $tahun; ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Jumlah Kegiatan</th>
                                        <td><?php echo $dataPiknik['total_piknik'] ?? 0; ?> kegiatan</td>
                                    </tr>
                                    <tr>
                                        <th>Total Peserta</th>
                                        <td><?php echo $dataPiknik['total_peserta'] ?? 0; ?> orang</td>
                                    </tr>
                                    <tr>
                                        <th>Total Biaya</th>
                                        <td>Rp <?php echo number_format($dataPiknik['total_biaya'] ?? 0, 0, ',', '.'); ?></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                <a href="../piknik/index.php" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Lihat Detail Piknik
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Laporan Anggota -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Laporan Anggota</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Anggota</span>
                                    <span class="info-box-number"><?php echo $dataAnggota['total_anggota']; ?> orang</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Anggota Aktif</span>
                                    <span class="info-box-number"><?php echo $dataAnggota['anggota_aktif']; ?> orang</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-user-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Anggota Tidak Aktif</span>
                                    <span class="info-box-number"><?php echo $dataAnggota['anggota_tidak_aktif']; ?> orang</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="../anggota/index.php" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Lihat Detail Anggota
                        </a>
                        <!-- Tombol cetak daftar anggota dihapus -->
                    </div>
                </div>
            </div>
            
            <!-- Tombol Cetak Laporan Bulanan -->
            <div class="card">
                <div class="card-body">
                    <a href="cetak.php?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" class="btn btn-success btn-lg btn-block" target="_blank">
                        <i class="fas fa-print"></i> Cetak Laporan Bulanan <?php echo $namaBulan[$bulan] . ' ' . $tahun; ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$(function () {
    // Initialize Select2
    $('.select2').select2();
});
</script>