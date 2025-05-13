<?php
// Cek session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Ambil ID piknik
$id_piknik = isset($_GET['id']) ? clean($conn, $_GET['id']) : 0;

// Ambil data piknik
$query = "SELECT * FROM piknik WHERE id = $id_piknik";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$piknik = $result->fetch_assoc();
$biaya_per_orang = $piknik['biaya_per_orang'];

// Proses hapus peserta
if (isset($_GET['delete'])) {
    $id_peserta = clean($conn, $_GET['delete']);
    
    $deleteQuery = "DELETE FROM piknik_peserta WHERE id = $id_peserta AND id_piknik = $id_piknik";
    if ($conn->query($deleteQuery)) {
        $success = "Data peserta berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data peserta: " . $conn->error;
    }
}

// Ambil data anggota untuk dropdown
$queryAnggota = "SELECT * FROM anggota WHERE status = 'Aktif' ORDER BY nama ASC";
$resultAnggota = $conn->query($queryAnggota);

// Ambil data peserta piknik
$queryPeserta = "SELECT pp.*, a.nama, a.alamat, a.no_hp 
                FROM piknik_peserta pp 
                LEFT JOIN anggota a ON pp.id_anggota = a.id 
                WHERE pp.id_piknik = $id_piknik
                ORDER BY a.nama ASC";
$resultPeserta = $conn->query($queryPeserta);

// Proses form tambah peserta
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_anggota = clean($conn, $_POST['id_anggota']);
    $jumlah_anggota = clean($conn, $_POST['jumlah_anggota']);
    $keterangan = clean($conn, $_POST['keterangan']);
    
    // Hitung biaya total
    $biaya_total = $piknik['biaya_per_orang'] * $jumlah_anggota;
    
    // Validasi input
    $errors = [];
    
    if (empty($id_anggota)) {
        $errors[] = "Anggota tidak boleh kosong";
    }
    
    if (empty($jumlah_anggota) || !is_numeric($jumlah_anggota) || $jumlah_anggota < 1) {
        $errors[] = "Jumlah anggota harus berupa angka positif";
    }
    
    // Cek apakah anggota sudah terdaftar
    $checkQuery = "SELECT * FROM piknik_peserta WHERE id_piknik = $id_piknik AND id_anggota = $id_anggota";
    $checkResult = $conn->query($checkQuery);
    if ($checkResult->num_rows > 0) {
        $errors[] = "Anggota ini sudah terdaftar sebagai peserta piknik!";
    }
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        $query = "INSERT INTO piknik_peserta (id_piknik, id_anggota, jumlah_anggota, biaya_total, keterangan) 
                  VALUES ($id_piknik, $id_anggota, $jumlah_anggota, $biaya_total, '$keterangan')";
        
        if ($conn->query($query)) {
            $success = "Data peserta berhasil ditambahkan!";
            // Reset form
            unset($_POST);
        } else {
            $error = "Gagal menambahkan data peserta: " . $conn->error;
        }
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Kelola Peserta Piknik</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Piknik</a></li>
                        <li class="breadcrumb-item"><a href="detail.php?id=<?php echo $id_piknik; ?>">Detail</a></li>
                        <li class="breadcrumb-item active">Peserta</li>
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
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informasi Piknik</h3>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th>Nama Kegiatan</th>
                                    <td><?php echo $piknik['nama_kegiatan']; ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td><?php echo date('d-m-Y', strtotime($piknik['tanggal_berangkat'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Tujuan</th>
                                    <td><?php echo $piknik['tujuan']; ?></td>
                                </tr>
                                <tr>
                                    <th>Biaya per Orang</th>
                                    <td>Rp <?php echo number_format($piknik['biaya_per_orang'], 0, ',', '.'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Tambah Peserta</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="id_anggota">Anggota</label>
                                    <select class="form-control select2" id="id_anggota" name="id_anggota" required>
                                        <option value="">-- Pilih Anggota --</option>
                                        <?php 
                                        // Reset pointer ke awal
                                        $resultAnggota->data_seek(0);
                                        while ($anggota = $resultAnggota->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo $anggota['id']; ?>" <?php echo (isset($_POST['id_anggota']) && $_POST['id_anggota'] == $anggota['id']) ? 'selected' : ''; ?>>
                                                <?php echo $anggota['nama']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="jumlah_anggota">Jumlah Anggota</label>
                                    <input type="number" class="form-control" id="jumlah_anggota" name="jumlah_anggota" min="1" value="<?php echo isset($_POST['jumlah_anggota']) ? $_POST['jumlah_anggota'] : '1'; ?>" required>
                                    <small class="form-text text-muted">Termasuk anggota keluarga yang ikut</small>
                                </div>
                                <div class="form-group">
                                    <label>Total Biaya</label>
                                    <input type="text" class="form-control" id="biaya_total" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="keterangan">Keterangan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="2"><?php echo isset($_POST['keterangan']) ? $_POST['keterangan'] : ''; ?></textarea>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Tambah Peserta</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Peserta</h3>
                        </div>
                        <div class="card-body">
                            <table id="dataTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>No. HP</th>
                                        <th>Jumlah Anggota</th>
                                        <th>Biaya Total</th>
                                        <th>Keterangan</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    if ($resultPeserta && $resultPeserta->num_rows > 0) {
                                        while ($peserta = $resultPeserta->fetch_assoc()): 
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $peserta['nama']; ?></td>
                                            <td><?php echo $peserta['alamat']; ?></td>
                                            <td><?php echo $peserta['no_hp']; ?></td>
                                            <td><?php echo $peserta['jumlah_anggota']; ?> orang</td>
                                            <td>Rp <?php echo number_format($peserta['biaya_total'], 0, ',', '.'); ?></td>
                                            <td><?php echo $peserta['keterangan']; ?></td>
                                            <td>
                                                <a href="peserta.php?id=<?php echo $id_piknik; ?>&delete=<?php echo $peserta['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus peserta ini?');">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile;
                                    } else {
                                        echo '<tr><td colspan="8" class="text-center">Belum ada peserta</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
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
    // Initialize DataTable
    $("#dataTable").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');
    
    // Initialize Select2
    $('.select2').select2();
    
    // Calculate total cost when quantity changes
    $('#jumlah_anggota').on('change keyup', function() {
        var jumlah = $(this).val();
        var biaya = <?php echo $piknik['biaya_per_orang']; ?>;
        var total = jumlah * biaya;
        $('#biaya_total').val('Rp ' + total.toLocaleString('id-ID'));
    });
    
    // Trigger calculation on page load
    $('#jumlah_anggota').trigger('change');
});
</script>