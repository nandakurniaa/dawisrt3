<?php
// Cek session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Ambil data ringkasan kas
$querySaldo = "SELECT 
    COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE 0 END), 0) as total_masuk,
    COALESCE(SUM(CASE WHEN jenis = 'keluar' THEN jumlah ELSE 0 END), 0) as total_keluar,
    COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE -jumlah END), 0) as saldo
    FROM kas";
$resultSaldo = $conn->query($querySaldo);
$saldo = $resultSaldo->fetch_assoc();

// Ambil data untuk grafik kas per bulan (6 bulan terakhir)
$queryGrafik = "SELECT 
    DATE_FORMAT(tanggal, '%Y-%m') as bulan,
    SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE 0 END) as total_masuk,
    SUM(CASE WHEN jenis = 'keluar' THEN jumlah ELSE 0 END) as total_keluar
    FROM kas
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
    ORDER BY bulan ASC";
$resultGrafik = $conn->query($queryGrafik);

$labels = [];
$datamasuk = [];
$datakeluar = [];

while ($row = $resultGrafik->fetch_assoc()) {
    $bulan = date('M Y', strtotime($row['bulan'] . '-01'));
    $labels[] = $bulan;
    $datamasuk[] = $row['total_masuk'];
    $datakeluar[] = $row['total_keluar'];
}

// Ambil data jadwal arisan terdekat
$queryArisan = "SELECT a.*, ang.nama as nama_penerima 
                FROM arisan a 
                LEFT JOIN anggota ang ON a.id_penerima = ang.id 
                WHERE a.tanggal >= CURDATE() 
                ORDER BY a.tanggal ASC 
                LIMIT 3";
$resultArisan = $conn->query($queryArisan);

// Ambil info arisan terakhir
$queryArisanTerakhir = "SELECT a.*, ang.nama as nama_penerima 
                        FROM arisan a 
                        LEFT JOIN anggota ang ON a.id_penerima = ang.id 
                        ORDER BY a.tanggal DESC 
                        LIMIT 1";
$resultArisanTerakhir = $conn->query($queryArisanTerakhir);
$arisanTerakhir = $resultArisanTerakhir->num_rows > 0 ? $resultArisanTerakhir->fetch_assoc() : null;

// Ambil data anggota aktif
$queryAnggotaAktif = "SELECT COUNT(*) as total FROM anggota WHERE status = 'Aktif'";
$resultAnggotaAktif = $conn->query($queryAnggotaAktif);
$jumlahAnggotaAktif = $resultAnggotaAktif->fetch_assoc()['total'];

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Tambahkan CSS untuk tampilan mewah -->
<style>
    /* Font Upgrade */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    .content-wrapper {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fc;
    }
    
    .dashboard-title {
        font-weight: 600;
        color: #2c3e50;
        letter-spacing: 0.5px;
        border-left: 4px solid #3498db;
        padding-left: 15px;
    }
    
    /* Card Styling */
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        margin-bottom: 25px;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 15px 20px;
    }
    
    .card-title {
        font-weight: 600;
        font-size: 16px;
        color: #2c3e50;
        margin-bottom: 0;
    }
    
    .card-title i {
        color: #3498db;
        margin-right: 8px;
    }
    
    /* Info Box Styling */
    .info-box {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .info-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .info-box-icon {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        border-radius: 10px 0 0 10px;
    }
    
    .info-box-content {
        padding: 15px;
    }
    
    .info-box-text {
        font-size: 14px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-box-number {
        font-size: 24px;
        font-weight: 600;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    
    .progress {
        height: 5px;
        margin-bottom: 5px;
        border-radius: 5px;
        background-color: rgba(255, 255, 255, 0.3);
    }
    
    .progress-bar {
        border-radius: 5px;
    }
    
    .progress-description {
        font-size: 12px;
        opacity: 0.8;
    }
    
    /* Perbaikan untuk link Lihat Detail */
    .small-box-footer {
        color: rgba(255, 255, 255, 0.9) !important;
        background: rgba(0, 0, 0, 0.1);
        display: block;
        padding: 5px 0;
        text-align: center;
        font-weight: 500;
        margin-top: 10px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    
    .small-box-footer:hover {
        color: #fff !important;
        background: rgba(0, 0, 0, 0.2);
        text-decoration: none;
    }
    
    .small-box-footer i {
        margin-left: 5px;
        transition: transform 0.3s ease;
    }
    
    .small-box-footer:hover i {
        transform: translateX(3px);
    }
    
    /* Table Styling */
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        border-top: none;
        border-bottom: 2px solid #e9ecef;
        font-weight: 600;
        color: #2c3e50;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }
    
    .table td {
        vertical-align: middle;
        padding: 12px 15px;
        border-color: #f1f1f1;
    }
    
    .badge {
        padding: 5px 10px;
        font-weight: 500;
        border-radius: 5px;
    }
    
    /* Button Styling */
    .btn {
        border-radius: 5px;
        font-weight: 500;
        padding: 8px 15px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        border: none;
    }
    
    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: none;
    }
    
    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animated-card {
        animation: fadeIn 0.5s ease-out forwards;
    }
    
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
    .delay-4 { animation-delay: 0.4s; }
    
    /* Custom Colors */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    }
    
    .bg-gradient-danger {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #f39c12 0%, #d35400 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .info-box-icon {
            width: 60px;
            height: 60px;
            font-size: 24px;
        }
        
        .info-box-number {
            font-size: 20px;
        }
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h1 class="m-0 dashboard-title">Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="info-box bg-gradient-primary animated-card delay-1">
                        <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pemasukan</span>
                            <span class="info-box-number">Rp <?= number_format($saldo['total_masuk'], 0, ',', '.') ?></span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <a href="../kas/index.php?jenis=masuk" class="small-box-footer">
                                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="info-box bg-gradient-danger animated-card delay-2">
                        <span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pengeluaran</span>
                            <span class="info-box-number">Rp <?= number_format($saldo['total_keluar'], 0, ',', '.') ?></span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <a href="../kas/index.php?jenis=keluar" class="small-box-footer">
                                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="info-box bg-gradient-success animated-card delay-3">
                        <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Saldo Saat Ini</span>
                            <span class="info-box-number">Rp <?= number_format($saldo['saldo'], 0, ',', '.') ?></span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <a href="../kas/index.php" class="small-box-footer">
                                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Grafik Pemasukan & Pengeluaran -->
                <div class="col-12">
                    <div class="card animated-card delay-1">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line"></i>
                                Grafik Pemasukan & Pengeluaran (6 Bulan Terakhir)
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="grafikKas" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Jadwal Arisan Terdekat -->
                <div class="col-lg-6 col-md-12">
                    <div class="card animated-card delay-2">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt"></i>
                                Jadwal Arisan Terdekat
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Penerima</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($resultArisan->num_rows > 0): ?>
                                        <?php while ($arisan = $resultArisan->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= date('d-m-Y', strtotime($arisan['tanggal'])) ?></td>
                                                <td><?= $arisan['nama_penerima'] ?></td>
                                                <td>
                                                    <?php
                                                    $today = date('Y-m-d');
                                                    if ($arisan['tanggal'] < $today) {
                                                        echo '<span class="badge bg-gradient-success">Selesai</span>';
                                                    } elseif ($arisan['tanggal'] == $today) {
                                                        echo '<span class="badge bg-gradient-warning">Hari Ini</span>';
                                                    } else {
                                                        echo '<span class="badge bg-gradient-info">Akan Datang</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="../arisan/detail.php?id=<?= $arisan['id'] ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Tidak ada jadwal arisan terdekat</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Info Arisan Terakhir -->
                <div class="col-lg-6 col-md-12">
                    <div class="card animated-card delay-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i>
                                Info Arisan Terakhir
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if ($arisanTerakhir): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-box bg-gradient-warning">
                                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Jumlah Hadir</span>
                                                <span class="info-box-number"><?= $arisanTerakhir['jumlah_hadir'] ?> orang</span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: <?= ($arisanTerakhir['jumlah_hadir'] / $jumlahAnggotaAktif) * 100 ?>%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    Dari <?= $jumlahAnggotaAktif ?> anggota aktif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box bg-gradient-primary">
                                            <span class="info-box-icon"><i class="fas fa-user"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Penerima</span>
                                                <span class="info-box-number"><?= $arisanTerakhir['nama_penerima'] ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    <?= date('d-m-Y', strtotime($arisanTerakhir['tanggal'])) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="info-box bg-gradient-success">
                                            <span class="info-box-icon"><i class="fas fa-cubes"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Jumlah Gula</span>
                                                <span class="info-box-number"><?= number_format($arisanTerakhir['jumlah_gula'], 2) ?> kg</span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    <?= number_format($arisanTerakhir['gula_per_anggota'], 2) ?> kg per anggota
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box bg-gradient-info">
                                            <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Jumlah Uang</span>
                                                <span class="info-box-number">Rp <?= number_format($arisanTerakhir['jumlah_uang'], 0, ',', '.') ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    Rp <?= number_format($arisanTerakhir['uang_per_anggota'], 0, ',', '.') ?> per anggota
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="../arisan/detail.php?id=<?= $arisanTerakhir['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                    <p>Belum ada data arisan</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan Chart.js sudah dimuat
    if (typeof Chart === 'undefined') {
        console.error('Chart.js tidak ditemukan. Pastikan library Chart.js sudah dimuat.');
        
        // Tambahkan Chart.js jika belum ada
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js';
        script.onload = function() {
            initializeChart();
        };
        document.head.appendChild(script);
    } else {
        initializeChart();
    }
    
    function initializeChart() {
        // Ambil data dari PHP
        var labels = <?= json_encode($labels) ?>;
        var datamasuk = <?= json_encode($datamasuk) ?>;
        var datakeluar = <?= json_encode($datakeluar) ?>;
        
        // Pastikan data tidak kosong
        if (labels.length === 0) {
            labels = ['Jan 2023', 'Feb 2023', 'Mar 2023', 'Apr 2023', 'Mei 2023', 'Jun 2023'];
            datamasuk = [0, 0, 0, 0, 0, 0];
            datakeluar = [0, 0, 0, 0, 0, 0];
            
            // Tambahkan pesan jika tidak ada data
            var chartContainer = document.getElementById('grafikKas').parentNode;
            var noDataMsg = document.createElement('div');
            noDataMsg.className = 'text-center text-muted mt-3';
            noDataMsg.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Belum ada data transaksi dalam 6 bulan terakhir';
            chartContainer.appendChild(noDataMsg);
        }
        
        var ctx = document.getElementById('grafikKas').getContext('2d');
        
        // Gradient untuk pemasukan
        var gradientIncome = ctx.createLinearGradient(0, 0, 0, 250);
        gradientIncome.addColorStop(0, 'rgba(52, 152, 219, 0.7)');
        gradientIncome.addColorStop(1, 'rgba(52, 152, 219, 0.1)');
        
        // Gradient untuk pengeluaran
        var gradientExpense = ctx.createLinearGradient(0, 0, 0, 250);
        gradientExpense.addColorStop(0, 'rgba(231, 76, 60, 0.7)');
        gradientExpense.addColorStop(1, 'rgba(231, 76, 60, 0.1)');
        
        // Buat grafik dengan opsi yang lebih baik
        var grafikKas = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pemasukan',
                        backgroundColor: gradientIncome,
                        borderColor: '#3498db',
                        pointBackgroundColor: '#3498db',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#3498db',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 3,
                        fill: true,
                        data: datamasuk,
                        lineTension: 0.4
                    },
                    {
                        label: 'Pengeluaran',
                        backgroundColor: gradientExpense,
                        borderColor: '#e74c3c',
                        pointBackgroundColor: '#e74c3c',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#e74c3c',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 3,
                        fill: true,
                        data: datakeluar,
                        lineTension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#6c757d',
                            fontFamily: "'Poppins', sans-serif",
                            padding: 10,
                            callback: function(value) {
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        },
                        gridLines: {
                            drawTicks: false,
                            display: true,
                            color: "rgba(0, 0, 0, 0.05)",
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            zeroLineColor: "transparent",
                            display: true,
                            color: "rgba(0, 0, 0, 0.05)",
                        },
                        ticks: {
                            fontColor: '#6c757d',
                            fontFamily: "'Poppins', sans-serif",
                            padding: 10
                        }
                    }]
                },
                tooltips: {
                    backgroundColor: 'rgba(47, 53, 66, 0.8)',
                    titleFontFamily: "'Poppins', sans-serif",
                    titleFontSize: 14,
                    titleFontStyle: 'bold',
                    titleFontColor: '#fff',
                    bodyFontFamily: "'Poppins', sans-serif",
                    bodyFontSize: 13,
                    bodyFontColor: '#fff',
                    borderWidth: 0,
                    cornerRadius: 8,
                    xPadding: 12,
                    yPadding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ': Rp ' + 
                                   tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                },
                legend: {
                    position: 'top',
                    labels: {
                        fontFamily: "'Poppins', sans-serif",
                        fontColor: '#2c3e50',
                        fontSize: 13,
                        usePointStyle: true,
                        padding: 20
                    }
                }
            }
        });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
