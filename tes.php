<?php
include 'config/koneksi.php';
echo "<h2>Koneksi berhasil!</h2>";

$q = mysqli_query($koneksi, "SELECT * FROM produk");
echo "<ul>";
while ($p = mysqli_fetch_assoc($q)) {
    echo "<li>{$p['nama']} - " . rupiah($p['harga']) . "</li>";
}
echo "</ul>";
?>