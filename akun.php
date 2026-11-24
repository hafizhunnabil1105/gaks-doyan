<?php
require_once 'config/koneksi.php';
require_once 'includes/auth.php';
require_once 'includes/skema.php';
wajib_login();
$judul = 'Akun Saya';
$tu = pilih_tabel($koneksi, ['users','user','pengguna','akun']);
$id = (int)$_SESSION['user']['id']; $pesan = ''; $err = false;
$s = $koneksi->prepare("SELECT * FROM `$tu` WHERE id=?"); $s->bind_param('i', $id); $s->execute(); $u = $s->get_result()->fetch_assoc();
$kHp = pilih_kolom($koneksi, $tu, ['telepon','no_hp','hp','no_telp','whatsapp']);
$kPw = pilih_kolom($koneksi, $tu, ['password','kata_sandi','sandi']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['aksi'] ?? '') === 'profil') {
        $n = trim($_POST['nama'] ?? '');
        if ($n !== '') {
            $s = $koneksi->prepare("UPDATE `$tu` SET nama=? WHERE id=?"); $s->bind_param('si', $n, $id); $s->execute();
            if ($kHp) { $h = trim($_POST['hp'] ?? ''); $s = $koneksi->prepare("UPDATE `$tu` SET `$kHp`=? WHERE id=?"); $s->bind_param('si', $h, $id); $s->execute(); }
            $_SESSION['user']['nama'] = $n; $pesan = 'Profil disimpan.';
        } else { $pesan = 'Nama wajib diisi.'; $err = true; }
    } elseif ($kPw) {
        $lama = $_POST['lama'] ?? ''; $baru = $_POST['baru'] ?? '';
        if (!password_verify($lama, $u[$kPw])) { $pesan = 'Kata sandi lama salah.'; $err = true; }
        elseif (strlen($baru) < 6) { $pesan = 'Kata sandi baru minimal 6 karakter.'; $err = true; }
        else { $h = password_hash($baru, PASSWORD_DEFAULT); $s = $koneksi->prepare("UPDATE `$tu` SET `$kPw`=? WHERE id=?"); $s->bind_param('si', $h, $id); $s->execute(); $pesan = 'Kata sandi diganti.'; }
    }
    $s = $koneksi->prepare("SELECT * FROM `$tu` WHERE id=?"); $s->bind_param('i', $id); $s->execute(); $u = $s->get_result()->fetch_assoc();
}
include 'includes/header.php'; ?>
<div class="gd-wrap gd-hal"><?php if ($pesan): ?><div class="gd-flash <?= $err ? 'err' : '' ?>"><?= e($pesan) ?></div><?php endif; ?>
<div class="gd-dua">
  <div class="gd-kotak"><h2>Profil</h2><form class="gd-form" method="post"><input type="hidden" name="aksi" value="profil">
    <label>Nama<input name="nama" value="<?= e($u['nama'] ?? '') ?>" required></label>
    <label>Email<input value="<?= e($u['email'] ?? '') ?>" disabled></label>
    <?php if ($kHp): ?><label>No. WhatsApp<input name="hp" value="<?= e($u[$kHp] ?? '') ?>"></label><?php endif; ?>
    <button class="gd-btn">Simpan</button></form></div>
  <div class="gd-kotak"><h2>Ganti Kata Sandi</h2><form class="gd-form" method="post"><input type="hidden" name="aksi" value="sandi">
    <label>Sandi lama<input type="password" name="lama" required></label><label>Sandi baru<input type="password" name="baru" required></label>
    <button class="gd-btn biru">Ganti Sandi</button></form></div>
</div></div>
<?php include 'includes/footer.php'; ?>