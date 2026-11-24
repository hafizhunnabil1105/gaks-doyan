<?php
include 'config/koneksi.php';

$error = "";
$sukses = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $email    = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $ulang    = $_POST['ulang'];

    if (stripos($email, 'admin') !== false || stripos($nama, 'admin') !== false) {
        $error = "Nama atau email tidak boleh mengandung kata 'admin'.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $ulang) {
        $error = "Konfirmasi password tidak sama.";
    } else {
        $cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($cek, "s", $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Email sudah terdaftar.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = mysqli_prepare($koneksi, "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'customer')");
            mysqli_stmt_bind_param($ins, "sss", $nama, $email, $hash);
            mysqli_stmt_execute($ins);
            $sukses = "Pendaftaran berhasil! Silakan <a href='login.php'>login</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar - <?= NAMA_BRAND ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= aset('assets/css/auth.css') ?>">
</head>
<body>
  <div class="auth-kartu">
    <div class="auth-brand">
      <img class="brand-logo" src="<?= aset('assets/img/logo.png') ?>" alt="Ga'ks Doyan">
      <h2>Gabung dan<br>mulai pesan.</h2>
      <p>Buat akun untuk memesan rice bowl dan memantau pesananmu.</p>
    </div>

    <div class="auth-form">
      <h1 class="judul-form">Buat akun baru</h1>
      <p class="sub-form">Daftar gratis, hanya butuh semenit</p>

      <?php if ($error): ?><div class="alert"><?= $error ?></div><?php endif; ?>
      <?php if ($sukses): ?><div class="ok"><?= $sukses ?></div><?php endif; ?>

      <form method="POST">
        <div class="kolom">
          <label for="nama">Nama lengkap</label>
          <input id="nama" type="text" name="nama" placeholder="Nama kamu" required autofocus>
        </div>

        <div class="kolom">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" placeholder="nama@email.com" required>
        </div>

        <div class="kolom sandi">
          <label for="password">Password</label>
          <input id="password" type="password" name="password" placeholder="Minimal 6 karakter" required>
          <button type="button" class="mata" aria-label="Tampilkan password" onclick="lihat(this)">
            <svg viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>

        <div class="kolom sandi">
          <label for="ulang">Ulangi password</label>
          <input id="ulang" type="password" name="ulang" placeholder="Ketik ulang password" required>
          <button type="button" class="mata" aria-label="Tampilkan password" onclick="lihat(this)">
            <svg viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>

        <button type="submit" class="btn-login">Daftar</button>
      </form>

      <p class="bawah">Sudah punya akun? <a href="login.php">Login</a></p>
      <a class="kembali-home" href="index.php">&larr; Kembali ke beranda</a>
    </div>
  </div>

<script>
function lihat(tombol) {
  var input = tombol.parentElement.querySelector('input');
  input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>