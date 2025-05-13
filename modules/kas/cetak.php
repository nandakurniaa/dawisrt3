<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Filter berdasarkan bulan dan tahun
$bulan = isset($_GET['bulan']) ? clean($conn, $_GET['bulan']) : date('m');
$tahun = isset($_GET['tahun']) ? clean($conn, $_GET['tahun']) : date('Y');

// Ambil data kas
$query = "SELECT * FROM kas WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun' ORDER BY tanggal ASC, id ASC";
$result = $conn->query($query);

// Hitung total saldo
$querySaldo = "SELECT 
                SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE 0 END) as total_masuk,
                SUM(CASE WHEN jenis = 'keluar' THEN jumlah ELSE 0 END) as total_keluar
              FROM kas WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'";
$resultSaldo = $conn->query($querySaldo);
$saldo = $resultSaldo->fetch_assoc();
$totalMasuk = $saldo['total_masuk'];
$totalKeluar = $saldo['total_keluar'];

// Hitung saldo awal bulan
$queryAwal = "SELECT 
                SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE 0 END) as total_masuk,
                SUM(CASE WHEN jenis = 'keluar' THEN jumlah ELSE 0 END) as total_keluar
              FROM kas 
              WHERE (YEAR(tanggal) < '$tahun' OR (YEAR(tanggal) = '$tahun' AND MONTH(tanggal) < '$bulan'))";
$resultAwal = $conn->query($queryAwal);
$saldoAwal = $resultAwal->fetch_assoc();
$saldoAwalBulan = $saldoAwal['total_masuk'] - $saldoAwal['total_keluar'];

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
    <title>Cetak Laporan Kas - Sistem DAWIS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2, .header h3 {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        .signature {
            margin-top: 80px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .container {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <h2>SISTEM INFORMASI DAWIS RT 3</h2>
            <h3>LAPORAN KAS BULAN <?php echo strtoupper($namaBulan[$bulan]) . ' ' . $tahun; ?></h3>
            <p>Tanggal Cetak: <?php echo date('d-m-Y H:i:s'); ?></p>
        </div>
        
        <h4>LAPORAN KAS</h4>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th>Keterangan</th>
                    <th width="15%">Kas Masuk</th>
                    <th width="15%">Kas Keluar</th>
                    <th width="15%">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-right"><strong>Saldo Awal Bulan</strong></td>
                    <td class="text-right"><strong>Rp <?php echo number_format($saldoAwalBulan, 0, ',', '.'); ?></strong></td>
                </tr>
                <?php 
                $no = 1;
                $saldoBerjalan = $saldoAwalBulan;
                while ($row = $result->fetch_assoc()): 
                    if ($row['jenis'] == 'masuk') {
                        $saldoBerjalan += $row['jumlah'];
                    } else {
                        $saldoBerjalan -= $row['jumlah'];
                    }
                ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                    <td><?php echo $row['keterangan']; ?></td>
                    <td class="text-right">
                        <?php if ($row['jenis'] == 'masuk'): ?>
                            Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <?php if ($row['jenis'] == 'keluar'): ?>
                            Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="text-right">Rp <?php echo number_format($saldoBerjalan, 0, ',', '.'); ?></td>
                </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="3" class="text-right"><strong>Total</strong></td>
                    <td class="text-right"><strong>Rp <?php echo number_format($totalMasuk, 0, ',', '.'); ?></strong></td>
                    <td class="text-right"><strong>Rp <?php echo number_format($totalKeluar, 0, ',', '.'); ?></strong></td>
                    <td class="text-right"><strong>Rp <?php echo number_format($saldoAwalBulan + $totalMasuk - $totalKeluar, 0, ',', '.'); ?></strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Tanggal: <?php echo date('d-m-Y'); ?></p>
            <div class="signature">
                <p>Bendahara RT 3</p>
                <br><br><br>
                <p>(_________________)</p>
            </div>
        </div>
        
        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()">Cetak</button>
            <button onclick="window.close()">Tutup</button>
        </div>
    </div>
</body>
</html>