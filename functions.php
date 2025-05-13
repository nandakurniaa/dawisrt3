<?php
/**
 * File: functions.php
 * Berisi fungsi-fungsi umum yang digunakan di seluruh aplikasi
 */

/**
 * Membersihkan input dari karakter berbahaya
 * 
 * @param object $conn Koneksi database
 * @param string $data Data yang akan dibersihkan
 * @return string Data yang sudah dibersihkan
 */
function clean($conn, $data) {
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

/**
 * Menghasilkan format tanggal Indonesia
 * 
 * @param string $date Tanggal dalam format Y-m-d
 * @return string Tanggal dalam format d Bulan Tahun
 */
function tanggal_indo($date) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecah = explode('-', $date);
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

/**
 * Menghasilkan format rupiah
 * 
 * @param int $angka Angka yang akan diformat
 * @return string Angka dalam format rupiah
 */
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Menghasilkan alert bootstrap
 * 
 * @param string $type Tipe alert (success, danger, warning, info)
 * @param string $message Pesan yang akan ditampilkan
 * @return string HTML alert
 */
function alert($type, $message) {
    return '<div class="alert alert-' . $type . ' alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-' . ($type == 'success' ? 'check' : 'ban') . '"></i> ' . 
                ($type == 'success' ? 'Sukses!' : 'Error!') . '</h5>
                ' . $message . '
            </div>';
}

// Fungsi untuk mendapatkan saldo terakhir
function getSaldoTerakhir($conn) {
    $query = "SELECT saldo FROM kas ORDER BY id DESC LIMIT 1";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['saldo'];
    } else {
        return 0;
    }
}

// Fungsi untuk mendapatkan jumlah anggota aktif
function getJumlahAnggotaAktif($conn) {
    $query = "SELECT COUNT(*) as total FROM anggota WHERE status = 'Aktif'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Fungsi untuk menghitung total gula dan uang untuk arisan
function hitungTotalArisan($conn) {
    $jumlahAnggota = getJumlahAnggotaAktif($conn);
    $totalGula = $jumlahAnggota * 0.5; // 0.5 kg per anggota
    $totalUang = $jumlahAnggota * 10000; // Rp 10.000 per anggota
    
    return [
        'gula' => $totalGula,
        'uang' => $totalUang
    ];
}
?>