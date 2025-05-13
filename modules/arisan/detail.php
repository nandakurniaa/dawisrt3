<?php
// Cek session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Ambil ID arisan
$id = isset($_GET['id']) ? clean($conn, $_GET['id']) : 0;

// Ambil data arisan
$query = "SELECT a.*, angg.nama as nama_penerima 
          FROM arisan a 
          JOIN anggota angg ON a.id_penerima = angg.id 
          WHERE a.id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$arisan = $result->fetch_assoc();

// Hitung jumlah anggota aktif
$queryAnggota = "SELECT COUNT(*) as jumlah FROM anggota WHERE status = 'Aktif'";
$resultAnggota = $conn->query($queryAnggota);
$jumlahAnggota = $resultAnggota->fetch_assoc()['jumlah'];

// Hitung total gula dan uang
$totalGula = $jumlahAnggota * 0.5; // 0.5 kg per anggota
$totalUang = $jumlahAnggota * 10000; // Rp 10.000 per anggota

// Ambil data setoran anggota
$querySetoran = "SELECT s.*, a.nama, a.no_hp 
                FROM arisan_setoran s 
                JOIN anggota a ON s.id_anggota = a.id 
                WHERE s.id_arisan = $id 
                ORDER BY a.nama ASC";
$resultSetoran = $conn->query($querySetoran);

// Proses checklist setoran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_anggota'])) {
    $id_anggota = clean($conn, $_POST['id_anggota']);
    $status_gula = isset($_POST['status_gula']) ? 1 : 0;
    $status_uang = isset($_POST['status_uang']) ? 1 : 0;
    
    // Cek apakah sudah ada data setoran
    $checkQuery = "SELECT id FROM arisan_setoran WHERE id_arisan = $id AND id_anggota = $id_anggota";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        // Update data yang sudah ada
        $setoranId = $checkResult->fetch_assoc()['id'];
        $updateQuery = "UPDATE arisan_setoran SET status_gula = $status_gula, status_uang = $status_uang WHERE id = $setoranId";
        $conn->query($updateQuery);
    } else {
        // Insert data baru
        $insertQuery = "INSERT INTO arisan_setoran (id_arisan, id_anggota, status_gula, status_uang) VALUES ($id, $id_anggota, $status_gula, $status_uang)";
        $conn->query($insertQuery);
    }
    
    // Hitung ulang jumlah hadir dan update
    $queryHadir = "SELECT COUNT(*) as hadir FROM arisan_setoran WHERE id_arisan = $id AND (status_gula = 1 OR status_uang = 1)";
    $resultHadir = $conn->query($queryHadir);
    $jumlahHadir = $resultHadir->fetch_assoc()['hadir'];
    
    // Update jumlah_hadir di tabel arisan
    $updateHadirQuery = "UPDATE arisan SET jumlah_hadir = $jumlahHadir WHERE id = $id";
    $conn->query($updateHadirQuery);
    
    // Redirect untuk refresh data
    header("Location: detail.php?id=$id");
    exit;
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detail Arisan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Arisan</a></li>
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
                            <h3 class="card-title">Informasi Arisan</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 200px">Tanggal</th>
                                    <td><?php echo date('d F Y', strtotime($arisan['tanggal'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Penerima</th>
                                    <td><?php echo $arisan['nama_penerima']; ?></td>
                                </tr>
                                <tr>
                                    <th>Lokasi</th>
                                    <td><?php echo isset($arisan['lokasi']) ? $arisan['lokasi'] : '-'; ?></td>
                                </tr>
                                <tr>
                                    <th>Keterangan</th>
                                    <td><?php echo isset($arisan['keterangan']) ? nl2br($arisan['keterangan']) : (isset($arisan['catatan']) ? nl2br($arisan['catatan']) : '-'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ringkasan Arisan</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-gradient-success">
                                        <span class="info-box-icon"><i class="fas fa-cubes"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Gula</span>
                                            <span class="info-box-number"><?php echo $totalGula; ?> kg</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                            <span class="progress-description">
                                                0,5 kg per anggota
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box bg-gradient-info">
                                        <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Uang</span>
                                            <span class="info-box-number">Rp <?php echo number_format($totalUang, 0, ',', '.'); ?></span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                            <span class="progress-description">
                                                Rp 10.000 per anggota
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <a href="cetak.php?id=<?php echo $id; ?>" class="btn btn-primary" target="_blank">
                                    <i class="fas fa-print"></i> Cetak Laporan
                                </a>
                                <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Setoran Anggota</h3>
                </div>
                <div class="card-body">
                    <table id="dataTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Anggota</th>
                                <th>No. HP</th>
                                <th class="text-center">Gula (0,5 kg)</th>
                                <th class="text-center">Uang (Rp 10.000)</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Ambil semua anggota aktif
                            $queryAllAnggota = "SELECT id, nama, no_hp FROM anggota WHERE status = 'Aktif' ORDER BY nama ASC";
                            $resultAllAnggota = $conn->query($queryAllAnggota);
                            
                            // Buat array untuk menyimpan data setoran
                            $setoran = [];
                            while ($row = $resultSetoran->fetch_assoc()) {
                                $setoran[$row['id_anggota']] = $row;
                            }
                            
                            $no = 1;
                            while ($anggota = $resultAllAnggota->fetch_assoc()): 
                                $id_anggota = $anggota['id'];
                                $status_gula = isset($setoran[$id_anggota]) ? $setoran[$id_anggota]['status_gula'] : 0;
                                $status_uang = isset($setoran[$id_anggota]) ? $setoran[$id_anggota]['status_uang'] : 0;
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $anggota['nama']; ?></td>
                                <td><?php echo $anggota['no_hp']; ?></td>
                                <td class="text-center">
                                    <?php if ($status_gula): ?>
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Sudah</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fas fa-times"></i> Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($status_uang): ?>
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Sudah</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fas fa-times"></i> Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-setoran-<?php echo $id_anggota; ?>">
                                        <i class="fas fa-edit"></i> Update
                                    </button>
                                    
                                    <!-- Modal Update Setoran -->
                                    <div class="modal fade" id="modal-setoran-<?php echo $id_anggota; ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Update Setoran: <?php echo $anggota['nama']; ?></h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="post" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_anggota" value="<?php echo $id_anggota; ?>">
                                                        
                                                        <div class="form-group">
                                                            <div class="custom-control custom-checkbox">
                                                                <input class="custom-control-input" type="checkbox" id="gula-<?php echo $id_anggota; ?>" name="status_gula" value="1" <?php echo $status_gula ? 'checked' : ''; ?>>
                                                                <label for="gula-<?php echo $id_anggota; ?>" class="custom-control-label">Gula (0,5 kg)</label>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <div class="custom-control custom-checkbox">
                                                                <input class="custom-control-input" type="checkbox" id="uang-<?php echo $id_anggota; ?>" name="status_uang" value="1" <?php echo $status_uang ? 'checked' : ''; ?>>
                                                                <label for="uang-<?php echo $id_anggota; ?>" class="custom-control-label">Uang (Rp 10.000)</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
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
                            <a href="index.php" class="btn btn-secondary">Kembali ke Daftar Arisan</a>
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
