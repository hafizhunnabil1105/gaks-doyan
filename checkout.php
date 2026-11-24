<?php
include 'config/koneksi.php';
include 'includes/auth.php';
wajib_login();

class StokKurang extends Exception {}

$keranjang = $_SESSION['keranjang'] ?? [];
if (!$keranjang) {
    header("Location: " . BASE_URL . "keranjang.php");
    exit;
}

$total = 0;
foreach ($keranjang as $it) { $total += $it['harga'] * $it['qty']; }

$data = [
    'nama'    => $_SESSION['user']['nama'],
    'no_hp'   => '',
    'alamat'  => '',
    'catatan' => '',
    'metode'  => 'COD',
];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama']    = trim($_POST['nama'] ?? '');
    $data['no_hp']   = trim($_POST['no_hp'] ?? '');
    $data['alamat']  = trim($_POST['alamat'] ?? '');
    $data['catatan'] = trim($_POST['catatan'] ?? '');
    $data['metode']  = $_POST['metode'] ?? 'COD';

    if ($data['nama'] === '' || $data['alamat'] === '') {
        $error = "Nama dan alamat wajib diisi.";
    } elseif (!preg_match('/^[0-9+\-\s]{9,20}$/', $data['no_hp'])) {
        $error = "Nomor HP tidak valid. Contoh: 081234567890.";
    } elseif (!in_array($data['metode'], ['COD', 'Transfer', 'QRIS'])) {
        $error = "Pilih metode pembayaran.";
    }

    if (!$error) {
        $per_produk = [];
        foreach ($keranjang as $it) {
            $per_produk[$it['produk_id']] = ($per_produk[$it['produk_id']] ?? 0) + $it['qty'];
        }

        mysqli_begin_transaction($koneksi);
        try {
            foreach ($per_produk as $pid => $jml) {
                $s = mysqli_prepare($koneksi, "SELECT nama, stok FROM produk WHERE id = ? AND tersedia = 1 FOR UPDATE");
                mysqli_stmt_bind_param($s, "i", $pid);
                mysqli_stmt_execute($s);
                $r = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
                if (!$r) throw new StokKurang("Ada produk di keranjangmu yang sudah tidak tersedia.");
                if ((int)$r['stok'] < $jml) {
                    throw new StokKurang("Stok " . $r['nama'] . " tidak cukup. Sisa " . (int)$r['stok'] . ".");
                }
                $u = mysqli_prepare($koneksi, "UPDATE produk SET stok = stok - ? WHERE id = ?");
                mysqli_stmt_bind_param($u, "ii", $jml, $pid);
                mysqli_stmt_execute($u);
            }

            $kode = 'GD' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $uid  = (int)$_SESSION['user']['id'];

            $s = mysqli_prepare($koneksi, "INSERT INTO pesanan
                (user_id, kode, nama_pelanggan, no_hp, alamat, catatan, metode_bayar, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($s, "issssssi", $uid, $kode, $data['nama'], $data['no_hp'],
                $data['alamat'], $data['catatan'], $data['metode'], $total);
            mysqli_stmt_execute($s);
            $pesanan_id = mysqli_insert_id($koneksi);

            $d = mysqli_prepare($koneksi, "INSERT INTO pesanan_detail
                (pesanan_id, produk_id, nama_produk, keterangan, harga, qty, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($keranjang as $it) {
                $nama_item = $it['nama'] . ($it['varian'] ? ' (' . $it['varian'] . ')' : '');
                $ket = [];
                if ($it['topping']) $ket[] = 'Topping: ' . implode(', ', $it['topping']);
                if ($it['catatan']) $ket[] = 'Catatan: ' . $it['catatan'];
                $ket = implode(' | ', $ket);
                $sub = $it['harga'] * $it['qty'];
                mysqli_stmt_bind_param($d, "iissiii", $pesanan_id, $it['produk_id'], $nama_item,
                    $ket, $it['harga'], $it['qty'], $sub);
                mysqli_stmt_execute($d);
            }

            mysqli_commit($koneksi);
            unset($_SESSION['keranjang']);
            header("Location: " . BASE_URL . "pesanan_sukses.php?kode=" . $kode);
            exit;
        } catch (StokKurang $ex) {
            mysqli_rollback($koneksi);
            $error = $ex->getMessage();
        } catch (Throwable $ex) {
            mysqli_rollback($koneksi);
            $error = "Terjadi kesalahan saat menyimpan pesanan. Coba lagi.";
        }
    }
}

include 'includes/header.php';
?>
<section class="wadah halaman">
  <a class="kembali" href="keranjang.php">&larr; Kembali ke keranjang</a>
  <h2 class="judul">Checkout</h2>

  <?php if ($error): ?><div class="info err"><?= e($error) ?></div><?php endif; ?>

  <form method="POST" class="co-grid">
    <div class="kartu-web co-form">
      <h3 style="margin:0">Data pengiriman</h3>

      <label class="f">Nama penerima</label>
      <input class="input-web" type="text" name="nama" value="<?= e($data['nama']) ?>" required>

      <label class="f">Nomor HP / WhatsApp</label>
      <input class="input-web" type="text" name="no_hp" placeholder="081234567890" value="<?= e($data['no_hp']) ?>" required>

      <label class="f">Alamat lengkap</label>
      <textarea class="input-web" name="alamat" rows="3" placeholder="Nama jalan, nomor rumah, patokan" required><?= e($data['alamat']) ?></textarea>

      <label class="f">Catatan untuk pesanan (opsional)</label>
      <input class="input-web" type="text" name="catatan" maxlength="150" value="<?= e($data['catatan']) ?>">

      <label class="f">Metode pembayaran</label>
      <div class="bayar-list">
        <label>
          <input type="radio" name="metode" value="COD" <?= $data['metode'] === 'COD' ? 'checked' : '' ?>>
          <span>COD<small>Bayar tunai saat pesanan diterima</small></span>
        </label>
        <label>
          <input type="radio" name="metode" value="Transfer" <?= $data['metode'] === 'Transfer' ? 'checked' : '' ?>>
          <span>Transfer bank<small>Transfer lalu kirim bukti via WhatsApp</small></span>
        </label>
        <label>
          <input type="radio" name="metode" value="QRIS" <?= $data['metode'] === 'QRIS' ? 'checked' : '' ?>>
          <span>QRIS<small>Scan kode QRIS lalu kirim bukti via WhatsApp</small></span>
        </label>
      </div>
    </div>

    <div class="kartu-web ringkas">
      <h3>Ringkasan pesanan</h3>
      <?php foreach ($keranjang as $it): ?>
        <div class="r-baris">
          <span><?= (int)$it['qty'] ?>x <?= e($it['nama']) ?><?= $it['varian'] ? ' (' . e($it['varian']) . ')' : '' ?>
            <?php if ($it['topping']): ?><br><small class="k-kecil"><?= e(implode(', ', $it['topping'])) ?></small><?php endif; ?>
          </span>
          <b><?= rupiah($it['harga'] * $it['qty']) ?></b>
        </div>
      <?php endforeach; ?>
      <div class="r-total"><span>Total</span><b><?= rupiah($total) ?></b></div>
      <button type="submit" class="btn-biru btn-lebar">Buat Pesanan</button>
    </div>
  </form>
</section>

<?php include 'includes/footer.php'; ?>