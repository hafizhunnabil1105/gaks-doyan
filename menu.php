<?php
require_once 'config/koneksi.php';
require_once 'includes/skema.php';
$judul = 'Menu';
$kat = []; if ($r = @$koneksi->query("SELECT * FROM kategori ORDER BY id")) while ($b = $r->fetch_assoc()) $kat[] = $b;
$produk = []; if ($r = @$koneksi->query("SELECT * FROM produk ORDER BY id")) while ($b = $r->fetch_assoc()) { if (isset($b['aktif']) && (string)$b['aktif'] === '0') continue; $produk[] = $b; }
include 'includes/header.php';
?>
<div class="gd-wrap">
  <div class="gd-kepala-hal"><h1>Menu Kami</h1><p>Pilih favoritmu, tambah topping sesuka hati.</p></div>
  <div class="gd-alat">
    <div class="gd-tab">
      <button class="on" data-k="semua">Semua</button>
      <?php foreach ($kat as $k): ?><button data-k="<?= (int)$k['id'] ?>"><?= e(f($k, ['nama','nama_kategori'])) ?></button><?php endforeach; ?>
    </div>
    <input class="gd-cari" id="cari" type="search" placeholder="Cari menu...">
  </div>
  <div class="gd-grid empat" style="margin-bottom:30px">
  <?php foreach ($produk as $p): $stok = (int)f($p, ['stok'], 99); $g = url_gambar(f($p, ['gambar','foto'])); $nama = f($p, ['nama','nama_produk']); ?>
    <div class="gd-kartu" data-k="<?= (int)f($p, ['kategori_id','id_kategori'], 0) ?>" data-n="<?= e(mb_strtolower($nama)) ?>">
      <div class="gd-foto"><?php if ($g): ?><img src="<?= e($g) ?>" alt="<?= e($nama) ?>"><?php else: ?><svg class="kosong" viewBox="0 0 24 24"><path d="M3 11h18a9 9 0 0 1-18 0z"/></svg><?php endif; ?>
        <?php if ($stok <= 0): ?><span class="gd-tag abu">Habis</span><?php elseif ($stok <= 5): ?><span class="gd-tag kuning">Stok tipis</span><?php endif; ?></div>
      <div class="gd-isi"><h3><?= e($nama) ?></h3>
        <p><?= e(mb_strimwidth((string)f($p, ['deskripsi','keterangan']), 0, 70, '...')) ?></p>
        <div class="gd-bawah"><span class="gd-harga"><?= rupiah((int)f($p, ['harga','harga_dasar'], 0)) ?></span>
        <?php if ($stok > 0): ?><a class="gd-btn" href="produk_opsi.php?id=<?= (int)$p['id'] ?>">Pesan</a><?php endif; ?></div></div>
    </div>
  <?php endforeach; ?>
  </div>
</div>
<script>
var kat='semua';
function saring(){var q=document.getElementById('cari').value.toLowerCase();
  document.querySelectorAll('.gd-kartu').forEach(function(k){
    var ok=(kat==='semua'||k.dataset.k===kat)&&k.dataset.n.indexOf(q)>-1;k.style.display=ok?'':'none';});}
document.querySelectorAll('.gd-tab button').forEach(function(b){b.onclick=function(){
  document.querySelectorAll('.gd-tab button').forEach(function(x){x.classList.remove('on')});b.classList.add('on');kat=b.dataset.k;saring();};});
document.getElementById('cari').oninput=saring;
</script>
<?php include 'includes/footer.php'; ?>