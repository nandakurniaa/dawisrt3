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
$queryPeserta = "SELECT pp.*, a.nama, a.alamat, a.no_hp 
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Daftar Peserta Piknik - Sistem DAWIS</title>
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
            <h3>DAFTAR PESERTA PIKNIK / KEGIATAN</h3>
            <p>Tanggal Cetak: <?php echo date('d-m-Y H:i:s'); ?></p>
        </div>
        
        <h4>INFORMASI PIKNIK</h4>
        <table class="info-table">
            <tr>
                <th>Nama Kegiatan</th>
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
                <th>Total Peserta</th>
                <td><?php echo $total['total_peserta'] ?? 0; ?> orang</td>
            </tr>
            <tr>
                <th>Total Biaya</th>
                <td>Rp <?php echo number_format($total['total_biaya'] ?? 0, 0, ',', '.'); ?></td>
            </tr>
        </table>
        
        <h4>DAFTAR PESERTA</h4>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>No. HP</th>
                    <th>Jumlah Anggota</th>
                    <th>Biaya per Orang</th>
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
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo $peserta['nama']; ?></td>
                    <td><?php echo $peserta['alamat']; ?></td>
                    <td><?php echo $peserta['no_hp']; ?></td>
                    <td class="text-center"><?php echo $peserta['jumlah_anggota']; ?> orang</td>
                    <td class="text-right">Rp <?php echo number_format($peserta['biaya_total'], 0, ',', '.'); ?></td>
                    <td><?php echo $peserta['keterangan']; ?></td>
                </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="4" class="text-right"><strong>Total</strong></td>
                    <td class="text-center"><strong><?php echo $total['total_peserta'] ?? 0; ?> orang</strong></td>
                    <td class="text-right"><strong>Rp <?php echo number_format($total['total_biaya'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td></td>
                </tr>
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