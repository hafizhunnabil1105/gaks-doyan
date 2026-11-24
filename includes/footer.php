<footer class="gd-foot">
  <div class="gd-wrap gd-foot-in">
    <div>
      <h4><?= e(NAMA_BRAND) ?></h4>
      <p>Rice bowl hangat dengan topping royal, dimasak fresh setiap pesanan.</p>
      <p class="gd-dim"><?= e(TOKO_ALAMAT) ?></p>
    </div>
    <div>
      <h4>Jelajahi</h4>
      <a href="menu.php">Menu</a><a href="promo.php">Promo</a><a href="lacak.php">Lacak Pesanan</a><a href="pesanan_saya.php">Pesanan Saya</a>
    </div>
    <div>
      <h4>Bantuan</h4>
      <a href="halaman.php?p=tentang">Tentang Kami</a><a href="halaman.php?p=carapesan">Cara Pesan</a><a href="halaman.php?p=testimoni">Testimoni</a><a href="halaman.php?p=faq">FAQ</a><a href="halaman.php?p=kontak">Kontak</a>
            <!-- <a href="halaman.php?p=syarat">Syarat &amp; Ketentuan</a><a href="halaman.php?p=privasi">Kebijakan Privasi</a> -->
    </div>
    <div>
      <h4>Hubungi Kami</h4>
      <p>WhatsApp: <?= e(TOKO_TELP) ?></p><p>Instagram: <?= e(TOKO_IG) ?></p><p>Buka <?= e(JAM_BUKA) ?> - <?= e(JAM_TUTUP) ?></p>
    </div>
  </div>
  <div class="gd-copy">&copy; <?= date('Y') ?> <?= e(NAMA_BRAND) ?>.</div>
</footer>
<a class="gd-wa" href="https://wa.me/<?= e(hp_wa(TOKO_WA)) ?>" target="_blank" aria-label="Chat WhatsApp">
  <svg viewBox="0 0 24 24" fill="#fff"><path d="M4 4h16a1 1 0 011 1v11a1 1 0 01-1 1H9l-5 4V5a1 1 0 011-1z"/></svg>
</a>
</body>
</html>