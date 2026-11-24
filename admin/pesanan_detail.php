<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
require_once '../includes/grafik.php';
wajib_admin();
$judul = 'Detail Pesanan';

$id = (int)($_GET['id'] ?? 0);
$s = $koneksi->prepare("SELECT * FROM pesanan WHERE id=?"); $s->bind_param('i', $id); $s->execute();
$p = $s->get_result()->fetch_assoc();
if (!$p) { header('Location: pesanan.php'); exit; }

$items = [];
$fk = pilih_kolom($koneksi, 'pesanan_detail', ['pesanan_id','id_pesanan']);
if ($fk) {
    $s = $koneksi->prepare("SELECT * FROM pesanan_detail WHERE `$fk`=?"); $s->bind_param('i', $id); $s->execute(); $r = $s->get_result();
    while ($d = $r->fetch_assoc()) {
        $nama = (string)f($d, ['nama_produk','nama','produk_nama']);
        if ($nama === '' && ($pid = (int)f($d, ['produk_id','id_produk'], 0))) { $q = $koneksi->query("SELECT nama FROM produk WHERE id=$pid"); if ($q && ($x = $q->fetch_assoc())) $nama = $x['nama']; }
        $qty = (int)f($d, ['jumlah','qty'], 1); $harga = (int)f($d, ['harga','harga_satuan'], 0);
        $items[] = ['nama' => $nama, 'qty' => $qty, 'ket' => (string)f($d, ['keterangan']), 'sub' => (int)f($d, ['subtotal'], $harga * $qty)];
    }
}
$ongkir = (int)f($p, ['ongkir'], 0);
$total = (int)f($p, ['total','total_harga','grand_total','total_bayar'], array_sum(array_column($items, 'sub')) + $ongkir);
$status = $p['status'] ?? 'Baru'; $bayar = $p['status_bayar'] ?? '-';
$nama = f($p, ['nama_penerima','nama_pelanggan','nama'], '-'); $telp = f($p, ['telepon','no_hp','hp','no_telp','telp'], '');
$kode = f($p, ['kode','kode_pesanan'], '#' . $id);

$tmpl = [
  'Baru'     => "Halo $nama, pesanan $kode sudah kami terima. Mohon kirim bukti pembayaran ya. Terima kasih!",
  'Diproses' => "Halo $nama, pembayaran pesanan $kode sudah kami terima dan pesananmu sedang diproses.",
  'Diantar'  => "Halo $nama, pesanan $kode sedang dalam perjalanan / siap diambil. Mohon ditunggu ya!",
  'Selesai'  => "Halo $nama, pesanan $kode sudah selesai. Terima kasih sudah memesan di " . NAMA_BRAND . "!",
  'Batal'    => "Halo $nama, mohon maaf pesanan $kode dibatalkan. Alasan: " . ($p['alasan_batal'] ?? '-'),
];
$linkWA = $telp ? 'https://wa.me/' . hp_wa($telp) . '?text=' . rawurlencode($tmpl[$status] ?? $tmpl['Baru']) : '';
$kls = $status === 'Selesai' ? 'hijau' : ($status === 'Baru' ? 'coral' : ($status === 'Batal' ? 'abu' : 'kuning'));

include '../includes/admin_header.php'; ?>
<p><a class="ad-btn kecil abu" href="pesanan.php">&larr; Semua pesanan</a></p>
<div class="ad-dua">
  <div class="ad-card">
    <h2>Pesanan <?= e($kode) ?></h2>
    <p class="ad-sub"><span class="ad-badge <?= $kls ?>"><?= e($status) ?></span> &nbsp; Pembayaran: <b><?= e($bayar) ?></b> &middot; <?= e(f($p, ['metode_bayar','metode'], '-')) ?></p>
    <div class="ad-scroll"><table class="ad-tabel"><tr><th>Menu</th><th>Jumlah</th><th>Subtotal</th></tr>
      <?php foreach ($items as $i): ?><tr><td><b><?= e($i['nama']) ?></b><?= $i['ket'] ? '<br><small>' . e($i['ket']) . '</small>' : '' ?></td><td><?= $i['qty'] ?></td><td><?= rupiah($i['sub']) ?></td></tr><?php endforeach; ?>
      <?php if ($ongkir): ?><tr><td>Ongkir</td><td></td><td><?= rupiah($ongkir) ?></td></tr><?php endif; ?>
      <tr><td><b>Total</b></td><td></td><td><b><?= rupiah($total) ?></b></td></tr></table></div>
    <p style="margin-top:14px"><b>Pelanggan:</b> <?= e($nama) ?> <?= $telp ? '(' . e($telp) . ')' : '' ?><br>
       <b>Pengambilan:</b> <?= e(f($p, ['metode_ambil'], '-')) ?><br><b>Lokasi:</b> <?= e(f($p, ['lokasi','alamat','alamat_kirim'], '-')) ?>
       <?php if ($status === 'Batal'): ?><br><b>Alasan batal:</b> <?= e($p['alasan_batal'] ?? '-') ?><?php endif; ?></p>
  </div>
  <div class="ad-card">
    <h2>Tindakan</h2><p class="ad-sub">Ikuti urutan: verifikasi bayar &rarr; proses &rarr; antar &rarr; selesai</p>
    <?php if ($status !== 'Batal' && $status !== 'Selesai'): ?>
    <form method="post" action="pesanan_aksi.php" class="ad-aksi" style="margin-bottom:10px"><input type="hidden" name="id" value="<?= $id ?>">
      <?php if ($bayar !== 'Lunas'): ?><button class="ad-btn" name="aksi" value="verifikasi">Verifikasi Bayar</button><?php endif; ?>
      <?php if ($status === 'Baru'): ?><button class="ad-btn biru" name="aksi" value="proses">Proses Pesanan</button><?php endif; ?>
      <?php if ($status === 'Diproses'): ?><button class="ad-btn biru" name="aksi" value="antar">Tandai Diantar</button><?php endif; ?>
      <?php if ($status === 'Diantar'): ?><button class="ad-btn biru" name="aksi" value="selesai">Tandai Selesai</button><?php endif; ?>
    </form>
    <form method="post" action="pesanan_aksi.php" class="ad-form" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="aksi" value="batal">
      <label>Alasan pembatalan<input name="alasan" placeholder="mis. stok habis / pelanggan membatalkan" required></label>
      <button class="ad-btn abu">Batalkan Pesanan</button></form>
    <?php else: ?><div class="ad-hint">Pesanan ini sudah <?= e(strtolower($status)) ?>.</div><?php endif; ?>
    <hr style="border:0;border-top:1px solid var(--ad-garis);margin:18px 0">
    <h2>Hubungi Pelanggan</h2>
    <?php if ($linkWA): ?><p class="ad-sub">Pesan otomatis sesuai status saat ini.</p><a class="ad-btn" style="background:#1fa855" target="_blank" href="<?= e($linkWA) ?>">Chat via WhatsApp</a>
    <?php else: ?><div class="ad-hint">Nomor telepon pelanggan tidak ada.</div><?php endif; ?>
  </div>
</div>
<?php include '../includes/admin_footer.php'; ?>