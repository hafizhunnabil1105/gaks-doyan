<?php
$halaman = 'produk';
$judul   = 'Kelola Produk';
$sub     = "Daftar menu Ga'ks Doyan";
include '../includes/admin_header.php';

function e($s) { return htmlspecialchars((string)$s); }

$cari = trim($_GET['cari'] ?? '');
$like = '%' . $cari . '%';
$per  = 5;
$hal  = max(1, (int)($_GET['hal'] ?? 1));

$s = mysqli_prepare($koneksi, "SELECT COUNT(*) FROM produk WHERE nama LIKE ?");
mysqli_stmt_bind_param($s, "s", $like);
mysqli_stmt_execute($s);
$total = (int)mysqli_fetch_row(mysqli_stmt_get_result($s))[0];

$jml_hal = max(1, (int)ceil($total / $per));
$hal     = min($hal, $jml_hal);
$offset  = ($hal - 1) * $per;

$s = mysqli_prepare($koneksi, "SELECT p.*, k.nama AS kategori
    FROM produk p LEFT JOIN kategori k ON k.id = p.kategori_id
    WHERE p.nama LIKE ? ORDER BY p.id DESC LIMIT ? OFFSET ?");
mysqli_stmt_bind_param($s, "sii", $like, $per, $offset);
mysqli_stmt_execute($s);
$hasil = mysqli_stmt_get_result($s);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php if ($flash): ?>
  <div class="info <?= $flash['tipe'] ?>"><?= e($flash['pesan']) ?></div>
<?php endif; ?>

<div class="alat">
  <form method="GET">
    <input type="text" name="cari" placeholder="Cari produk..." value="<?= e($cari) ?>">
    <button class="btn abu" type="submit">Cari</button>
  </form>
  <a href="produk_form.php" class="btn">+ Tambah Produk</a>
</div>

<div class="kartu tabel-scroll">
  <table>
    <thead>
      <tr><th>No</th><th>Gambar</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php if ($total === 0): ?>
      <tr><td colspan="7" class="kosong">Belum ada produk.</td></tr>
    <?php endif; ?>
    <?php $no = $offset + 1; while ($p = mysqli_fetch_assoc($hasil)): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td>
          <?php if ($p['gambar'] !== 'default.jpg' && file_exists(__DIR__ . '/../assets/img/produk/' . $p['gambar'])): ?>
            <img class="thumb" src="<?= BASE_URL ?>assets/img/produk/<?= e($p['gambar']) ?>" alt="">
          <?php else: ?>
            <div class="thumb"></div>
          <?php endif; ?>
        </td>
        <td><?= e($p['nama']) ?></td>
        <td><?= e($p['kategori'] ?? '-') ?></td>
        <td><?= rupiah($p['harga']) ?></td>
        <td><span class="badge-stok <?= $p['stok'] <= 5 ? 'tipis' : '' ?>"><?= (int)$p['stok'] ?></span></td>
        <td>
          <div class="aksi">
            <a class="btn-kecil" href="produk_form.php?id=<?= $p['id'] ?>">Edit</a>
            <form method="POST" action="produk_hapus.php"
                  onsubmit="return confirm('Hapus produk ini?')">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button class="btn-kecil hapus" type="submit">Hapus</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <div class="paging">
    <span>Menampilkan <?= $total ? $offset + 1 : 0 ?> - <?= min($offset + $per, $total) ?> dari <?= $total ?> produk</span>
    <?php for ($i = 1; $i <= $jml_hal; $i++): ?>
      <a class="<?= $i === $hal ? 'aktif' : '' ?>" href="?hal=<?= $i ?>&cari=<?= urlencode($cari) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>