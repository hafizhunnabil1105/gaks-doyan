<?php
include 'config/koneksi.php';

if (isset($_SESSION['user'])) {
    $tujuan = $_SESSION['user']['role'] === 'admin' ? 'admin/index.php' : 'index.php';
    header("Location: " . BASE_URL . $tujuan);
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = mysqli_prepare($koneksi, "SELECT id, nama, email, password, role FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'nama'  => $user['nama'],
            'email' => $user['email'],
            'role'  => $user['role']
        ];
        $tujuan = $user['role'] === 'admin' ? 'admin/index.php' : 'index.php';
        header("Location: " . BASE_URL . $tujuan);
        exit;
    } else {
        $error = "Email atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - <?= NAMA_BRAND ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= aset('assets/css/auth.css') ?>">
</head>
<body>
  <div class="auth-kartu">
    <div class="auth-brand">
      <img class="brand-logo" src="<?= aset('assets/img/logo.png') ?>" alt="Ga'ks Doyan">
      <h2>Makan enak,<br>nggak perlu ribet.</h2>
      <p>Rice bowl dan minuman segar dengan topping favoritmu.</p>
    </div>

    <div class="auth-form">
      <h1 class="judul-form">Selamat datang</h1>
      <p class="sub-form">Masuk untuk mulai memesan</p>

      <?php if ($error): ?><div class="alert"><?= $error ?></div><?php endif; ?>

      <form method="POST">
        <div class="kolom">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" placeholder="nama@email.com" required autofocus>
        </div>

        <div class="kolom sandi">
          <label for="password">Password</label>
          <input id="password" type="password" name="password" placeholder="Masukkan password" required>
          <button type="button" class="mata" aria-label="Tampilkan password" onclick="lihat(this)">
            <svg viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>

        <button type="submit" class="btn-login">Masuk</button>
      </form>

      <p class="bawah">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
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