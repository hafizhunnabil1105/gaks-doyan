<?php
// Kerangka halaman admin. Dipakai semua halaman di folder admin/.
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    // cari koneksi dengan nama variabel apa pun, jika belum ada muat dari config
    foreach (get_defined_vars() as $_v) if ($_v instanceof mysqli) { $koneksi = $_v; break; }
    if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
        require_once __DIR__ . '/../config/koneksi.php';
        if (!isset($koneksi) || !($koneksi instanceof mysqli)) foreach (get_defined_vars() as $_v) if ($_v instanceof mysqli) { $koneksi = $_v; break; }
    }
}
require_once __DIR__ . '/skema.php';
if (isset($koneksi) && $koneksi instanceof mysqli) pastikan_tabel($koneksi);
$_hal = basename($_SERVER['SCRIPT_NAME'], '.php');
$_judul = $judul ?? $judul_halaman ?? 'Admin';
$_user = $_SESSION['user'] ?? [];
$_baru = 0;
$_r = @$koneksi->query("SELECT COUNT(*) n FROM pesanan WHERE status='Baru'");
if ($_r) $_baru = (int)$_r->fetch_assoc()['n'];
$_pesan = 0;
$_r = @$koneksi->query("SELECT COUNT(*) n FROM pesan_kontak WHERE dibaca=0");
if ($_r) $_pesan = (int)$_r->fetch_assoc()['n'];

$_menu = [
  ['index',     'Dashboard', 'M3 12l9-9 9 9M5 10v10h14V10', 0],
  ['pesanan',   'Pesanan',   'M6 2h12l2 5v13H4V7zM4 7h16M9 11h6', $_baru],
  ['produk',    'Produk',    'M21 8l-9-5-9 5v8l9 5 9-5zM3 8l9 5 9-5M12 13v8', 0],
  ['promo',     'Promo',     'M20 12l-8 8-9-9V3h8zM7.5 7.5h.01', 0],
  ['pelanggan', 'Pelanggan', 'M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 21a8 8 0 0116 0', 0],
  ['pesan',     'Pesan Masuk','M4 5h16v12H8l-4 4z', $_pesan],
  ['testimoni', 'Testimoni', 'M12 3l2.8 5.8 6.2.9-4.5 4.4 1 6.2L12 17.3 6.5 20.3l1-6.2L3 9.7l6.2-.9z', 0],
  ['laporan',   'Laporan',   'M4 20V10M10 20V4M16 20v-8M22 20H2', 0],
  ['pengguna',  'Pengguna',  'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z', 0],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($_judul) ?> - Admin <?= e(NAMA_BRAND) ?></title>
<link rel="stylesheet" href="<?= aset('assets/css/admin.css') ?>">
<link rel="stylesheet" href="<?= aset('assets/css/admin-extra.css') ?>">
<style>
.ad-side{overflow-y:auto;padding:14px 12px}
.ad-brand{padding:2px 8px 12px}
.ad-nav{gap:2px;flex:none;margin-bottom:12px}
.ad-nav a{padding:8px 12px;font-size:14px}
.ad-bawah{margin-top:auto;padding-top:10px}
.ad-web,.ad-out{padding:8px}
.ad-keluar{background:#e8553d;color:#fff!important;text-decoration:none;padding:8px 16px;border-radius:10px;font-weight:700;font-size:14px}
.ad-keluar:hover{filter:brightness(1.08)}
.ad-logo{background:#fff;overflow:hidden}
.ad-logo img,.ad-avatar img{width:100%;height:100%;object-fit:cover;display:block}
.ad-profil i.ad-avatar{width:42px;height:42px;background:#fff;border:2px solid #e8553d;overflow:hidden}
</style>
</head>
<body class="ad">
<input type="checkbox" id="ad-toggle" class="ad-toggle">
<aside class="ad-side">
  <a class="ad-brand" href="index.php"><span class="ad-logo"><img src="<?= aset('assets/img/logo.png') ?>" alt="Ga'ks Doyan"></span><span><b>Ga'ks Doyan</b><small>Panel Admin</small></span></a>
  <nav class="ad-nav">
    <?php foreach ($_menu as [$f, $t, $ik, $lb]): ?>
      <a href="<?= $f ?>.php" class="<?= $_hal === $f || ($f === 'pesanan' && strpos($_hal, 'pesanan') === 0) || ($f === 'produk' && strpos($_hal, 'produk') === 0) ? 'on' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $ik ?>"/></svg>
        <span><?= e($t) ?></span>
        <?php if ($lb > 0): ?><em><?= $lb ?></em><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="ad-bawah">
    <a class="ad-web" href="../index.php" target="_blank">Lihat Website &#8599;</a>
    <a class="ad-out" href="../logout.php">Logout</a>
  </div>
</aside>
<label for="ad-toggle" class="ad-tutup"></label>
<div class="ad-isi">
  <header class="ad-top">
    <label for="ad-toggle" class="ad-burger">&#9776;</label>
    <h1><?= e($_judul) ?></h1>
    <div class="ad-profil">
      <span>Haloo Admin</span>
      <a class="ad-keluar" href="../logout.php">Logout</a>
      <i class="ad-avatar"><img src="<?= aset('assets/img/logo.png') ?>" alt="Ga'ks Doyan"></i>
    </div>
  </header>
  <main class="ad-main">
<?php $flash = $_SESSION['flash'] ?? null; if ($flash): unset($_SESSION['flash']); ?>
  <div class="ad-flash"><?= e(is_array($flash) ? ($flash['pesan'] ?? '') : $flash) ?></div>
<?php endif; ?>