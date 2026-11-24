<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
require_once '../includes/grafik.php';
wajib_admin();
$judul = 'Dashboard';

$c = kolom_pesanan($koneksi);
function tanya(mysqli $k, string $sql) { try { return $k->query($sql) ?: null; } catch (Throwable $x) { return null; } }
function satu(mysqli $k, string $sql) { $r = tanya($k, $sql); return $r ? (int)($r->fetch_row()[0] ?? 0) : 0; }
$kBayar = pilih_kolom($koneksi, 'pesanan', ['status_bayar','status_pembayaran','pembayaran']);

$baru  = satu($koneksi, "SELECT COUNT(*) FROM pesanan WHERE status='Baru'");
$verif = $kBayar ? satu($koneksi, "SELECT COUNT(*) FROM pesanan WHERE `$kBayar`='Menunggu verifikasi' AND status<>'Batal'") : 0;
$hariIni = $omzet = 0;
if ($c['tgl']) {
    $hariIni = satu($koneksi, "SELECT COUNT(*) FROM pesanan WHERE DATE(`{$c['tgl']}`)=CURDATE()");
    if ($c['total']) $omzet = satu($koneksi, "SELECT COALESCE(SUM(`{$c['total']}`),0) FROM pesanan WHERE DATE(`{$c['tgl']}`)=CURDATE() AND status<>'Batal'");
}

// grafik 7 hari
$hari = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
$per = data_harian($koneksi, date('Y-m-d', strtotime('-6 days')), date('Y-m-d'));
$grafik = []; $total7 = 0;
foreach ($per as $tgl => $n) {
    $grafik[] = ['label' => $hari[date('w', strtotime($tgl))] . ' ' . date('d', strtotime($tgl)), 'nilai' => $n, 'ini' => $tgl === date('Y-m-d')];
    $total7 += $n;
}

// status
$status = ['Baru' => 0, 'Diproses' => 0, 'Diantar' => 0, 'Selesai' => 0, 'Batal' => 0];
$r = tanya($koneksi, "SELECT status, COUNT(*) n FROM pesanan GROUP BY status");
if ($r) while ($b = $r->fetch_assoc()) if (isset($status[$b['status']])) $status[$b['status']] = (int)$b['n'];
$maksS = max(1, max($status));

// produk terlaris (3 teratas)
$laris = [];
$kq = pilih_kolom($koneksi, 'pesanan_detail', ['jumlah','qty','kuantitas']);
$kn = pilih_kolom($koneksi, 'pesanan_detail', ['nama_produk','nama','produk_nama']);
$kp = pilih_kolom($koneksi, 'pesanan_detail', ['produk_id','id_produk']);
if ($kq) {
    if ($kn) $sql = "SELECT `$kn` nama, SUM(`$kq`) qty FROM pesanan_detail GROUP BY `$kn` ORDER BY qty DESC LIMIT 3";
    elseif ($kp) $sql = "SELECT p.nama nama, SUM(d.`$kq`) qty FROM pesanan_detail d JOIN produk p ON p.id=d.`$kp` GROUP BY d.`$kp` ORDER BY qty DESC LIMIT 3";
    else $sql = '';
    if ($sql && ($r = tanya($koneksi, $sql))) while ($b = $r->fetch_assoc()) $laris[] = $b;
}

// pesanan terbaru (3)
$terbaru = [];
$urut = $c['tgl'] ? "`{$c['tgl']}` DESC" : 'id DESC';
if ($r = tanya($koneksi, "SELECT * FROM pesanan ORDER BY $urut LIMIT 3")) while ($b = $r->fetch_assoc()) $terbaru[] = $b;

include '../includes/admin_header.php';
?>
<style>
.ad-main{padding:16px 24px 20px}
.ad-stat{gap:12px;margin-bottom:14px}
.ad-st{padding:12px 16px}.ad-st b{font-size:22px;margin-top:2px}.ad-st small{font-size:12.5px}
.ad-st em{display:block;font-style:normal;font-size:11.5px;color:var(--ad-abu);margin-top:2px}
.ad-card{padding:14px 18px;margin-bottom:0}.ad-card h2{font-size:15px}.ad-sub{margin:0 0 8px;font-size:12px}
.ad-dua{gap:14px;margin-bottom:14px}
.ad-tabel th,.ad-tabel td{padding:7px 10px}
.sr-baris{margin:7px 0}
</style>
<div class="ad-stat">
  <div class="ad-st c"><small>Pesanan Baru</small><b><?= $baru ?></b><em>belum diproses</em></div>
  <div class="ad-st k"><small>Menunggu Cek Bayar</small><b><?= $verif ?></b><em>bukti bayar belum dicek</em></div>
  <div class="ad-st"><small>Pesanan Hari Ini</small><b><?= $hariIni ?></b><em>yang masuk hari ini</em></div>
  <div class="ad-st h"><small>Uang Masuk Hari Ini</small><b><?= rupiah($omzet) ?></b><em>total pesanan hari ini</em></div>
</div>

<div class="ad-dua">
  <div class="ad-card">
    <h2>Penjualan 7 Hari Terakhir</h2>
    <p class="ad-sub">Tiap batang = uang masuk per hari. Total: <b><?= rupiah($total7) ?></b></p>
    <?= grafik_batang($grafik, 640, 175) ?>
  </div>
  <div class="ad-card">
    <h2>Posisi Pesanan</h2>
    <p class="ad-sub">Berapa pesanan di tiap tahap</p>
    <?php foreach ($status as $s => $n): ?>
      <div class="sr-baris"><span><?= $s ?></span><div class="sr-track"><div class="sr-bar sr-<?= $s ?>" style="width:<?= round($n / $maksS * 100) ?>%"></div></div><b><?= $n ?></b></div>
    <?php endforeach; ?>
  </div>
</div>

<div class="ad-dua" style="margin-bottom:0">
  <div class="ad-card">
    <h2>Pesanan Terbaru <a href="pesanan.php" style="float:right;font-size:12px;color:var(--ad-coral);text-decoration:none">Lihat semua &rarr;</a></h2>
    <?php if (!$terbaru): ?><div class="ad-kosong">Belum ada pesanan.</div><?php else: ?>
    <div class="ad-scroll"><table class="ad-tabel">
      <tr><th>Kode</th><th>Total</th><th>Status</th><th></th></tr>
      <?php foreach ($terbaru as $p): $st = $p['status'] ?? ''; ?>
      <tr>
        <td><b><?= e(f($p, ['kode','kode_pesanan'], '#' . ($p['id'] ?? ''))) ?></b></td>
        <td><?= rupiah((int)f($p, ['total','total_harga','grand_total','total_bayar'], 0)) ?></td>
        <td><span class="ad-badge <?= $st === 'Selesai' ? 'hijau' : ($st === 'Baru' ? 'coral' : ($st === 'Batal' ? 'abu' : 'kuning')) ?>"><?= e($st) ?></span></td>
        <td><a class="ad-btn kecil biru" href="pesanan_detail.php?id=<?= (int)$p['id'] ?>">Detail</a></td>
      </tr>
      <?php endforeach; ?>
    </table></div>
    <?php endif; ?>
  </div>
  <div class="ad-card">
    <h2>Menu Paling Laku</h2>
    <p class="ad-sub">Jumlah porsi yang sudah terjual</p>
    <?php if (!$laris): ?><div class="ad-kosong">Belum ada penjualan.</div><?php else: $mk = max(1, (int)$laris[0]['qty']); foreach ($laris as $i => $b): ?>
      <div class="sr-baris" style="grid-template-columns:1fr 28px"><span><?= ($i + 1) . '. ' . e($b['nama']) ?></span><b><?= (int)$b['qty'] ?></b></div>
      <div class="sr-track" style="margin-top:-4px"><div class="sr-bar" style="width:<?= round($b['qty'] / $mk * 100) ?>%"></div></div>
    <?php endforeach; endif; ?>
  </div>
</div>
<?php include '../includes/admin_footer.php'; ?>