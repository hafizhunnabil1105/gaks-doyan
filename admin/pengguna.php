<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
require_once '../includes/skema.php';
wajib_admin();
$judul = 'Pengguna & Hak Akses';
$tb = pilih_tabel($koneksi, ['users','user','pengguna','akun']);
$kRole = $tb ? pilih_kolom($koneksi, $tb, ['role','level','peran']) : null;
$kId = $tb ? (pilih_kolom($koneksi, $tb, ['id','user_id','id_user']) ?: 'id') : 'id';
$meId = (int)($_SESSION['user']['id'] ?? 0);
if ($tb && $kRole && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0); $a = $_POST['aksi'] ?? '';
    if ($id && $id !== $meId) {
        if ($a === 'jadikan_admin' || $a === 'jadikan_pelanggan') {
            $v = $a === 'jadikan_admin' ? 'admin' : 'pelanggan';
            $s = $koneksi->prepare("UPDATE `$tb` SET `$kRole`=? WHERE `$kId`=?"); $s->bind_param('si', $v, $id); $s->execute();
            $_SESSION['flash'] = 'Hak akses diperbarui.';
        }
        if ($a === 'hapus') { $s = $koneksi->prepare("DELETE FROM `$tb` WHERE `$kId`=?"); $s->bind_param('i', $id); try { $s->execute(); $_SESSION['flash'] = 'Pengguna dihapus.'; } catch (Throwable $x) { $_SESSION['flash'] = 'Tidak bisa dihapus karena punya riwayat pesanan.'; } }
    } else { $_SESSION['flash'] = 'Akun yang sedang dipakai tidak bisa diubah.'; }
    header('Location: pengguna.php'); exit;
}
$daftar = $tb ? $koneksi->query("SELECT * FROM `$tb` ORDER BY `$kId` ASC") : null;
include '../includes/admin_header.php'; ?>
<div class="ad-card">
  <h2>Manajemen Pengguna</h2>
  <p class="ad-sub"><b>Admin</b> dapat mengelola seluruh panel. <b>Pelanggan</b> hanya dapat memesan dan melihat pesanannya sendiri.</p>
  <?php if (!$daftar): ?><div class="ad-kosong">Tabel pengguna tidak ditemukan.</div><?php else: ?>
  <div class="ad-scroll"><table class="ad-tabel"><tr><th>Nama</th><th>Email</th><th>Hak akses</th><th></th></tr>
  <?php while ($u = $daftar->fetch_assoc()): $role = $kRole ? $u[$kRole] : '-'; $uid = (int)$u[$kId]; ?>
    <tr><td><b><?= e(f($u, ['nama','name','username'], '-')) ?></b><?= $uid === $meId ? ' <small>(Anda)</small>' : '' ?></td>
      <td><?= e(f($u, ['email'], '-')) ?></td>
      <td><span class="ad-badge <?= $role === 'admin' ? 'coral' : 'hijau' ?>"><?= e(ucfirst($role)) ?></span></td>
      <td><?php if ($uid !== $meId): ?><form method="post" class="ad-aksi"><input type="hidden" name="id" value="<?= $uid ?>">
        <?php if ($role === 'admin'): ?><button class="ad-btn kecil abu" name="aksi" value="jadikan_pelanggan" onclick="return confirm('Cabut akses admin?')">Jadikan Pelanggan</button>
        <?php else: ?><button class="ad-btn kecil biru" name="aksi" value="jadikan_admin" onclick="return confirm('Beri akses admin?')">Jadikan Admin</button><?php endif; ?>
        <button class="ad-btn kecil abu" name="aksi" value="hapus" onclick="return confirm('Hapus pengguna ini?')">Hapus</button></form><?php endif; ?></td></tr>
  <?php endwhile; ?></table></div><?php endif; ?>
</div>
<?php include '../includes/admin_footer.php'; ?>