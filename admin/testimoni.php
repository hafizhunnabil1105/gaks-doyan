<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
require_once '../includes/skema.php';
wajib_admin();
$judul = 'Testimoni';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0); $a = $_POST['aksi'] ?? '';
    if ($a === 'tampil' || $a === 'sembunyi') { $v = $a === 'tampil' ? 1 : 0; $s = $koneksi->prepare("UPDATE testimoni SET tampil=? WHERE id=?"); $s->bind_param('ii', $v, $id); $s->execute(); }
    if ($a === 'hapus') { $s = $koneksi->prepare("DELETE FROM testimoni WHERE id=?"); $s->bind_param('i', $id); $s->execute(); }
    header('Location: testimoni.php'); exit;
}
$daftar = $koneksi->query("SELECT * FROM testimoni ORDER BY tampil ASC, id DESC");
include '../includes/admin_header.php'; ?>
<div class="ad-card">
  <h2>Moderasi Testimoni</h2>
  <p class="ad-sub">Testimoni yang ditulis pelanggan masuk berstatus <b>Menunggu</b>. Setujui agar tampil di website.</p>
  <?php if (!$daftar || $daftar->num_rows === 0): ?><div class="ad-kosong">Belum ada testimoni.</div><?php else: ?>
  <div class="ad-scroll"><table class="ad-tabel"><tr><th>Pelanggan</th><th>Nilai</th><th>Isi</th><th>Status</th><th></th></tr>
  <?php while ($t = $daftar->fetch_assoc()): ?>
    <tr><td><b><?= e($t['nama']) ?></b><br><small><?= e(date('d M Y', strtotime($t['dibuat']))) ?></small></td>
      <td><?= (int)$t['bintang'] ?>/5</td><td style="max-width:360px"><?= e($t['isi']) ?></td>
      <td><?= $t['tampil'] ? '<span class="ad-badge hijau">Tampil</span>' : '<span class="ad-badge coral">Menunggu</span>' ?></td>
      <td><form method="post" class="ad-aksi"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <?php if ($t['tampil']): ?><button class="ad-btn kecil abu" name="aksi" value="sembunyi">Sembunyikan</button>
        <?php else: ?><button class="ad-btn kecil biru" name="aksi" value="tampil">Setujui</button><?php endif; ?>
        <button class="ad-btn kecil abu" name="aksi" value="hapus" onclick="return confirm('Hapus testimoni ini?')">Hapus</button></form></td></tr>
  <?php endwhile; ?></table></div><?php endif; ?>
</div>
<?php include '../includes/admin_footer.php'; ?>