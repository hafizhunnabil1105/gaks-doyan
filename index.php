<?php
require_once 'config/koneksi.php';
require_once 'includes/skema.php';
$judul = 'Beranda';

$banner = cari_aset(['banner*','hero*']);
$produk = [];
if ($r = @$koneksi->query("SELECT * FROM produk ORDER BY id")) while ($b = $r->fetch_assoc()) {
    if (isset($b['aktif']) && (string)$b['aktif'] === '0') continue;
    if ((int)f($b, ['stok'], 1) <= 0) continue;
    $produk[] = $b;
}
$favorit = array_slice($produk, 0, 4);
$semua = [];
if ($r = @$koneksi->query("SELECT * FROM produk ORDER BY id")) while ($b = $r->fetch_assoc()) { if (isset($b['aktif']) && (string)$b['aktif'] === '0') continue; $semua[] = $b; }
$promo = [];
if ($r = @$koneksi->query("SELECT * FROM promo ORDER BY id DESC LIMIT 6")) while ($b = $r->fetch_assoc()) {
    if ((string)f($b, ['aktif','status','tampil'], 1) === '0') continue;
    $promo[] = $b;
}
$promo = array_slice($promo, 0, 3);
$buka = toko_buka();
include 'includes/header.php';
?>
<section class="gd-hero">
  <?php if ($banner): ?><div class="gd-hero-bg" style="background-image:url('<?= aset($banner) ?>')"></div><?php endif; ?>
  <div class="gd-hero-in <?= $banner ? '' : 'gd-hero-solo' ?>">
    <div>
      <h1>Rice bowl hangat,<br><em>bikin nagih</em> tiap suapan</h1>
      <p>Ayam, sapi, dan saus favorit dimasak fresh saat dipesan. Pesan online, diantar atau ambil sendiri.</p>
      <div class="gd-hero-btn">
        <a class="gd-btn" href="menu.php">Lihat Menu</a>
        <a class="gd-btn garis" style="color:#fff!important;border-color:#fff" target="_blank" href="https://wa.me/<?= e(hp_wa(TOKO_WA)) ?>">Chat WhatsApp</a>
      </div>
    </div>
    <?php if ($banner): ?><div class="gd-hero-img"><img src="<?= aset($banner) ?>" alt="Rice bowl"></div><?php endif; ?>
  </div>
</section>
<div class="gd-bar"><div class="gd-bar-in">
  <span class="gd-status <?= $buka ? '' : 'tutup' ?>"><?= $buka ? 'Buka sekarang' : 'Sedang tutup' ?></span>
  <span>Jam <?= e(JAM_BUKA) ?> - <?= e(JAM_TUTUP) ?></span><span><?= e(TOKO_ALAMAT) ?></span>
</div></div>

<div class="gd-wrap">
  <div class="gd-fitur">
    <div class="gd-fi"><i><svg viewBox="0 0 24 24"><path d="M3 11h18a9 9 0 0 1-18 0z"/><path d="M8 7c0-1.5 1.5-1.5 1.5-3M12 7c0-1.5 1.5-1.5 1.5-3M16 7c0-1.5 1.5-1.5 1.5-3"/></svg></i><div><b>Dimasak Fresh</b><small></small></div></div>
    <div class="gd-fi"><i><svg viewBox="0 0 24 24"><path d="M2 6h11v10H2zM13 9h4l3 3v4h-7z"/><circle cx="6" cy="17.5" r="1.8"/><circle cx="16.5" cy="17.5" r="1.8"/></svg></i><div><b>Antar / Ambil</b><small></small></div></div>
    <div class="gd-fi"><i><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20M6 15h4"/></svg></i><div><b>Bayar Mudah</b><small></small></div></div>
    <div class="gd-fi"><i><svg viewBox="0 0 24 24"><path d="M4 5h16v11H9l-5 4z"/></svg></i><div><b>Konfirmasi WA</b><small></small></div></div>
  </div>

  <section class="gd-sec">
    <div class="gd-kepala"><div><h2>Menu Favorit</h2><p>Pilihan yang paling sering dipesan</p></div><a class="gd-lagi" href="menu.php">Lihat semua menu &rarr;</a></div>
    <div class="gd-grid empat">
    <?php foreach ($favorit as $p): $stok = (int)f($p, ['stok'], 99); $g = url_gambar(f($p, ['gambar','foto'])); ?>
      <div class="gd-kartu">
        <div class="gd-foto"><?php if ($g): ?><img src="<?= e($g) ?>" alt="<?= e(f($p, ['nama','nama_produk'])) ?>"><?php else: ?><svg class="kosong" viewBox="0 0 24 24"><path d="M3 11h18a9 9 0 0 1-18 0z"/></svg><?php endif; ?>
          <?php if ($stok <= 5): ?><span class="gd-tag kuning">Stok tipis</span><?php endif; ?></div>
        <div class="gd-isi"><h3><?= e(f($p, ['nama','nama_produk'])) ?></h3>
          <div class="gd-bawah"><span class="gd-harga"><?= rupiah((int)f($p, ['harga','harga_dasar'], 0)) ?></span>
          <a class="gd-btn" href="produk_opsi.php?id=<?= (int)$p['id'] ?>">Pesan</a></div></div>
      </div>
    <?php endforeach; ?>
    <?php if (!$favorit): ?><p>Menu belum tersedia.</p><?php endif; ?>
    </div>
  </section>

<?php if ($promo): ?>
  <section class="gd-sec">
    <div class="gd-kepala"><div><h2>Promo Spesial</h2><p>Hemat lebih banyak untuk pesananmu</p></div><a class="gd-lagi" href="promo.php">Semua promo &rarr;</a></div>
    <div class="gd-promo mini">
    <?php foreach ($promo as $p): $g = foto_promo($p, $semua); ?>
      <div class="gd-pr"><?php if ($g): ?><img src="<?= e($g) ?>" alt=""><?php endif; ?>
        <div><?php if (f($p, ['tag','label'])): ?><span class="gd-tag"><?= e(f($p, ['tag','label'])) ?></span><?php endif; ?>
        <h3><?= e(f($p, ['judul','nama','nama_promo'])) ?></h3><p><?= e(mb_strimwidth((string)f($p, ['deskripsi','keterangan','isi']), 0, 60, '...')) ?></p></div></div>
    <?php endforeach; ?></div>
  </section>
<?php endif; ?>

  <div class="gd-cta slim">
    <div><h2>Lapar? Yuk pesan sekarang</h2><p>Bingung cara pesannya? Lihat panduan singkat kami.</p></div>
    <div class="gd-aksi-cta"><a class="gd-btn" href="menu.php">Pesan Sekarang</a><a class="gd-btn biru" href="halaman.php?p=carapesan">Cara Pesan</a></div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>