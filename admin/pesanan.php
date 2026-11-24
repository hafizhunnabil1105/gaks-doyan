<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
require_once '../includes/grafik.php';
wajib_admin();
$judul = 'Pesanan';
$c = kolom_pesanan($koneksi);
$tahap = ['Semua', 'Baru', 'Diproses', 'Diantar', 'Selesai', 'Batal'];
$st = $_GET['status'] ?? 'Semua'; if (!in_array($st, $tahap, true)) $st = 'Semua';
$q = trim($_GET['q'] ?? '');

$where = []; $tipe = ''; $par = [];
if ($st !== 'Semua') { $where[] = 'status=?'; $tipe .= 's'; $par[] = $st; }
if ($q !== '') {
    $kn = pilih_kolom($koneksi, 'pesanan', ['nama_pelanggan','nama_penerima','nama']);
    $w = 'kode LIKE ?'; $tipe .= 's'; $par[] = "%$q%";
    if ($kn) { $w .= " OR `$kn` LIKE ?"; $tipe .= 's'; $par[] = "%$q%"; }
    $where[] = "($w)";
}
$urut = $c['tgl'] ? "`{$c['tgl']}` DESC" : 'id DESC';
$sql = "SELECT * FROM pesanan" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY $urut LIMIT 200";
$s = $koneksi->prepare($sql);
if ($par) $s->bind_param($tipe, ...$par);
$s->execute(); $daftar = $s->get_result();

$hitung = ['Semua' => 0];
$r = $koneksi->query("SELECT status, COUNT(*) n FROM pesanan GROUP BY status");
while ($b = $r->fetch_assoc()) { $hitung[$b['status']] = (int)$b['n']; $hitung['Semua'] += (int)$b['n']; }
include '../includes/admin_header.php'; ?>
<div class="ad-filter">
  <?php foreach ($tahap as $t): ?>
    <a class="<?= $st === $t ? 'on' : '' ?>" href="pesanan.php?status=<?= urlencode($t) ?>"><?= $t ?> (<?= (int)($hitung[$t] ?? 0) ?>)</a>
  <?php endforeach; ?>
</div>
<div class="ad-card">
  <form method="get" class="ad-form baris" style="margin-bottom:14px">
    <input type="hidden" name="status" value="<?= e($st) ?>">
    <label>Cari kode atau nama<input name="q" value="<?= e($q) ?>" placeholder="GD2611..."></label>
    <div class="ad-aksi"><button class="ad-btn biru">Cari</button><a class="ad-btn abu" href="rekap_unduh.php">Unduh CSV</a></div>
  </form>
  <?php if ($daftar->num_rows === 0): ?><div class="ad-kosong">Belum ada pesanan pada kategori ini.</div><?php else: ?>
  <div class="ad-scroll"><table class="ad-tabel">
    <tr><th>Kode</th><th>Pelanggan</th><th>Metode</th><th>Total</th><th>Status</th><th>Bayar</th><th>Waktu</th><th></th></tr>
    <?php while ($p = $daftar->fetch_assoc()): $s2 = $p['status'] ?? ''; ?>
    <tr>
      <td><b><?= e(f($p, ['kode','kode_pesanan'], '#' . $p['id'])) ?></b></td>
      <td><?= e(f($p, ['nama_pelanggan','nama_penerima','nama'], '-')) ?><br><small><?= e(f($p, ['no_hp','telepon','hp'], '')) ?></small></td>
      <td><?= e(f($p, ['metode_bayar','metode'], '-')) ?></td>
      <td><?= rupiah((int)f($p, ['total','total_harga','grand_total','total_bayar'], 0)) ?></td>
      <td><span class="ad-badge <?= $s2 === 'Selesai' ? 'hijau' : ($s2 === 'Baru' ? 'coral' : ($s2 === 'Batal' ? 'abu' : 'kuning')) ?>"><?= e($s2) ?></span></td>
      <td><?= e($p['status_bayar'] ?? '-') ?></td>
      <td><small><?= $c['tgl'] && !empty($p[$c['tgl']]) ? e(date('d M Y H:i', strtotime($p[$c['tgl']]))) : '-' ?></small></td>
      <td><a class="ad-btn kecil biru" href="pesanan_detail.php?id=<?= (int)$p['id'] ?>">Detail</a></td>
    </tr>
    <?php endwhile; ?>
  </table></div><?php endif; ?>
</div>
<?php include '../includes/admin_footer.php'; ?>