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

// Proses form tambah piknik
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kegiatan = clean($conn, $_POST['nama_kegiatan']);
    $tanggal_berangkat = clean($conn, $_POST['tanggal_berangkat']);
    $tanggal_pulang = clean($conn, $_POST['tanggal_pulang']);
    $tujuan = clean($conn, $_POST['tujuan']);
    $biaya_per_orang = clean($conn, $_POST['biaya_per_orang']);
    $keterangan = clean($conn, $_POST['keterangan']);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama_kegiatan)) {
        $errors[] = "Nama kegiatan tidak boleh kosong";
    }
    
    if (empty($tanggal_berangkat)) {
        $errors[] = "Tanggal berangkat tidak boleh kosong";
    }
    
    if (empty($tujuan)) {
        $errors[] = "Tujuan tidak boleh kosong";
    }
    
    if (empty($biaya_per_orang) || !is_numeric($biaya_per_orang)) {
        $errors[] = "Biaya per orang harus berupa angka";
    }
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        $query = "INSERT INTO piknik (nama_kegiatan, tanggal_berangkat, tanggal_pulang, tujuan, biaya_per_orang, keterangan) 
                  VALUES ('$nama_kegiatan', '$tanggal_berangkat', '$tanggal_pulang', '$tujuan', $biaya_per_orang, '$keterangan')";
        
        if ($conn->query($query)) {
            // Redirect ke halaman daftar piknik dengan pesan sukses
            $_SESSION['success'] = "Data piknik berhasil ditambahkan!";
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal menambahkan data piknik: " . $conn->error;
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
                    <h1 class="m-0">Tambah Piknik / Kegiatan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Piknik</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Tambah Piknik / Kegiatan</h3>
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
                            <label for="nama_kegiatan">Nama Kegiatan</label>
                            <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" value="<?php echo isset($_POST['nama_kegiatan']) ? $_POST['nama_kegiatan'] : ''; ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_berangkat">Tanggal Berangkat</label>
                                    <input type="date" class="form-control" id="tanggal_berangkat" name="tanggal_berangkat" value="<?php echo isset($_POST['tanggal_berangkat']) ? $_POST['tanggal_berangkat'] : date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_pulang">Tanggal Pulang</label>
                                    <input type="date" class="form-control" id="tanggal_pulang" name="tanggal_pulang" value="<?php echo isset($_POST['tanggal_pulang']) ? $_POST['tanggal_pulang'] : date('Y-m-d', strtotime('+1 day')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tujuan">Tujuan</label>
                            <input type="text" class="form-control" id="tujuan" name="tujuan" value="<?php echo isset($_POST['tujuan']) ? $_POST['tujuan'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="biaya_per_orang">Biaya per Orang (Rp)</label>
                            <input type="number" class="form-control" id="biaya_per_orang" name="biaya_per_orang" value="<?php echo isset($_POST['biaya_per_orang']) ? $_POST['biaya_per_orang'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?php echo isset($_POST['keterangan']) ? $_POST['keterangan'] : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../../includes/footer.php'; ?>