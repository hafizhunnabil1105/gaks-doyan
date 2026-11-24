<?php
require_once __DIR__ . '/skema.php';
$_u = $_SESSION['user'] ?? null;
$_judul = $judul ?? $judul_halaman ?? 'Beranda';
$_logo = cari_aset(['logo*.png','logo*.jpg','logo*.jpeg','logo*.webp','logo*.svg']);
$_jml = 0;
foreach (($_SESSION['keranjang'] ?? []) as $_it) $_jml += is_array($_it) ? (int)($_it['qty'] ?? $_it['jumlah'] ?? 1) : (int)$_it;
$_hal = basename($_SERVER['SCRIPT_NAME'], '.php');
$_p = $_GET['p'] ?? '';
$_nav = [
  ['index.php', 'Beranda', $_hal === 'index'],
  ['menu.php', 'Menu', $_hal === 'menu'],
  ['promo.php', 'Promo', $_hal === 'promo'],
  ['halaman.php?p=carapesan', 'Cara Pesan', $_p === 'carapesan'],
  ['halaman.php?p=tentang', 'Tentang', $_p === 'tentang'],
  ['halaman.php?p=kontak', 'Kontak', $_p === 'kontak'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($_judul) ?> - <?= e(NAMA_BRAND) ?></title>
<link rel="stylesheet" href="<?= aset('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= aset('assets/css/final.css') ?>">
<link rel="stylesheet" href="<?= aset('assets/css/ringkas.css') ?>">
</head>
<body class="gd">
<input type="checkbox" id="gd-menu" class="gd-cek">
<header class="gd-nav">
  <div class="gd-nav-in">
    <a class="gd-logo" href="index.php">
      <?php if ($_logo): ?><img src="<?= aset($_logo) ?>" alt="<?= e(NAMA_BRAND) ?>"><?php else: ?><span class="gd-logo-t">G</span><?php endif; ?>
      <b><?= e(NAMA_BRAND) ?></b>
    </a>
    <label for="gd-menu" class="gd-burger" aria-label="Menu">&#9776;</label>
    <nav class="gd-links">
      <?php foreach ($_nav as [$h, $t, $on]): ?><a href="<?= $h ?>" class="<?= $on ? 'on' : '' ?>"><?= $t ?></a><?php endforeach; ?>
    </nav>
    <div class="gd-aksi">
      <?php if ($_u): ?>
        <a class="gd-ghost" href="<?= ($_u['role'] ?? '') === 'admin' ? 'admin/index.php' : 'pesanan_saya.php' ?>"><?= ($_u['role'] ?? '') === 'admin' ? 'Admin' : 'Pesanan Saya' ?></a>
        <?php if (($_u['role'] ?? '') !== 'admin'): ?><a class="gd-ghost" href="akun.php">Akun</a><?php endif; ?>
        <a class="gd-ghost" href="logout.php">Keluar</a>
      <?php else: ?>
        <a class="gd-ghost" href="login.php">Masuk</a>
      <?php endif; ?>
      <a class="gd-btn" href="keranjang.php">Keranjang<?php if ($_jml): ?> <span class="gd-cnt"><?= $_jml ?></span><?php endif; ?></a>
    </div>
  </div>
</header>