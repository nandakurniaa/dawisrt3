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

// Ambil data arisan berdasarkan id
$query = "SELECT a.*, b.nama as nama_penerima, b.alamat, b.no_hp 
          FROM arisan a 
          LEFT JOIN anggota b ON a.id_penerima = b.id 
          WHERE a.id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Data arisan tidak ditemukan!";
    header("Location: index.php");
    exit;
}

$arisan = $result->fetch_assoc();

// Ambil data anggota aktif
$queryAnggota = "SELECT * FROM anggota WHERE status = 'Aktif' ORDER BY nama ASC";
$resultAnggota = $conn->query($queryAnggota);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Data Arisan - Sistem DAWIS</title>
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
        .info-table th {
            width: 200px;
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
            <h3>LAPORAN DATA ARISAN</h3>
            <p>Tanggal Cetak: <?php echo date('d-m-Y H:i:s'); ?></p>
        </div>
        
        <h4>INFORMASI ARISAN</h4>
        <table class="info-table">
            <tr>
                <th>Tanggal Arisan</th>
                <td><?php echo date('d-m-Y', strtotime($arisan['tanggal'])); ?></td>
            </tr>
            <tr>
                <th>Penerima</th>
                <td><?php echo $arisan['nama_penerima']; ?></td>
            </tr>
            <tr>
                <th>Alamat Penerima</th>
                <td><?php echo $arisan['alamat']; ?></td>
            </tr>
            <tr>
                <th>No. HP Penerima</th>
                <td><?php echo $arisan['no_hp']; ?></td>
            </tr>
            <tr>
                <th>Gula per Anggota</th>
                <td><?php echo number_format($arisan['gula_per_anggota'], 1); ?> kg</td>
            </tr>
            <tr>
                <th>Uang per Anggota</th>
                <td>Rp <?php echo number_format($arisan['uang_per_anggota'], 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <th>Total Gula Diterima</th>
                <td><?php echo number_format($arisan['gula_diterima'], 1); ?> kg</td>
            </tr>
            <tr>
                <th>Total Uang Diterima</th>
                <td>Rp <?php echo number_format($arisan['uang_diterima'], 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <th>Catatan</th>
                <td><?php echo nl2br($arisan['catatan']); ?></td>
            </tr>
        </table>
        
        <h4>DAFTAR ANGGOTA YANG BERPARTISIPASI</h4>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Anggota</th>
                    <th width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($anggota = $resultAnggota->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $anggota['nama']; ?></td>
                    <td>Aktif</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Tanggal: <?php echo date('d-m-Y'); ?></p>
            <div class="signature">
                <p>Ketua RT 3</p>
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