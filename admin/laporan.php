<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
require_once '../includes/grafik.php';
wajib_admin();
$judul = 'Laporan Keuangan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'tambah') {
        $tgl = $_POST['tanggal'] ?? date('Y-m-d'); $ket = trim($_POST['keterangan'] ?? '');
        $kat = trim($_POST['kategori'] ?? 'Lainnya'); $jml = (int)($_POST['jumlah'] ?? 0);
        if ($ket !== '' && $jml > 0) {
            $s = $koneksi->prepare("INSERT INTO pengeluaran (tanggal, keterangan, kategori, jumlah) VALUES (?,?,?,?)");
            $s->bind_param('sssi', $tgl, $ket, $kat, $jml); $s->execute();
            $_SESSION['flash'] = 'Pengeluaran dicatat.';
        }
    } elseif ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        $s = $koneksi->prepare("DELETE FROM pengeluaran WHERE id=?"); $s->bind_param('i', $id); $s->execute();
        $_SESSION['flash'] = 'Pengeluaran dihapus.';
    }
    header('Location: laporan.php?bulan=' . urlencode($_POST['bulan'] ?? date('Y-m'))); exit;
}

$bulan = preg_match('/^\d{4}-\d{2}$/', $_GET['bulan'] ?? '') ? $_GET['bulan'] : date('Y-m');
$awal = $bulan . '-01'; $akhir = date('Y-m-t', strtotime($awal));
$c = kolom_pesanan($koneksi);

$pendapatan = 0; $jmlPesanan = 0;
if ($c['tgl'] && $c['total']) {
    $s = $koneksi->prepare("SELECT COUNT(*) n, COALESCE(SUM(`{$c['total']}`),0) s FROM pesanan WHERE DATE(`{$c['tgl']}`) BETWEEN ? AND ? AND status='Selesai'");
    $s->bind_param('ss', $awal, $akhir); $s->execute(); $x = $s->get_result()->fetch_assoc();
    $jmlPesanan = (int)$x['n']; $pendapatan = (int)$x['s'];
}
$s = $koneksi->prepare("SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran WHERE tanggal BETWEEN ? AND ?");
$s->bind_param('ss', $awal, $akhir); $s->execute(); $keluar = (int)$s->get_result()->fetch_row()[0];
$laba = $pendapatan - $keluar;

$per = data_harian($koneksi, $awal, $akhir);
$grafik = [];
foreach ($per as $t => $n) $grafik[] = ['label' => date('d', strtotime($t)), 'nilai' => $n, 'ini' => $t === date('Y-m-d')];

$s = $koneksi->prepare("SELECT * FROM pengeluaran WHERE tanggal BETWEEN ? AND ? ORDER BY tanggal DESC, id DESC");
$s->bind_param('ss', $awal, $akhir); $s->execute(); $daftar = $s->get_result();

$prev = date('Y-m', strtotime($awal . ' -1 month')); $next = date('Y-m', strtotime($awal . ' +1 month'));
$namaBulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
include '../includes/admin_header.php';
?>
<style>
.ad-main{padding:16px 24px 20px}
.ad-filter{margin-bottom:12px}.ad-filter a{padding:6px 14px;font-size:13px}
.ad-stat{gap:12px;margin-bottom:14px}
.ad-st{padding:12px 16px}.ad-st b{font-size:22px;margin-top:2px}.ad-st small{font-size:12.5px}
.ad-st em{display:block;font-style:normal;font-size:11.5px;color:var(--ad-abu);margin-top:2px}
.ad-card{padding:14px 18px;margin-bottom:0}.ad-card h2{font-size:15px}.ad-sub{margin:0 0 8px;font-size:12px}
.ad-dua{gap:14px;margin-top:14px;grid-template-columns:1.3fr 1fr}
.ad-tabel th,.ad-tabel td{padding:6px 10px}
.lp-scroll{max-height:200px;overflow:auto}
.lp-form{display:grid;grid-template-columns:1fr 1fr;gap:8px 12px}
.lp-form label{display:grid;gap:3px;font-size:12px;font-weight:600;color:var(--ad-navy)}
.lp-form input,.lp-form select{padding:7px 10px;border:1.5px solid var(--ad-garis);border-radius:8px;font:inherit}
.lp-form .lebar{grid-column:1/3}
</style>
<div class="ad-filter">
  <a href="?bulan=<?= $prev ?>">&larr; Bulan lalu</a>
  <a class="on"><?= $namaBulan[substr($bulan, 5, 2)] . ' ' . substr($bulan, 0, 4) ?></a>
  <a href="?bulan=<?= $next ?>">Bulan depan &rarr;</a>
  <a href="rekap_unduh.php">Unduh Rekap (CSV)</a>
</div>
<div class="ad-stat">
  <div class="ad-st h"><small>Pemasukan</small><b><?= rupiah($pendapatan) ?></b><em>dari <?= $jmlPesanan ?> pesanan yang sudah Selesai</em></div>
  <div class="ad-st c"><small>Pengeluaran</small><b><?= rupiah($keluar) ?></b><em>belanja bahan, gas, dll</em></div>
  <div class="ad-st <?= $laba >= 0 ? '' : 'c' ?>"><small>Untung / Rugi</small><b><?= ($laba < 0 ? '-' : '') . rupiah(abs($laba)) ?></b><em>pemasukan dikurangi pengeluaran</em></div>
  <div class="ad-st k"><small>Rata-rata per Pesanan</small><b><?= rupiah($jmlPesanan ? intdiv($pendapatan, $jmlPesanan) : 0) ?></b><em>pemasukan dibagi jumlah pesanan</em></div>
</div>
<div class="ad-card"><h2>Penjualan per Tanggal</h2><p class="ad-sub">Tiap batang = uang masuk di tanggal itu (pesanan yang tidak dibatalkan)</p><?= grafik_batang($grafik, 900, 130) ?></div>

<div class="ad-dua">
  <div class="ad-card">
    <h2>Daftar Pengeluaran Bulan Ini</h2>
    <?php if ($daftar->num_rows === 0): ?><div class="ad-kosong" style="padding:14px 0">Belum ada pengeluaran.</div><?php else: ?>
    <div class="ad-scroll lp-scroll"><table class="ad-tabel"><tr><th>Tgl</th><th>Keterangan</th><th>Jumlah</th><th></th></tr>
    <?php while ($p = $daftar->fetch_assoc()): ?>
      <tr><td><?= e(date('d/m', strtotime($p['tanggal']))) ?></td><td><?= e($p['keterangan']) ?> <span class="ad-badge"><?= e($p['kategori']) ?></span></td><td><?= rupiah((int)$p['jumlah']) ?></td>
      <td><form method="post"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="bulan" value="<?= e($bulan) ?>"><button class="ad-btn kecil abu" onclick="return confirm('Hapus?')">Hapus</button></form></td></tr>
    <?php endwhile; ?></table></div><?php endif; ?>
  </div>
  <div class="ad-card">
    <h2>Catat Pengeluaran Baru</h2>
    <form class="lp-form" method="post">
      <input type="hidden" name="aksi" value="tambah"><input type="hidden" name="bulan" value="<?= e($bulan) ?>">
      <label>Tanggal<input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required></label>
      <label>Kategori<select name="kategori"><option>Bahan baku</option><option>Operasional</option><option>Gaji</option><option>Marketing</option><option>Lainnya</option></select></label>
      <label class="lebar">Untuk apa?<input name="keterangan" maxlength="150" placeholder="mis. Belanja ayam" required></label>
      <label>Jumlah (Rp)<input type="number" name="jumlah" min="1" required></label>
      <label>&nbsp;<button class="ad-btn" style="width:100%">Simpan</button></label>
    </form>
  </div>
</div>
<?php include '../includes/admin_footer.php'; ?>