<?php
include 'config/koneksi.php';
include 'includes/auth.php';
wajib_login();

$id = (int)($_GET['id'] ?? 0);
$s = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE id = ? AND tersedia = 1");
mysqli_stmt_bind_param($s, "i", $id);
mysqli_stmt_execute($s);
$p = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
if (!$p || $p['stok'] <= 0) {
    header("Location: " . BASE_URL . "index.php#katalog");
    exit;
}

$varian  = mysqli_query($koneksi, "SELECT * FROM produk_varian WHERE produk_id = $id ORDER BY harga");
$topping = mysqli_query($koneksi, "SELECT t.* FROM topping t
    JOIN produk_topping pt ON pt.topping_id = t.id
    WHERE pt.produk_id = $id AND t.tersedia = 1 ORDER BY t.id");

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include 'includes/header.php';
$ada_gambar = $p['gambar'] !== 'default.jpg' && file_exists(__DIR__ . '/assets/img/produk/' . $p['gambar']);
$maks = min(20, (int)$p['stok']);
?>
<section class="wadah halaman">
  <a class="kembali" href="index.php#katalog">&larr; Kembali ke menu</a>

  <?php if ($flash): ?><div class="info <?= $flash['tipe'] ?>"><?= e($flash['pesan']) ?></div><?php endif; ?>

  <form method="POST" action="keranjang_tambah.php" class="opsi-kartu" id="formOpsi" data-dasar="<?= (int)$p['harga'] ?>">
    <input type="hidden" name="produk_id" value="<?= $p['id'] ?>">

    <div class="opsi-foto">
      <?php if ($ada_gambar): ?><img src="assets/img/produk/<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>"><?php endif; ?>
    </div>

    <div class="opsi-isi">
      <h2><?= e($p['nama']) ?></h2>
      <p class="deskripsi"><?= e($p['deskripsi']) ?></p>

      <?php if (mysqli_num_rows($varian) > 0): ?>
        <h4>Pilih ukuran</h4>
        <div class="pilihan">
          <?php $n = 0; while ($v = mysqli_fetch_assoc($varian)): ?>
            <label><input type="radio" name="varian_id" value="<?= $v['id'] ?>" data-harga="<?= (int)$v['harga'] ?>" <?= $n++ === 0 ? 'checked' : '' ?>>
              <?= e($v['nama']) ?> - <?= rupiah($v['harga']) ?></label>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="harga-dasar"><?= rupiah($p['harga']) ?></div>
      <?php endif; ?>

      <?php if (mysqli_num_rows($topping) > 0): ?>
        <h4>Tambah topping</h4>
        <div class="pilihan">
          <?php while ($t = mysqli_fetch_assoc($topping)): ?>
            <label><input type="checkbox" name="topping[]" value="<?= $t['id'] ?>" data-harga="<?= (int)$t['harga'] ?>">
              <?= e($t['nama']) ?> (+<?= rupiah($t['harga']) ?>)</label>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>

      <h4>Jumlah</h4>
      <input class="input-web" type="number" name="qty" value="1" min="1" max="<?= $maks ?>" style="max-width:120px">

      <h4>Catatan (opsional)</h4>
      <input class="input-web" type="text" name="catatan" maxlength="150" placeholder="Contoh: sambalnya dipisah">

      <div class="total-bar">
        <div>Total: <b id="total"></b></div>
        <button type="submit" class="btn-biru">Tambah ke Keranjang</button>
      </div>
    </div>
  </form>
</section>

<script>
var f = document.getElementById('formOpsi');
function hitung() {
  var harga = parseInt(f.dataset.dasar);
  var v = f.querySelector('input[name=varian_id]:checked');
  if (v) harga = parseInt(v.dataset.harga);
  f.querySelectorAll('input[name="topping[]"]:checked').forEach(function (c) { harga += parseInt(c.dataset.harga); });
  var qty = Math.max(1, parseInt(f.qty.value) || 1);
  document.getElementById('total').textContent = 'Rp ' + (harga * qty).toLocaleString('id-ID');
}
f.addEventListener('change', hitung);
f.addEventListener('input', hitung);
hitung();
</script>

<?php include 'includes/footer.php'; ?>