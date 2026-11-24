<?php
ob_start();
$id      = (int)($_GET['id'] ?? 0);
$halaman = 'produk';
$judul   = $id ? 'Edit Produk' : 'Tambah Produk';
include '../includes/admin_header.php';

function e($s) { return htmlspecialchars((string)$s); }

$data = ['nama' => '', 'kategori_id' => '', 'harga' => '', 'stok' => '', 'deskripsi' => '', 'gambar' => 'default.jpg'];

if ($id) {
    $s = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE id = ?");
    mysqli_stmt_bind_param($s, "i", $id);
    mysqli_stmt_execute($s);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
    if (!$row) { header("Location: produk.php"); exit; }
    $data = $row;
}

$pilih_topping = [];
if ($id) {
    $r = mysqli_query($koneksi, "SELECT topping_id FROM produk_topping WHERE produk_id = $id");
    while ($t = mysqli_fetch_assoc($r)) $pilih_topping[] = (int)$t['topping_id'];
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama']        = trim($_POST['nama'] ?? '');
    $data['kategori_id'] = (int)($_POST['kategori_id'] ?? 0);
    $data['harga']       = (int)($_POST['harga'] ?? 0);
    $data['stok']        = (int)($_POST['stok'] ?? 0);
    $data['deskripsi']   = trim($_POST['deskripsi'] ?? '');
    $pilih_topping       = array_map('intval', $_POST['topping'] ?? []);

    if ($data['nama'] === '') {
        $error = "Nama produk wajib diisi.";
    } elseif ($data['harga'] <= 0) {
        $error = "Harga harus lebih dari 0.";
    } elseif ($data['stok'] < 0) {
        $error = "Stok tidak boleh negatif.";
    }

    $gambar_baru = null;
    if (!$error && !empty($_FILES['gambar']['name'])) {
        $f   = $_FILES['gambar'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $error = "Upload gambar gagal. Coba lagi.";
        } elseif (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $error = "Gambar harus berformat JPG, PNG, atau WEBP.";
        } elseif ($f['size'] > 2 * 1024 * 1024) {
            $error = "Ukuran gambar maksimal 2 MB.";
        } elseif (!@getimagesize($f['tmp_name'])) {
            $error = "File itu bukan gambar yang valid.";
        } else {
            $folder = __DIR__ . '/../assets/img/produk/';
            if (!is_dir($folder)) mkdir($folder, 0777, true);
            $gambar_baru = uniqid('produk_') . '.' . $ext;
            move_uploaded_file($f['tmp_name'], $folder . $gambar_baru);
        }
    }

    if (!$error) {
        $kat    = $data['kategori_id'] ?: null;
        $gambar = $gambar_baru ?? $data['gambar'];

        if ($id) {
            $s = mysqli_prepare($koneksi, "UPDATE produk SET kategori_id=?, nama=?, deskripsi=?, harga=?, stok=?, gambar=? WHERE id=?");
            mysqli_stmt_bind_param($s, "issiisi", $kat, $data['nama'], $data['deskripsi'], $data['harga'], $data['stok'], $gambar, $id);
            mysqli_stmt_execute($s);
            $pid = $id;

            if ($gambar_baru && $data['gambar'] !== 'default.jpg') {
                @unlink(__DIR__ . '/../assets/img/produk/' . $data['gambar']);
            }
            $pesan = "Produk berhasil diperbarui.";
        } else {
            $s = mysqli_prepare($koneksi, "INSERT INTO produk (kategori_id, nama, deskripsi, harga, stok, gambar) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($s, "issiis", $kat, $data['nama'], $data['deskripsi'], $data['harga'], $data['stok'], $gambar);
            mysqli_stmt_execute($s);
            $pid = mysqli_insert_id($koneksi);
            $pesan = "Produk baru berhasil ditambahkan.";
        }

        mysqli_query($koneksi, "DELETE FROM produk_topping WHERE produk_id = $pid");
        foreach ($pilih_topping as $tid) {
            mysqli_query($koneksi, "INSERT INTO produk_topping (produk_id, topping_id) VALUES ($pid, $tid)");
        }

        $_SESSION['flash'] = ['tipe' => 'ok', 'pesan' => $pesan];
        header("Location: produk.php");
        exit;
    }
}

$kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id");
$topping  = mysqli_query($koneksi, "SELECT * FROM topping WHERE tersedia = 1 ORDER BY id");
$ada_gambar = $data['gambar'] !== 'default.jpg'
              && file_exists(__DIR__ . '/../assets/img/produk/' . $data['gambar']);
?>

<div class="kartu form-produk">
  <?php if ($error): ?><div class="info err"><?= e($error) ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label>Nama Produk</label>
    <input type="text" name="nama" placeholder="Contoh: Rice Bowl Ayam Teriyaki" value="<?= e($data['nama']) ?>" required>

    <div class="form-3kol">
      <div>
        <label>Kategori</label>
        <select name="kategori_id">
          <option value="0">Pilih kategori</option>
          <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
            <option value="<?= $k['id'] ?>" <?= (int)$data['kategori_id'] === (int)$k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label>Harga (Rp)</label>
        <input type="number" name="harga" min="0" placeholder="15000" value="<?= e($data['harga']) ?>" required>
      </div>
      <div>
        <label>Stok</label>
        <input type="number" name="stok" min="0" placeholder="20" value="<?= e($data['stok']) ?>" required>
      </div>
    </div>

    <label>Deskripsi</label>
    <textarea name="deskripsi" rows="3" placeholder="Tulis deskripsi produk..."><?= e($data['deskripsi']) ?></textarea>

    <label>Upload Gambar <?= $id ? '(kosongkan jika tidak ingin mengganti)' : '' ?></label>
    <div class="upload-wrap">
      <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp" onchange="tampilPreview(this)">
      <div class="preview" id="preview">
        <?php if ($ada_gambar): ?>
          <img src="<?= BASE_URL ?>assets/img/produk/<?= e($data['gambar']) ?>" alt="">
        <?php else: ?>Preview<?php endif; ?>
      </div>
    </div>

    <label>Topping yang tersedia untuk produk ini</label>
    <div class="topping-list">
      <?php while ($t = mysqli_fetch_assoc($topping)): ?>
        <label>
          <input type="checkbox" name="topping[]" value="<?= $t['id'] ?>" <?= in_array((int)$t['id'], $pilih_topping) ? 'checked' : '' ?>>
          <?= e($t['nama']) ?>
        </label>
      <?php endwhile; ?>
    </div>

    <div class="form-aksi">
      <a href="produk.php" class="btn abu">Batal</a>
      <button type="submit" class="btn"><?= $id ? 'Simpan Perubahan' : 'Simpan' ?></button>
    </div>
  </form>
</div>

<script>
function tampilPreview(input) {
  if (input.files && input.files[0]) {
    document.getElementById('preview').innerHTML =
      '<img src="' + URL.createObjectURL(input.files[0]) + '" alt="">';
  }
}
</script>

<?php include '../includes/admin_footer.php'; ?>