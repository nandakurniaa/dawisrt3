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

// Ambil data anggota berdasarkan id
$query = "SELECT * FROM anggota WHERE id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Anggota tidak ditemukan!";
    header("Location: index.php");
    exit;
}

$anggota = $result->fetch_assoc();

// Proses form edit anggota
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = clean($conn, $_POST['nama']);
    $jabatan = clean($conn, $_POST['jabatan']);
    $no_hp = clean($conn, $_POST['no_hp']);
    $status = clean($conn, $_POST['status']);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = "Nama tidak boleh kosong";
    }
    
    if (empty($jabatan)) {
        $errors[] = "jabatan tidak boleh kosong";
    }
    
    if (empty($no_hp)) {
        $errors[] = "Nomor HP tidak boleh kosong";
    }
    
    // Jika tidak ada error, update data
    if (empty($errors)) {
        $query = "UPDATE anggota SET nama = '$nama', jabatan = '$jabatan', no_hp = '$no_hp', status = '$status' WHERE id = $id";
        
        if ($conn->query($query)) {
            // Redirect ke halaman daftar anggota dengan pesan sukses
            $_SESSION['success'] = "Data anggota berhasil diperbarui!";
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal memperbarui data anggota: " . $conn->error;
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
                    <h1 class="m-0">Edit Anggota</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Anggota</a></li>
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
                    <h3 class="card-title">Form Edit Anggota</h3>
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
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?php echo isset($_POST['nama']) ? $_POST['nama'] : $anggota['nama']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="jabatan">Jabatan</label>
                            <input type="text" class="form-control" id="jabatan" name="jabatan" value="<?php echo isset($_POST['jabatan']) ? $_POST['jabatan'] : $anggota['jabatan']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="no_hp">Nomor HP</label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?php echo isset($_POST['no_hp']) ? $_POST['no_hp'] : $anggota['no_hp']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="Aktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Aktif') || (!isset($_POST['status']) && $anggota['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="Tidak Aktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Tidak Aktif') || (!isset($_POST['status']) && $anggota['status'] == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
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