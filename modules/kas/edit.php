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

// Ambil data kas berdasarkan id
$query = "SELECT * FROM kas WHERE id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Data transaksi kas tidak ditemukan!";
    header("Location: index.php");
    exit;
}

$kas = $result->fetch_assoc();

// Proses form edit transaksi kas
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal = clean($conn, $_POST['tanggal']);
    $jenis = clean($conn, $_POST['jenis']);
    $jumlah = clean($conn, $_POST['jumlah']);
    $keterangan = clean($conn, $_POST['keterangan']);
    
    // Validasi input
    $errors = [];
    
    if (empty($tanggal)) {
        $errors[] = "Tanggal tidak boleh kosong";
    }
    
    if (empty($jenis)) {
        $errors[] = "Jenis transaksi tidak boleh kosong";
    }
    
    if (empty($jumlah) || !is_numeric($jumlah) || $jumlah <= 0) {
        $errors[] = "Jumlah harus berupa angka positif";
    }
    
    if (empty($keterangan)) {
        $errors[] = "Keterangan tidak boleh kosong";
    }
    
    // Jika tidak ada error, update data
    if (empty($errors)) {
        $query = "UPDATE kas SET tanggal = '$tanggal', jenis = '$jenis', jumlah = $jumlah, keterangan = '$keterangan' WHERE id = $id";
        
        if ($conn->query($query)) {
            // Redirect ke halaman daftar kas dengan pesan sukses
            $_SESSION['success'] = "Data transaksi kas berhasil diperbarui!";
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal memperbarui data transaksi kas: " . $conn->error;
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
                    <h1 class="m-0">Edit Transaksi Kas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Kas</a></li>
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
                    <h3 class="card-title">Form Edit Transaksi Kas</h3>
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
                            <label for="tanggal">Tanggal Transaksi</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo isset($_POST['tanggal']) ? $_POST['tanggal'] : $kas['tanggal']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="jenis">Jenis Transaksi</label>
                            <select class="form-control" id="jenis" name="jenis" required>
                                <option value="">-- Pilih Jenis Transaksi --</option>
                                <option value="masuk" <?php echo (isset($_POST['jenis']) && $_POST['jenis'] == 'masuk') || (!isset($_POST['jenis']) && $kas['jenis'] == 'masuk') ? 'selected' : ''; ?>>Kas Masuk (Pemasukan)</option>
                                <option value="keluar" <?php echo (isset($_POST['jenis']) && $_POST['jenis'] == 'keluar') || (!isset($_POST['jenis']) && $kas['jenis'] == 'keluar') ? 'selected' : ''; ?>>Kas Keluar (Pengeluaran)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jumlah">Jumlah (Rp)</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" value="<?php echo isset($_POST['jumlah']) ? $_POST['jumlah'] : $kas['jumlah']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" required><?php echo isset($_POST['keterangan']) ? $_POST['keterangan'] : $kas['keterangan']; ?></textarea>
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