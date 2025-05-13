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

// Ambil data piknik berdasarkan id
$query = "SELECT * FROM piknik WHERE id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Data piknik tidak ditemukan!";
    header("Location: index.php");
    exit;
}

$piknik = $result->fetch_assoc();

// Proses form edit piknik
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
    
    // Jika tidak ada error, update data
    if (empty($errors)) {
        $query = "UPDATE piknik SET 
                  nama_kegiatan = '$nama_kegiatan', 
                  tanggal_berangkat = '$tanggal_berangkat', 
                  tanggal_pulang = '$tanggal_pulang', 
                  tujuan = '$tujuan', 
                  biaya_per_orang = $biaya_per_orang, 
                  keterangan = '$keterangan' 
                  WHERE id = $id";
        
        if ($conn->query($query)) {
            // Jika biaya per orang berubah, update biaya total peserta
            if ($piknik['biaya_per_orang'] != $biaya_per_orang) {
                $updatePeserta = "UPDATE piknik_peserta 
                                 SET biaya_total = jumlah_anggota * $biaya_per_orang 
                                 WHERE id_piknik = $id";
                $conn->query($updatePeserta);
            }
            
            // Redirect ke halaman detail piknik dengan pesan sukses
            $_SESSION['success'] = "Data piknik berhasil diperbarui!";
            header("Location: detail.php?id=$id");
            exit;
        } else {
            $error = "Gagal memperbarui data piknik: " . $conn->error;
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
                    <h1 class="m-0">Edit Piknik / Kegiatan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Piknik</a></li>
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
                    <h3 class="card-title">Form Edit Piknik / Kegiatan</h3>
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
                            <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" value="<?php echo isset($_POST['nama_kegiatan']) ? $_POST['nama_kegiatan'] : $piknik['nama_kegiatan']; ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_berangkat">Tanggal Berangkat</label>
                                    <input type="date" class="form-control" id="tanggal_berangkat" name="tanggal_berangkat" value="<?php echo isset($_POST['tanggal_berangkat']) ? $_POST['tanggal_berangkat'] : $piknik['tanggal_berangkat']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_pulang">Tanggal Pulang</label>
                                    <input type="date" class="form-control" id="tanggal_pulang" name="tanggal_pulang" value="<?php echo isset($_POST['tanggal_pulang']) ? $_POST['tanggal_pulang'] : $piknik['tanggal_pulang']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tujuan">Tujuan</label>
                            <input type="text" class="form-control" id="tujuan" name="tujuan" value="<?php echo isset($_POST['tujuan']) ? $_POST['tujuan'] : $piknik['tujuan']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="biaya_per_orang">Biaya per Orang (Rp)</label>
                            <input type="number" class="form-control" id="biaya_per_orang" name="biaya_per_orang" value="<?php echo isset($_POST['biaya_per_orang']) ? $_POST['biaya_per_orang'] : $piknik['biaya_per_orang']; ?>" required>
                            <?php if ($conn->query("SELECT COUNT(*) as total FROM piknik_peserta WHERE id_piknik = $id")->fetch_assoc()['total'] > 0): ?>
                            <small class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle"></i> Perubahan biaya per orang akan mengubah biaya total semua peserta yang sudah terdaftar.
                            </small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?php echo isset($_POST['keterangan']) ? $_POST['keterangan'] : $piknik['keterangan']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="detail.php?id=<?php echo $id; ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../../includes/footer.php'; ?>