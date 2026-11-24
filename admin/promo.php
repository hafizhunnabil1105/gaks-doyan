<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
require_once '../includes/skema.php';
wajib_admin();
$judul = 'Promo';

$kJudul = pilih_kolom($koneksi, 'promo', ['judul','nama','nama_promo','title']);
$kIsi   = pilih_kolom($koneksi, 'promo', ['deskripsi','keterangan','isi','detail']);
$kTag   = pilih_kolom($koneksi, 'promo', ['tag','label']);
$kAktif = pilih_kolom($koneksi, 'promo', ['aktif','status','tampil']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    if ($aksi === 'hapus' && $id) { $s = $koneksi->prepare("DELETE FROM promo WHERE id=?"); $s->bind_param('i', $id); $s->execute(); $_SESSION['flash'] = 'Promo dihapus.'; }
    elseif ($aksi === 'aktif' && $id && $kAktif) { $s = $koneksi->prepare("UPDATE promo SET `$kAktif` = 1 - `$kAktif` WHERE id=?"); $s->bind_param('i', $id); $s->execute(); }
    elseif ($aksi === 'simpan' && $kJudul) {
        $nilai = [$kJudul => trim($_POST['judul'] ?? '')];
        if ($kIsi) $nilai[$kIsi] = trim($_POST['isi'] ?? '');
        if ($kTag) $nilai[$kTag] = trim($_POST['tag'] ?? '');
        if ($id) {
            $set = implode(',', array_map(fn($k) => "`$k`=?", array_keys($nilai)));
            $s = $koneksi->prepare("UPDATE promo SET $set WHERE id=?");
            $s->bind_param(str_repeat('s', count($nilai)) . 'i', ...[...array_values($nilai), $id]);
        } else {
            if ($kAktif) $nilai[$kAktif] = '1';
            $kol = implode(',', array_map(fn($k) => "`$k`", array_keys($nilai)));
            $s = $koneksi->prepare("INSERT INTO promo ($kol) VALUES (" . implode(',', array_fill(0, count($nilai), '?')) . ")");
            $s->bind_param(str_repeat('s', count($nilai)), ...array_values($nilai));
        }
        $s->execute(); $_SESSION['flash'] = 'Promo disimpan.';
    }
    header('Location: promo.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) { $s = $koneksi->prepare("SELECT * FROM promo WHERE id=?"); $e1 = (int)$_GET['edit']; $s->bind_param('i', $e1); $s->execute(); $edit = $s->get_result()->fetch_assoc(); }
$daftar = $koneksi->query("SELECT * FROM promo ORDER BY id DESC");
include '../includes/admin_header.php'; ?>
<div class="ad-dua">
  <div class="ad-card">
    <h2>Daftar Promo</h2><p class="ad-sub">Promo aktif tampil di beranda dan halaman Promo.</p>
    <?php if (!$daftar || $daftar->num_rows === 0): ?><div class="ad-kosong">Belum ada promo.</div><?php else: ?>
    <div class="ad-scroll"><table class="ad-tabel"><tr><th>Promo</th><th>Tag</th><th>Status</th><th></th></tr>
    <?php while ($p = $daftar->fetch_assoc()): $on = $kAktif ? (int)$p[$kAktif] === 1 : true; ?>
      <tr><td><b><?= e($kJudul ? $p[$kJudul] : '#' . $p['id']) ?></b><br><small><?= e($kIsi ? mb_strimwidth((string)$p[$kIsi], 0, 70, '...') : '') ?></small></td>
      <td><?= $kTag && $p[$kTag] ? '<span class="ad-badge coral">' . e($p[$kTag]) . '</span>' : '-' ?></td>
      <td><span class="ad-badge <?= $on ? 'hijau' : 'abu' ?>"><?= $on ? 'Aktif' : 'Nonaktif' ?></span></td>
      <td><form method="post" class="ad-aksi"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <a class="ad-btn kecil biru" href="?edit=<?= (int)$p['id'] ?>">Edit</a>
        <?php if ($kAktif): ?><button class="ad-btn kecil abu" name="aksi" value="aktif"><?= $on ? 'Matikan' : 'Aktifkan' ?></button><?php endif; ?>
        <button class="ad-btn kecil abu" name="aksi" value="hapus" onclick="return confirm('Hapus promo ini?')">Hapus</button></form></td></tr>
    <?php endwhile; ?></table></div><?php endif; ?>
  </div>
  <div class="ad-card">
    <h2><?= $edit ? 'Edit Promo' : 'Tambah Promo' ?></h2><p class="ad-sub">Gambar promo tetap memakai yang sudah ada.</p>
    <?php if (!$kJudul): ?><div class="ad-hint">Kolom judul promo tidak ditemukan.</div><?php else: ?>
    <form class="ad-form" method="post"><input type="hidden" name="aksi" value="simpan"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <label>Judul<input name="judul" required value="<?= e($edit[$kJudul] ?? '') ?>"></label>
      <?php if ($kIsi): ?><label>Deskripsi<textarea name="isi" rows="3"><?= e($edit[$kIsi] ?? '') ?></textarea></label><?php endif; ?>
      <?php if ($kTag): ?><label>Tag (mis. Hemat, Baru)<input name="tag" maxlength="30" value="<?= e($edit[$kTag] ?? '') ?>"></label><?php endif; ?>
      <div class="ad-aksi"><button class="ad-btn">Simpan</button><?php if ($edit): ?><a class="ad-btn abu" href="promo.php">Batal</a><?php endif; ?></div>
    </form><?php endif; ?>
  </div>
</div>
<?php include '../includes/admin_footer.php'; ?>