<?php
// Konfigurasi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'dawis_db';

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset ke utf8
$conn->set_charset("utf8");

// HAPUS fungsi clean() dari sini karena sudah ada di functions.php

// Fungsi lain yang mungkin diperlukan bisa tetap di sini
?>