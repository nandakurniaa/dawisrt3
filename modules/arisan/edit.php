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

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = clean($conn, $_GET['id']);

// Ambil data arisan berdasarkan id
$query = "SELECT * FROM arisan WHERE id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Data arisan tidak ditemukan!";
    header("Location: index.php");
    exit;
}

$arisan = $result->fetch_assoc();

// Ambil data anggota untuk dropdown
$queryAnggota = "SELECT * FROM anggota WHERE status = 'Aktif' ORDER BY nama ASC";
$resultAnggota = $conn->query($queryAnggota);

// Hitung jumlah anggota aktif
$queryJumlahAnggota = "SELECT COUNT(*) as total FROM anggota WHERE status = 'Aktif'";
$resultJumlahAnggota = $conn->query($queryJumlahAnggota);
$jumlahAnggota = $resultJumlahAnggota->fetch_assoc()['total'];

// Proses form edit arisan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal = clean($conn, $_POST['tanggal']);
    $id_penerima = clean($conn, $_POST['id_penerima']);
    $gula_per_anggota = clean($conn, $_POST['gula_per_anggota']);
    $uang_per_anggota = clean($conn, $_POST['uang_per_anggota']);
    $lokasi = clean($conn, $_POST['lokasi']);
    $catatan = isset($_POST['catatan']) ? clean($conn, $_POST['catatan']) : '';
    $jumlah_hadir = isset($_POST['jumlah_hadir']) ? intval($_POST['jumlah_hadir']) : 0;

    // Hitung total gula dan uang
    $jumlah_gula = $gula_per_anggota * $jumlahAnggota;
    $jumlah_uang = $uang_per_anggota * $jumlahAnggota;

    // Validasi input
    $errors = [];
    
    if (empty($tanggal)) {
        $errors[] = "Tanggal tidak boleh kosong";
    }
    
    if (empty($id_penerima)) {
        $errors[] = "Penerima tidak boleh kosong";
    }
    
    if (empty($gula_per_anggota) || !is_numeric($gula_per_anggota)) {
        $errors[] = "Gula per anggota harus berupa angka";
    }
    
    if (empty($uang_per_anggota) || !is_numeric($uang_per_anggota)) {
        $errors[] = "Uang per anggota harus berupa angka";
    }
    
    // Jika tidak ada error, update data
    if (empty($errors)) {
        $query = "UPDATE arisan SET 
                  tanggal = '$tanggal', 
                  id_penerima = $id_penerima, 
                  lokasi = '$lokasi',
                  jumlah_gula = $jumlah_gula, 
                  jumlah_uang = $jumlah_uang, 
                  gula_per_anggota = $gula_per_anggota, 
                  uang_per_anggota = $uang_per_anggota, 
                  catatan = '$catatan',
                  jumlah_hadir = $jumlah_hadir
                  WHERE id = $id";
        
        if ($conn->query($query)) {
            // Redirect ke halaman daftar arisan dengan pesan sukses
            $_SESSION['success'] = "Data arisan berhasil diperbarui!";
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal memperbarui data arisan: " . $conn->error;
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
                    <h1 class="m-0">Edit Arisan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Arisan</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Edit Arisan</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                            <?php echo $error; ?>
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
                    
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="tanggal">Tanggal Arisan</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo isset($_POST['tanggal']) ? $_POST['tanggal'] : $arisan['tanggal']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="id_penerima">Penerima Arisan</label>
                            <select class="form-control select2" id="id_penerima" name="id_penerima" required>
                                <option value="">-- Pilih Penerima --</option>
                                <?php 
                                // Reset pointer ke awal
                                $resultAnggota->data_seek(0);
                                while ($anggota = $resultAnggota->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $anggota['id']; ?>" <?php echo (isset($_POST['id_penerima']) && $_POST['id_penerima'] == $anggota['id']) || (!isset($_POST['id_penerima']) && $arisan['id_penerima'] == $anggota['id']) ? 'selected' : ''; ?>>
                                        <?php echo $anggota['nama']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gula_per_anggota">Gula per Anggota (kg)</label>
                                    <input type="number" step="0.1" class="form-control" id="gula_per_anggota" name="gula_per_anggota" value="<?php echo isset($_POST['gula_per_anggota']) ? $_POST['gula_per_anggota'] : $arisan['gula_per_anggota']; ?>" required>
                                    <small class="form-text text-muted">Jumlah anggota aktif: <?php echo $jumlahAnggota; ?> orang</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="uang_per_anggota">Uang per Anggota (Rp)</label>
                                    <input type="number" class="form-control" id="uang_per_anggota" name="uang_per_anggota" value="<?php echo isset($_POST['uang_per_anggota']) ? $_POST['uang_per_anggota'] : $arisan['uang_per_anggota']; ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Gula yang Diterima</label>
                                    <input type="text" class="form-control" id="total_gula" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Uang yang Diterima</label>
                                    <input type="text" class="form-control" id="total_uang" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="catatan">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3"><?php echo isset($_POST['catatan']) ? $_POST['catatan'] : $arisan['catatan']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="jumlah_hadir">Jumlah Hadir</label>
                            <input type="number" class="form-control" id="jumlah_hadir" name="jumlah_hadir" min="0" max="<?php echo $jumlahAnggota; ?>" value="<?php echo isset($_POST['jumlah_hadir']) ? $_POST['jumlah_hadir'] : $arisan['jumlah_hadir']; ?>" required>
                            <small class="form-text text-muted">Maksimal: <?php echo $jumlahAnggota; ?> orang</small>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
$(function () {
    $('.select2').select2();
    
    // Hitung total gula dan uang
    function hitungTotal() {
        var jumlahAnggota = <?php echo $jumlahAnggota; ?>;
        var gulaPerAnggota = parseFloat($('#gula_per_anggota').val()) || 0;
        var uangPerAnggota = parseFloat($('#uang_per_anggota').val()) || 0;
        
        var totalGula = gulaPerAnggota * jumlahAnggota;
        var totalUang = uangPerAnggota * jumlahAnggota;
        
        $('#total_gula').val(totalGula.toFixed(1) + ' kg');
        $('#total_uang').val('Rp ' + totalUang.toLocaleString('id-ID'));
    }
    
    // Panggil fungsi saat halaman dimuat
    hitungTotal();
    
    // Panggil fungsi saat nilai input berubah
    $('#gula_per_anggota, #uang_per_anggota').on('input', function() {
        hitungTotal();
    });
});
</script>