<?php
include 'config/koneksi.php';
include 'includes/auth.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$pid     = (int)($_POST['produk_id'] ?? 0);
$vid     = (int)($_POST['varian_id'] ?? 0);
$qty     = max(1, min(20, (int)($_POST['qty'] ?? 1)));
$catatan = mb_substr(trim($_POST['catatan'] ?? ''), 0, 150);
$top_post = array_unique(array_map('intval', $_POST['topping'] ?? []));

$s = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE id = ? AND tersedia = 1");
mysqli_stmt_bind_param($s, "i", $pid);
mysqli_stmt_execute($s);
$p = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
if (!$p) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$harga = (int)$p['harga'];
$nama_varian = '';
if ($vid) {
    $s = mysqli_prepare($koneksi, "SELECT * FROM produk_varian WHERE id = ? AND produk_id = ?");
    mysqli_stmt_bind_param($s, "ii", $vid, $pid);
    mysqli_stmt_execute($s);
    $v = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
    if ($v) { $harga = (int)$v['harga']; $nama_varian = $v['nama']; } else { $vid = 0; }
}

$top_valid = [];
$top_nama  = [];
foreach ($top_post as $tid) {
    $s = mysqli_prepare($koneksi, "SELECT t.* FROM topping t
        JOIN produk_topping pt ON pt.topping_id = t.id
        WHERE t.id = ? AND pt.produk_id = ? AND t.tersedia = 1");
    mysqli_stmt_bind_param($s, "ii", $tid, $pid);
    mysqli_stmt_execute($s);
    $t = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
    if ($t) {
        $harga += (int)$t['harga'];
        $top_valid[] = (int)$t['id'];
        $top_nama[]  = $t['nama'];
    }
}
sort($top_valid);

$keranjang = $_SESSION['keranjang'] ?? [];
$sudah = 0;
foreach ($keranjang as $it) {
    if ($it['produk_id'] === $pid) $sudah += $it['qty'];
}
if ($sudah + $qty > (int)$p['stok']) {
    $_SESSION['flash'] = ['tipe' => 'err',
        'pesan' => 'Stok tidak mencukupi. Sisa stok ' . (int)$p['stok'] . ', di keranjangmu sudah ada ' . $sudah . '.'];
    header("Location: " . BASE_URL . "produk_opsi.php?id=$pid");
    exit;
}

$key = 'k' . md5($pid . '|' . $vid . '|' . implode(',', $top_valid) . '|' . $catatan);
if (isset($keranjang[$key])) {
    $keranjang[$key]['qty'] += $qty;
} else {
    $keranjang[$key] = [
        'produk_id' => $pid,
        'nama'      => $p['nama'],
        'varian'    => $nama_varian,
        'topping'   => $top_nama,
        'catatan'   => $catatan,
        'harga'     => $harga,
        'gambar'    => $p['gambar'],
        'qty'       => $qty,
    ];
}
$_SESSION['keranjang'] = $keranjang;
$_SESSION['flash'] = ['tipe' => 'ok', 'pesan' => $p['nama'] . ' ditambahkan ke keranjang.'];

header("Location: " . BASE_URL . "keranjang.php");
exit;
?>