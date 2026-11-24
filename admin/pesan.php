<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
wajib_admin();
$judul = 'Pesan Masuk';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['aksi'] ?? '') === 'hapus') { $s = $koneksi->prepare("DELETE FROM pesan_kontak WHERE id=?"); $s->bind_param('i', $id); $s->execute(); }
    if (($_POST['aksi'] ?? '') === 'baca')  { $s = $koneksi->prepare("UPDATE pesan_kontak SET dibaca=1 WHERE id=?"); $s->bind_param('i', $id); $s->execute(); }
    header('Location: pesan.php'); exit;
}
$daftar = $koneksi->query("SELECT * FROM pesan_kontak ORDER BY id DESC");
include '../includes/admin_header.php'; ?>
<div class="ad-card">
  <h2>Pesan dari Formulir Kontak</h2>
  <p class="ad-sub">Pertanyaan dan masukan dari pengunjung website.</p>
  <?php if (!$daftar || $daftar->num_rows === 0): ?><div class="ad-kosong">Belum ada pesan masuk.</div><?php else: ?>
  <div class="ad-scroll"><table class="ad-tabel"><tr><th>Waktu</th><th>Pengirim</th><th>Pesan</th><th></th></tr>
  <?php while ($p = $daftar->fetch_assoc()): ?>
    <tr><td><?= e(date('d M Y H:i', strtotime($p['dibuat']))) ?></td>
      <td><b><?= e($p['nama']) ?></b><br><small><a href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a></small></td>
      <td style="max-width:380px"><?= nl2br(e($p['pesan'])) ?> <?= $p['dibaca'] ? '' : '<span class="ad-badge coral">Baru</span>' ?></td>
      <td><form method="post" class="ad-aksi"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <?php if (!$p['dibaca']): ?><button class="ad-btn kecil biru" name="aksi" value="baca">Tandai dibaca</button><?php endif; ?>
        <button class="ad-btn kecil abu" name="aksi" value="hapus" onclick="return confirm('Hapus pesan ini?')">Hapus</button></form></td></tr>
  <?php endwhile; ?></table></div><?php endif; ?>
</div>
<?php include '../includes/admin_footer.php'; ?>