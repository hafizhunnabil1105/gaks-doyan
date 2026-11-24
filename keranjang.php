<?php
include 'config/koneksi.php';
include 'includes/auth.php';
wajib_login();

$keranjang = $_SESSION['keranjang'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key  = $_POST['key'] ?? '';
    $aksi = $_POST['aksi'] ?? '';
    if (isset($keranjang[$key])) {
        if ($aksi === 'hapus') {
            unset($keranjang[$key]);
        } elseif ($aksi === 'ubah') {
            $qty = (int)($_POST['qty'] ?? 1);
            if ($qty < 1) {
                unset($keranjang[$key]);
            } else {
                $pid = $keranjang[$key]['produk_id'];
                $r = mysqli_fetch_row(mysqli_query($koneksi, "SELECT stok FROM produk WHERE id = $pid"));
                $stok = (int)($r[0] ?? 0);
                $lain = 0;
                foreach ($keranjang as $k => $it) {
                    if ($k !== $key && $it['produk_id'] === $pid) $lain += $it['qty'];
                }
                $maks = max(1, min(20, $stok - $lain));
                if ($qty > $maks) {
                    $_SESSION['flash'] = ['tipe' => 'err', 'pesan' => 'Jumlah disesuaikan dengan stok yang tersedia.'];
                }
                $keranjang[$key]['qty'] = min($qty, $maks);
            }
        }
    }
    $_SESSION['keranjang'] = $keranjang;
    header("Location: " . BASE_URL . "keranjang.php");
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include 'includes/header.php';

$total = 0;
foreach ($keranjang as $it) { $total += $it['harga'] * $it['qty']; }
?>
<section class="wadah halaman">
  <h2 class="judul">Keranjang Belanja</h2>

  <?php if ($flash): ?><div class="info <?= $flash['tipe'] ?>"><?= e($flash['pesan']) ?></div><?php endif; ?>

  <?php if (!$keranjang): ?>
    <div class="kartu-web kosong-web">
      <p>Keranjangmu masih kosong.</p>
      <a class="btn-biru" href="index.php#katalog">Lihat Menu</a>
    </div>
  <?php else: ?>
    <div class="kartu-web">
      <?php foreach ($keranjang as $key => $it): ?>
        <?php $ada = $it['gambar'] !== 'default.jpg' && file_exists(__DIR__ . '/assets/img/produk/' . $it['gambar']); ?>
        <div class="k-baris">
          <div class="k-foto"><?php if ($ada): ?><img src="assets/img/produk/<?= e($it['gambar']) ?>" alt=""><?php endif; ?></div>
          <div class="k-info">
            <b><?= e($it['nama']) ?><?= $it['varian'] ? ' (' . e($it['varian']) . ')' : '' ?></b>
            <?php if ($it['topping']): ?><div class="k-kecil">Topping: <?= e(implode(', ', $it['topping'])) ?></div><?php endif; ?>
            <?php if ($it['catatan']): ?><div class="k-kecil">Catatan: <?= e($it['catatan']) ?></div><?php endif; ?>
            <div class="k-kecil"><?= rupiah($it['harga']) ?> / porsi</div>
          </div>
          <form method="POST" class="k-qty">
            <input type="hidden" name="aksi" value="ubah">
            <input type="hidden" name="key" value="<?= e($key) ?>">
            <input class="input-web" type="number" name="qty" value="<?= (int)$it['qty'] ?>" min="0" max="20" onchange="this.form.submit()">
          </form>
          <div class="k-sub"><?= rupiah($it['harga'] * $it['qty']) ?></div>
          <form method="POST">
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="key" value="<?= e($key) ?>">
            <button class="k-hapus" type="submit">Hapus</button>
          </form>
        </div>
      <?php endforeach; ?>

      <div class="k-total">
        <span>Total</span>
        <b><?= rupiah($total) ?></b>
      </div>
      <div class="k-aksi">
        <a class="btn-garis-gelap" href="index.php#katalog">&larr; Tambah menu lain</a>
        <a class="btn-biru" href="checkout.php">Lanjut ke Checkout</a>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>