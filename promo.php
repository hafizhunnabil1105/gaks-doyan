<?php
require_once 'config/koneksi.php';
require_once 'includes/skema.php';
$judul = 'Promo';
$promo = []; if ($r = @$koneksi->query("SELECT * FROM promo ORDER BY id DESC")) while ($b = $r->fetch_assoc()) { if ((string)f($b, ['aktif','status','tampil'], 1) === '0') continue; $promo[] = $b; }
$semua = []; if ($r = @$koneksi->query("SELECT * FROM produk ORDER BY id")) while ($b = $r->fetch_assoc()) $semua[] = $b;
include 'includes/header.php'; ?>
<div class="gd-wrap gd-hal"><h2 class="gd-judul">Promo Spesial</h2><p>Hemat lebih banyak untuk pesananmu</p>
<?php if (!$promo): ?><div class="gd-kotak" style="text-align:center">Belum ada promo saat ini.</div><?php else: ?>
<div class="gd-promo daftar"><?php foreach ($promo as $p): $g = foto_promo($p, $semua); ?>
  <div class="gd-pr"><?php if ($g): ?><img src="<?= e($g) ?>" alt=""><?php endif; ?><div>
    <?php if (f($p, ['tag','label'])): ?><span class="gd-tag"><?= e(f($p, ['tag','label'])) ?></span><?php endif; ?>
    <h3><?= e(f($p, ['judul','nama','nama_promo'])) ?></h3><p><?= e(f($p, ['deskripsi','keterangan','isi'])) ?></p></div></div>
<?php endforeach; ?></div><p style="text-align:center;margin-top:24px"><a class="gd-btn" href="index.php#menu">Pesan Sekarang</a></p><?php endif; ?></div>
<?php include 'includes/footer.php'; ?>