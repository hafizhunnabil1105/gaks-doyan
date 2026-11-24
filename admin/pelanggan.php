<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
require_once '../includes/grafik.php';
wajib_admin();
$judul = 'Pelanggan';

$tu = pilih_tabel($koneksi, ['users','user','pengguna','akun']);
$c  = kolom_pesanan($koneksi);
$kRole = $tu ? pilih_kolom($koneksi, $tu, ['role','peran','level']) : null;
$kHp   = $tu ? pilih_kolom($koneksi, $tu, ['telepon','no_hp','hp','no_telp','whatsapp']) : null;

function segmen(int $n, int $belanja): array {
    if ($n >= 5 || $belanja >= 500000) return ['VIP', 'hijau'];
    if ($n >= 2) return ['Aktif', 'kuning'];
    if ($n >= 1) return ['Baru', ''];
    return ['Belum beli', 'abu'];
}

// ===== detail satu pelanggan =====
$id = (int)($_GET['id'] ?? 0);
if ($tu && $id && $c['user']) {
    $st = $koneksi->prepare("SELECT * FROM `$tu` WHERE id=?"); $st->bind_param('i', $id); $st->execute();
    $u = $st->get_result()->fetch_assoc();
    if ($u) {
        $riw = [];
        $st = $koneksi->prepare("SELECT * FROM pesanan WHERE `{$c['user']}`=? ORDER BY id DESC"); $st->bind_param('i', $id); $st->execute();
        $r = $st->get_result(); $belanja = 0;
        while ($b = $r->fetch_assoc()) { $riw[] = $b; if (($b['status'] ?? '') !== 'Batal') $belanja += (int)f($b, ['total','total_harga','grand_total','total_bayar'], 0); }
        [$sg, $sk] = segmen(count($riw), $belanja);
        $hp = $kHp ? (string)($u[$kHp] ?? '') : '';
        $judul = 'Detail Pelanggan';
        include '../includes/admin_header.php'; ?>
        <p><a class="ad-btn kecil abu" href="pelanggan.php">&larr; Kembali</a></p>
        <div class="ad-card">
          <h2><?= e(f($u, ['nama','name','username'], 'Pelanggan')) ?> <span class="ad-badge <?= $sk ?>"><?= $sg ?></span></h2>
          <p class="ad-sub"><?= e($u['email'] ?? '') ?><?= $hp ? ' &middot; ' . e($hp) : '' ?></p>
          <div class="ad-stat" style="margin:0">
            <div class="ad-st"><small>Total Pesanan</small><b><?= count($riw) ?></b></div>
            <div class="ad-st h"><small>Total Belanja</small><b><?= rupiah($belanja) ?></b></div>
            <div class="ad-st c"><small>Rata-rata / Pesanan</small><b><?= rupiah(count($riw) ? intdiv($belanja, count($riw)) : 0) ?></b></div>
            <div class="ad-st k"><small>Hubungi</small><b style="font-size:16px"><?= $hp ? '<a class="ad-btn kecil" target="_blank" href="' . e('https://wa.me/' . hp_wa($hp) . '?text=' . rawurlencode('Halo ' . f($u, ['nama'], '') . ', terima kasih sudah memesan di ' . NAMA_BRAND . '!')) . '">Chat WhatsApp</a>' : '-' ?></b></div>
          </div>
        </div>
        <div class="ad-card"><h2>Riwayat Pesanan</h2><p class="ad-sub">Semua interaksi pemesanan pelanggan ini</p>
        <?php if (!$riw): ?><div class="ad-kosong">Belum pernah memesan.</div><?php else: ?>
        <div class="ad-scroll"><table class="ad-tabel"><tr><th>Kode</th><th>Total</th><th>Status</th><th>Bayar</th><th></th></tr>
        <?php foreach ($riw as $p): ?><tr>
          <td><b><?= e(f($p, ['kode','kode_pesanan'], '#' . $p['id'])) ?></b></td>
          <td><?= rupiah((int)f($p, ['total','total_harga','grand_total','total_bayar'], 0)) ?></td>
          <td><?= e($p['status'] ?? '') ?></td><td><?= e($p['status_bayar'] ?? '-') ?></td>
          <td><a class="ad-btn kecil biru" href="pesanan_detail.php?id=<?= (int)$p['id'] ?>">Detail</a></td></tr>
        <?php endforeach; ?></table></div><?php endif; ?></div>
        <?php include '../includes/admin_footer.php'; exit;
    }
}

// ===== daftar =====
$q = trim($_GET['q'] ?? '');
$daftar = [];
if ($tu) {
    $sql = "SELECT * FROM `$tu`";
    $w = []; $par = []; $tp = '';
    if ($kRole) $w[] = "`$kRole` <> 'admin'";
    if ($q !== '') { $w[] = "(nama LIKE ? OR email LIKE ?)"; $par = ["%$q%", "%$q%"]; $tp = 'ss'; }
    if ($w) $sql .= ' WHERE ' . implode(' AND ', $w);
    $sql .= ' ORDER BY id DESC';
    $st = $koneksi->prepare($sql);
    if ($par) $st->bind_param($tp, ...$par);
    $st->execute(); $r = $st->get_result();
    while ($u = $r->fetch_assoc()) {
        $n = 0; $belanja = 0; $terakhir = '';
        if ($c['user']) {
            $s2 = $koneksi->prepare("SELECT COUNT(*) n, COALESCE(SUM(" . ($c['total'] ? "`{$c['total']}`" : '0') . "),0) s" . ($c['tgl'] ? ", MAX(`{$c['tgl']}`) t" : ", '' t") . " FROM pesanan WHERE `{$c['user']}`=? AND status<>'Batal'");
            $s2->bind_param('i', $u['id']); $s2->execute();
            $x = $s2->get_result()->fetch_assoc();
            $n = (int)$x['n']; $belanja = (int)$x['s']; $terakhir = $x['t'];
        }
        $u['_n'] = $n; $u['_b'] = $belanja; $u['_t'] = $terakhir;
        $daftar[] = $u;
    }
}
$rk = ['VIP' => 0, 'Aktif' => 0, 'Baru' => 0, 'Belum beli' => 0];
foreach ($daftar as $u) $rk[segmen($u['_n'], $u['_b'])[0]]++;

include '../includes/admin_header.php'; ?>
<div class="ad-stat">
  <div class="ad-st"><small>Total Pelanggan</small><b><?= count($daftar) ?></b></div>
  <div class="ad-st h"><small>VIP (&ge;5 pesanan / &ge;Rp500rb)</small><b><?= $rk['VIP'] ?></b></div>
  <div class="ad-st k"><small>Aktif (2+ pesanan)</small><b><?= $rk['Aktif'] ?></b></div>
  <div class="ad-st c"><small>Belum Pernah Beli</small><b><?= $rk['Belum beli'] ?></b></div>
</div>
<div class="ad-card">
  <h2>Data Pelanggan (CRM)</h2>
  <p class="ad-sub">Klik Detail untuk melihat riwayat pesanan dan menghubungi lewat WhatsApp.</p>
  <form class="ad-form baris" method="get" style="margin-bottom:16px">
    <label>Cari nama / email<input name="q" value="<?= e($q) ?>" placeholder="Ketik lalu Enter"></label>
    <div><button class="ad-btn biru">Cari</button></div>
  </form>
  <?php if (!$tu): ?><div class="ad-hint">Tabel pengguna tidak ditemukan.</div>
  <?php elseif (!$daftar): ?><div class="ad-kosong">Belum ada pelanggan.</div>
  <?php else: ?><div class="ad-scroll"><table class="ad-tabel">
    <tr><th>Nama</th><th>Email</th><th>Pesanan</th><th>Total Belanja</th><th>Segmen</th><th></th></tr>
    <?php foreach ($daftar as $u): [$sg, $sk] = segmen($u['_n'], $u['_b']); ?>
    <tr><td><b><?= e(f($u, ['nama','name','username'], '-')) ?></b></td><td><?= e($u['email'] ?? '') ?></td>
      <td><?= $u['_n'] ?></td><td><?= rupiah($u['_b']) ?></td>
      <td><span class="ad-badge <?= $sk ?>"><?= $sg ?></span></td>
      <td><a class="ad-btn kecil biru" href="pelanggan.php?id=<?= (int)$u['id'] ?>">Detail</a></td></tr>
    <?php endforeach; ?></table></div><?php endif; ?>
</div>
<?php include '../includes/admin_footer.php'; ?>