<?php
date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "gaks_doyan";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', 'http://localhost/gaks-doyan/');
define('NAMA_BRAND', "Ga'ks Doyan");

function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Alamat gambar + versi file, supaya browser selalu memuat gambar terbaru
function aset($path) {
    $file = __DIR__ . '/../' . $path;
    $v = is_file($file) ? filemtime($file) : 0;
    return BASE_URL . $path . '?v=' . $v;
}
?>