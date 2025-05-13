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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Bulanan <?php echo $namaBulan[$bulan] . ' ' . $tahun; ?> - DAWIS RT 3</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h2 {
            margin-bottom: 5px;
        }
        .header p {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f4f6f9;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                font-size: 12pt;
            }
            .container {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>LAPORAN BULANAN DAWIS RT 3</h2>
            <h3><?php echo $namaBulan[$bulan] . ' ' . $tahun; ?></h3>
            <p>Jl. Contoh No. 123, Kelurahan Contoh, Kecamatan Contoh</p>
        </div>
        
        <div class="section">
            <h4 class="section-title">Ringkasan Keuangan</h4>
            <div class="row">
                <div class="col-md-6">
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
            </div>
        </div>
        
        <div class="section">
            <h4 class="section-title">Kegiatan Piknik</h4>
            <div class="row">
                <div class="col-md-6">
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
            </div>
        </div>
        
        <div class="section">
            <h4 class="section-title">Data Anggota</h4>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Total Anggota</th>
                            <td><?php echo $dataAnggota['total_anggota']; ?> orang</td>
                        </tr>
                        <tr>
                            <th>Anggota Aktif</th>
                            <td><?php echo $dataAnggota['anggota_aktif']; ?> orang</td>
                        </tr>
                        <tr>
                            <th>Anggota Tidak Aktif</th>
                            <td><?php echo $dataAnggota['anggota_tidak_aktif']; ?> orang</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="row">
                <div class="col-md-6">
                    <p>Dicetak pada: <?php echo date('d-m-Y H:i:s'); ?></p>
                </div>
                <div class="col-md-6 text-right">
                    <p>Mengetahui,</p>
                    <br><br><br>
                    <p>Ketua RT 3</p>
                </div>
            </div>
        </div>
        
        <div class="no-print mt-4 text-center">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
            <a href="index.php?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Auto print saat halaman dimuat
            // window.print();
        });
    </script>
</body>
</html>