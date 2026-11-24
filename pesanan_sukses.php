<?php
require_once 'config/koneksi.php';
require_once 'includes/auth.php';
require_once 'includes/skema.php';
wajib_login();
$judul = 'Pesanan Berhasil';

$kode = trim($_GET['kode'] ?? '');
$s = $koneksi->prepare("SELECT * FROM pesanan WHERE kode=?");
$s->bind_param('s', $kode); $s->execute();
$p = $s->get_result()->fetch_assoc();
if (!$p) { header('Location: index.php'); exit; }

$u = $_SESSION['user'];
$kUser = pilih_kolom($koneksi, 'pesanan', ['user_id','pelanggan_id','id_user','customer_id']);
if ($kUser && ($u['role'] ?? '') !== 'admin' && (int)$p[$kUser] !== (int)$u['id']) { header('Location: index.php'); exit; }

// item pesanan
$items = []; $jumlahItem = 0;
$fk = pilih_kolom($koneksi, 'pesanan_detail', ['pesanan_id','id_pesanan']);
if ($fk) {
    $s = $koneksi->prepare("SELECT * FROM pesanan_detail WHERE `$fk`=?");
    $s->bind_param('i', $p['id']); $s->execute(); $r = $s->get_result();
    while ($d = $r->fetch_assoc()) {
        $nama = (string)f($d, ['nama_produk','nama','produk_nama']);
        if ($nama === '' && ($pid = (int)f($d, ['produk_id','id_produk'], 0))) {
            $q = $koneksi->query("SELECT nama FROM produk WHERE id=$pid"); if ($q && ($x = $q->fetch_assoc())) $nama = $x['nama'];
        }
        $qty = (int)f($d, ['jumlah','qty'], 1);
        $harga = (int)f($d, ['harga','harga_satuan'], 0);
        $items[] = ['nama' => $nama ?: 'Menu', 'qty' => $qty, 'ket' => (string)f($d, ['keterangan']), 'sub' => (int)f($d, ['subtotal'], $harga * $qty)];
        $jumlahItem += $qty;
    }
}
$ongkir = (int)f($p, ['ongkir'], 0);
$total = (int)f($p, ['total','total_harga','grand_total','total_bayar'], array_sum(array_column($items, 'sub')) + $ongkir);
$subtotal = (int)f($p, ['subtotal'], $total - $ongkir);

$metode = mb_strtolower(trim((string)f($p, ['metode_bayar','metode'], 'transfer')));
$jenis = strpos($metode, 'cod') !== false ? 'cod' : (strpos($metode, 'qris') !== false ? 'qris' : 'transfer');
$nama = f($p, ['nama_penerima','nama_pelanggan','nama'], $u['nama'] ?? '');
$telp = f($p, ['telepon','no_hp','hp','no_telp','telp'], '');
$lokasi = f($p, ['lokasi','alamat','alamat_kirim'], '');
$ambil = f($p, ['metode_ambil'], 'Antar');
$qris = cari_aset(['qris*']);
// pecah info rekening: "BCA 1234567890 a.n. Nama"
$bank = trim((string)strtok(BANK_INFO, ' '));
$rek = preg_match('/\d[\d\s-]{5,}\d/', BANK_INFO, $mm) ? preg_replace('/\D/', '', $mm[0]) : '';
$an = preg_match('/a\.?\s?n\.?\s*(.+)$/i', BANK_INFO, $m2) ? trim($m2[1]) : NAMA_BRAND;
$kTgl = pilih_kolom($koneksi, 'pesanan', ['created_at','tanggal','tgl_pesanan','waktu_pesan','dibuat','tanggal_pesan']);
$batas = date('H:i', ($kTgl && !empty($p[$kTgl]) ? strtotime($p[$kTgl]) : time()) + 3600);

// teks WhatsApp
$wa = "Halo " . NAMA_BRAND . ", saya ingin konfirmasi pesanan.\n\nKode: $kode\nNama: $nama\nTelepon: $telp\nPengambilan: $ambil\n";
if ($lokasi !== '') $wa .= "Lokasi: $lokasi\n";
$wa .= "\nPesanan:\n";
foreach ($items as $i) $wa .= '- ' . $i['qty'] . 'x ' . $i['nama'] . ($i['ket'] ? ' (' . $i['ket'] . ')' : '') . ' = ' . rupiah($i['sub']) . "\n";
if ($ongkir) $wa .= "Ongkir: " . rupiah($ongkir) . "\n";
$wa .= "Total: " . rupiah($total) . "\nPembayaran: " . ($jenis === 'cod' ? 'COD (bayar di tempat)' : ($jenis === 'qris' ? 'QRIS' : 'Transfer bank')) . "\n";
$wa .= $jenis === 'cod' ? "\nMohon diproses, terima kasih." : "\nBukti pembayaran saya lampirkan di chat ini. Terima kasih.";
$linkWA = 'https://wa.me/' . hp_wa(TOKO_WA) . '?text=' . rawurlencode($wa);

include 'includes/header.php';
?>
<div class="gd-wrap gd-struk"><div class="gd-kotak">
  <h1>Pesanan berhasil dibuat!</h1>
  <div style="color:var(--gd-abu)">Simpan kode pesananmu</div>
  <div class="gd-kodebox"><?= e($kode) ?></div>

  <?php foreach ($items as $i): ?>
    <div class="gd-item"><div><b><?= (int)$i['qty'] ?>x <?= e($i['nama']) ?></b><?php if ($i['ket']): ?><small><?= e($i['ket']) ?></small><?php endif; ?></div><b><?= rupiah($i['sub']) ?></b></div>
  <?php endforeach; ?>
  <div class="gd-rinci"><span>Subtotal</span><span><?= rupiah($subtotal) ?></span></div>
  <?php if ($ongkir): ?><div class="gd-rinci"><span>Ongkir</span><span><?= rupiah($ongkir) ?></span></div><?php endif; ?>
  <div class="gd-total"><span>Total</span><span><?= rupiah($total) ?></span></div>

  <?php if ($jenis === 'transfer'): ?>
    <div class="gd-bank">
      <div class="gd-bank-kepala"><span class="gd-bank-nama"><?= e($bank) ?></span><small>Transfer Bank</small></div>
      <div class="gd-lbl">Nomor rekening</div>
      <div class="gd-rek"><b><?= e($rek ?: BANK_INFO) ?></b><?php if ($rek): ?><button type="button" class="gd-salin" data-salin="<?= e($rek) ?>">Salin</button><?php endif; ?></div>
      <div class="gd-lbl">Atas nama</div>
      <div class="gd-rek"><b><?= e($an) ?></b></div>
      <div class="gd-lbl">Jumlah yang harus ditransfer</div>
      <div class="gd-rek"><b class="gd-merah"><?= rupiah($total) ?></b><button type="button" class="gd-salin" data-salin="<?= $total ?>">Salin</button></div>
      <div class="gd-batas">Mohon bayar sebelum pukul <b><?= e($batas) ?></b></div>
      <ol class="gd-langkah-bayar">
        <li>Buka aplikasi m-banking atau ATM, pilih menu transfer.</li>
        <li>Masukkan nomor rekening dan nominal persis seperti di atas.</li>
        <li>Simpan bukti transfer, lalu kirim lewat tombol WhatsApp di bawah.</li>
      </ol>
    </div>
  <?php elseif ($jenis === 'qris'): ?>
    <div class="gd-info qris gd-tengah"><h3>Bayar dengan QRIS</h3>
      <?php if ($qris): ?>
        <img class="gd-qr" src="<?= aset($qris) ?>" alt="QRIS">
      <?php else: ?>
        <div id="qrcode" class="gd-qrbox"></div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
          if (window.QRCode) new QRCode(document.getElementById('qrcode'), {text: <?= json_encode('QRIS|' . NAMA_BRAND . '|' . $kode . '|' . $total) ?>, width: 200, height: 200, correctLevel: QRCode.CorrectLevel.M});
        </script>
      <?php endif; ?>
      <div class="gd-nominal"><?= rupiah($total) ?></div>
      <p>Scan memakai GoPay, OVO, DANA, ShopeePay, atau m-banking.</p>
      <p class="gd-kecil">Setelah membayar, kirim bukti lewat tombol WhatsApp di bawah.</p>
    </div>
  <?php else: ?>
    <div class="gd-info cod"><h3>Bayar di Tempat (COD)</h3>
      <p>Siapkan uang pas <b><?= rupiah($total) ?></b> saat pesanan tiba atau saat diambil.</p>
      <p>Tekan tombol WhatsApp di bawah agar pesananmu segera kami proses.</p></div>
  <?php endif; ?>

  <div class="gd-kirim"><b>Dikirim ke:</b> <?= e($nama) ?><?= $telp ? ', ' . e($telp) : '' ?><?= $lokasi ? '<br>' . e($lokasi) : '' ?><br><b>Pengambilan:</b> <?= e($ambil) ?> &middot; Estimasi <?= (int)ESTIMASI_MENIT ?> menit</div>

  <div class="gd-tombol">
    <a class="gd-btn wa" target="_blank" href="<?= e($linkWA) ?>">Konfirmasi via WhatsApp</a>
    <a class="gd-btn garis" href="pesanan_saya.php">Lihat pesanan saya</a>
    <a class="gd-btn garis" href="menu.php">Kembali ke menu</a>
  </div>
</div></div>
<script>
document.querySelectorAll('.gd-salin').forEach(function(b){b.onclick=function(){
  var t=b.dataset.salin, ok=function(){b.textContent='Tersalin';setTimeout(function(){b.textContent='Salin'},1500)};
  if(navigator.clipboard){navigator.clipboard.writeText(t).then(ok)}else{var x=document.createElement('textarea');x.value=t;document.body.appendChild(x);x.select();document.execCommand('copy');x.remove();ok()}
};});
</script>
<?php include 'includes/footer.php'; ?>