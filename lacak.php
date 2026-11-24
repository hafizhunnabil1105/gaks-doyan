<?php
require_once 'config/koneksi.php';
require_once 'includes/skema.php';
$judul = 'Lacak Pesanan';
$kode = strtoupper(trim($_GET['kode'] ?? '')); $p = null;
if ($kode !== '') { $s = $koneksi->prepare("SELECT * FROM pesanan WHERE kode=?"); $s->bind_param('s', $kode); $s->execute(); $p = $s->get_result()->fetch_assoc(); }
$tahap = ['Baru','Diproses','Diantar','Selesai']; $pos = $p ? array_search($p['status'], $tahap, true) : false;
include 'includes/header.php'; ?>
<div class="gd-wrap gd-hal" style="max-width:640px"><div class="gd-kotak"><h1>Lacak Pesanan</h1>
  <form class="gd-form" method="get"><label>Kode pesanan<input name="kode" value="<?= e($kode) ?>" placeholder="Masukkan kode pesanan" required></label><button class="gd-btn">Cek Status</button></form>
  <?php if ($kode !== '' && !$p): ?><div class="gd-flash err">Kode tidak ditemukan.</div>
  <?php elseif ($p): ?><h3>Pesanan <?= e($p['kode']) ?></h3>
    <?php if ($p['status'] === 'Batal'): ?><div class="gd-flash err">Pesanan dibatalkan.</div><?php else: ?>
    <ul class="gd-lacak"><?php foreach ($tahap as $i => $t): ?><li class="<?= $pos !== false && $i <= $pos ? 'ok' : '' ?>"><?= $t ?></li><?php endforeach; ?></ul>
    <p>Status pembayaran: <b><?= e($p['status_bayar'] ?? '-') ?></b></p><?php endif; endif; ?></div></div>
<?php include 'includes/footer.php'; ?>