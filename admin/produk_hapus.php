<?php
include '../config/koneksi.php';
include '../includes/auth.php';
wajib_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    $s = mysqli_prepare($koneksi, "SELECT gambar FROM produk WHERE id = ?");
    mysqli_stmt_bind_param($s, "i", $id);
    mysqli_stmt_execute($s);
    $p = mysqli_fetch_assoc(mysqli_stmt_get_result($s));

    if ($p) {
        $d = mysqli_prepare($koneksi, "DELETE FROM produk WHERE id = ?");
        mysqli_stmt_bind_param($d, "i", $id);
        mysqli_stmt_execute($d);

        if ($p['gambar'] !== 'default.jpg') {
            @unlink(__DIR__ . '/../assets/img/produk/' . $p['gambar']);
        }
        $_SESSION['flash'] = ['tipe' => 'ok', 'pesan' => 'Produk berhasil dihapus.'];
    }
}

header("Location: produk.php");
exit;
?>