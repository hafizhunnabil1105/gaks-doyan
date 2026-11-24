<?php
require_once 'config/koneksi.php';
require_once 'includes/skema.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$p = $_GET['p'] ?? 'tentang';
$ok = ['tentang','faq','syarat','privasi','kontak','carapesan','testimoni'];
if (!in_array($p, $ok, true)) $p = 'tentang';
$pesan = ''; $err = false;
if ($p === 'kontak' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = trim($_POST['nama'] ?? ''); $em = trim($_POST['email'] ?? ''); $ps = trim($_POST['pesan'] ?? '');
    if ($n && filter_var($em, FILTER_VALIDATE_EMAIL) && $ps) {
        $s = $koneksi->prepare("INSERT INTO pesan_kontak (nama,email,pesan) VALUES (?,?,?)");
        $s->bind_param('sss', $n, $em, $ps); $s->execute();
        $pesan = 'Terima kasih, pesanmu sudah terkirim. Kami akan membalas secepatnya.';
    } else { $pesan = 'Mohon isi nama, email yang valid, dan pesan.'; $err = true; }
}
if ($p === 'testimoni' && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['user'])) {
    $n = trim($_SESSION['user']['nama'] ?? $_SESSION['user']['username'] ?? 'Pelanggan'); $ps = trim($_POST['isi'] ?? ''); $b = max(1, min(5, (int)($_POST['bintang'] ?? 5)));
    if ($ps !== '') { $ps = mb_substr($ps, 0, 300); $s = $koneksi->prepare("INSERT INTO testimoni (nama,isi,bintang,tampil) VALUES (?,?,?,0)"); $s->bind_param('ssi', $n, $ps, $b); $s->execute(); $pesan = 'Terima kasih! Testimonimu akan tampil setelah disetujui admin.'; }
}
$testi = []; if ($p === 'tentang' && ($r = @$koneksi->query("SELECT * FROM testimoni WHERE tampil=1 ORDER BY id DESC LIMIT 3"))) while ($b = $r->fetch_assoc()) $testi[] = $b;
$judul = ['tentang'=>'Tentang Kami','faq'=>'FAQ','syarat'=>'Syarat & Ketentuan','privasi'=>'Kebijakan Privasi','kontak'=>'Kontak','carapesan'=>'Cara Pesan','testimoni'=>'Testimoni'][$p];
$testi = [];
if ($p === 'testimoni' && ($r = @$koneksi->query("SELECT * FROM testimoni WHERE tampil=1 ORDER BY id DESC"))) while ($b = $r->fetch_assoc()) $testi[] = $b;
include 'includes/header.php';
?>
<div class="gd-wrap gd-hal">
<?php if ($p === 'tentang'): ?>
  <div class="gd-kotak"><h1>Tentang <?= e(NAMA_BRAND) ?></h1>
    <p><?= e(NAMA_BRAND) ?> adalah UMKM rice bowl yang menyajikan nasi hangat dengan topping ayam, sapi, dan saus pilihan, dimasak fresh setiap ada pesanan. Kini pemesanan bisa dilakukan lewat website dengan alur yang jelas dari pilih menu sampai pesanan diterima.</p>
    <h3>Visi</h3><p>Menjadi rice bowl favorit anak muda dengan rasa konsisten, harga ramah kantong, dan layanan yang cepat.</p>
    <h3>Misi</h3><p>1. Menggunakan bahan segar dan proses higienis.<br>2. Memberi pengalaman pesan yang mudah dan transparan.<br>3. Terus berinovasi pada menu dan layanan.</p></div>
  <div class="gd-kotak"><h2>Cara Pesan</h2><div class="gd-langkah">
    <div class="gd-lg"><span>1</span><b>Pilih Menu</b><p>Tentukan menu, ukuran, dan topping.</p></div>
    <div class="gd-lg"><span>2</span><b>Checkout</b><p>Isi data, pilih antar atau ambil sendiri.</p></div>
    <div class="gd-lg"><span>3</span><b>Bayar</b><p>Transfer atau COD, kirim bukti lewat WhatsApp.</p></div>
    <div class="gd-lg"><span>4</span><b>Nikmati</b><p>Pesanan diproses dan siap disantap.</p></div></div></div>
  <?php if ($testi): ?><div class="gd-kotak"><h2>Kata Pelanggan</h2><div class="gd-testi"><?php foreach ($testi as $t): ?>
    <div class="gd-ts"><div class="bt"><?= str_repeat('&#9733;', (int)$t['bintang']) ?></div><p>"<?= e($t['isi']) ?>"</p><b><?= e($t['nama']) ?></b></div>
  <?php endforeach; ?></div></div><?php endif; ?>
<?php elseif ($p === 'carapesan'): ?>
  <div class="gd-kepala-hal"><h1>Cara Pesan</h1><p>Empat langkah mudah, tanpa ribet.</p></div>
  <div class="gd-langkah lebar" style="margin:18px 0 30px">
    <div class="gd-lg"><span>1</span><b>Pilih Menu</b><p>Tentukan menu, ukuran, dan topping.</p></div>
    <div class="gd-lg"><span>2</span><b>Checkout</b><p>Isi data, pilih antar atau ambil sendiri.</p></div>
    <div class="gd-lg"><span>3</span><b>Bayar</b><p>Transfer atau COD, kirim bukti lewat WhatsApp.</p></div>
    <div class="gd-lg"><span>4</span><b>Nikmati</b><p>Pesanan diproses dan siap disantap.</p></div>
  </div>
  <p style="text-align:center"><a class="gd-btn" href="menu.php">Mulai Pesan</a></p>
<?php elseif ($p === 'testimoni'): ?>
  <div class="gd-kepala-hal"><h1>Kata Mereka</h1><p>Testimoni pelanggan setia kami.</p></div>
  <?php if ($pesan): ?><div class="gd-flash"><?= e($pesan) ?></div><?php endif; ?>
  <?php if (!$testi): ?><div class="gd-kotak" style="margin-top:18px;text-align:center">Belum ada testimoni.</div><?php else: ?>
  <div class="gd-testi" style="margin-top:18px"><?php foreach ($testi as $t): ?>
    <div class="gd-ts"><div class="bt"><?= str_repeat('&#9733;', (int)$t['bintang']) ?></div><p>"<?= e($t['isi']) ?>"</p><b><?= e($t['nama']) ?></b></div>
  <?php endforeach; ?></div><?php endif; ?>
  <div class="gd-kotak" style="margin-top:22px;max-width:560px"><h3 style="margin-top:0">Tulis Testimoni</h3>
  <?php if (empty($_SESSION['user'])): ?><p>Silakan <a href="login.php"><b>login</b></a> untuk menulis testimoni.</p><?php else: ?>
    <form class="gd-form" method="post"><label>Penilaian<select name="bintang"><option value="5">5 - Sangat puas</option><option value="4">4 - Puas</option><option value="3">3 - Cukup</option><option value="2">2 - Kurang</option><option value="1">1 - Buruk</option></select></label>
    <label>Ceritamu<textarea name="isi" rows="3" maxlength="300" required></textarea></label><button class="gd-btn">Kirim</button></form><?php endif; ?></div>
<?php elseif ($p === 'faq'): ?>
  <div class="gd-kotak gd-faq"><h1>Pertanyaan yang Sering Ditanyakan</h1>
    <details><summary>Bagaimana cara memesan?</summary><p>Pilih menu, masukkan ke keranjang, checkout, lalu kirim konfirmasi pembayaran lewat WhatsApp.</p></details>
    <details><summary>Metode pembayaran apa saja?</summary><p>Transfer bank dan bayar di tempat (COD). Info rekening: <?= e(BANK_INFO) ?>.</p></details>
    <details><summary>Berapa ongkos kirim?</summary><p>Ongkir antar <?= rupiah(ONGKIR_ANTAR) ?>. Gratis jika ambil sendiri.</p></details>
    <details><summary>Berapa lama pesanan sampai?</summary><p>Estimasi sekitar <?= (int)ESTIMASI_MENIT ?> menit setelah pembayaran terverifikasi.</p></details>
    <details><summary>Bisa membatalkan pesanan?</summary><p>Bisa selama pesanan belum diproses, lewat halaman Pesanan Saya.</p></details>
    <details><summary>Jam operasional?</summary><p><?= e(JAM_BUKA) ?> - <?= e(JAM_TUTUP) ?>.</p></details></div>
<?php elseif ($p === 'syarat'): ?>
  <div class="gd-kotak"><h1>Syarat &amp; Ketentuan</h1>
    <p>1. Pesanan dianggap sah setelah pembayaran terverifikasi (kecuali COD).</p>
    <p>2. Harga dan stok dapat berubah sewaktu-waktu tanpa pemberitahuan.</p>
    <p>3. Pembatalan hanya dapat dilakukan sebelum pesanan diproses.</p>
    <p>4. Keterlambatan akibat cuaca atau lalu lintas di luar tanggung jawab kami sepenuhnya.</p>
    <p>5. Keluhan kualitas dapat disampaikan maksimal 1 jam setelah pesanan diterima beserta foto.</p></div>
<?php elseif ($p === 'privasi'): ?>
  <div class="gd-kotak"><h1>Kebijakan Privasi</h1>
    <p>Kami mengumpulkan nama, email, dan alamat pengantaran hanya untuk memproses pesanan dan menghubungi Anda.</p>
    <p>Data tidak dijual atau dibagikan ke pihak ketiga. Kata sandi disimpan terenkripsi.</p>
    <p>Anda dapat mengubah data dan kata sandi kapan saja di halaman Akun.</p></div>
<?php else: ?>
  <div class="gd-dua">
    <div class="gd-kotak"><h1>Hubungi Kami</h1>
      <?php if ($pesan): ?><div class="gd-flash <?= $err ? 'err' : '' ?>"><?= e($pesan) ?></div><?php endif; ?>
      <form class="gd-form" method="post">
        <label>Nama<input name="nama" required></label><label>Email<input type="email" name="email" required></label>
        <label>Pesan<textarea name="pesan" rows="5" required></textarea></label><button class="gd-btn">Kirim Pesan</button></form></div>
    <div class="gd-kotak"><h2>Info Toko</h2>
      <p><b>Alamat:</b> <?= e(TOKO_ALAMAT) ?></p><p><b>Telepon/WA:</b> <?= e(TOKO_TELP) ?></p><p><b>Instagram:</b> <?= e(TOKO_IG) ?></p><p><b>Jam buka:</b> <?= e(JAM_BUKA) ?> - <?= e(JAM_TUTUP) ?></p>
      <iframe class="gd-peta" loading="lazy" src="https://www.google.com/maps?q=<?= urlencode(TOKO_ALAMAT) ?>&output=embed"></iframe></div>
  </div>
<?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>