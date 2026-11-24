<?php
include 'config/koneksi.php';
include 'includes/auth.php';
wajib_login();

$uid = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'batal') {
    $oid = (int)($_POST['id'] ?? 0);
    mysqli_begin_transaction($koneksi);
    try {
        $s = mysqli_prepare($koneksi, "SELECT id, status FROM pesanan WHERE id = ? AND user_id = ? FOR UPDATE");
        mysqli_stmt_bind_param($s, "ii", $oid, $uid);
        mysqli_stmt_execute($s);
        $o = mysqli_fetch_assoc(mysqli_stmt_get_result($s));

        if ($o && $o['status'] === 'Baru') {
            $u = mysqli_prepare($koneksi, "UPDATE pesanan SET status = 'Dibatalkan' WHERE id = ?");
            mysqli_stmt_bind_param($u, "i", $oid);
            mysqli_stmt_execute($u);

            $s = mysqli_prepare($koneksi, "SELECT produk_id, qty FROM pesanan_detail WHERE pesanan_id = ?");
            mysqli_stmt_bind_param($s, "i", $oid);
            mysqli_stmt_execute($s);
            $rows = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
            foreach ($rows as $r) {
                if ($r['produk_id']) {
                    $k = mysqli_prepare($koneksi, "UPDATE produk SET stok = stok + ? WHERE id = ?");
                    mysqli_stmt_bind_param($k, "ii", $r['qty'], $r['produk_id']);
                    mysqli_stmt_execute($k);
                }
            }
            $_SESSION['flash'] = ['tipe' => 'ok', 'pesan' => 'Pesanan berhasil dibatalkan.'];
        } else {
            $_SESSION['flash'] = ['tipe' => 'err', 'pesan' => 'Pesanan ini sudah diproses dan tidak bisa dibatalkan.'];
        }
        mysqli_commit($koneksi);
    } catch (Throwable $ex) {
        mysqli_rollback($koneksi);
        $_SESSION['flash'] = ['tipe' => 'err', 'pesan' => 'Gagal membatalkan pesanan. Coba lagi.'];
    }
    header("Location: " . BASE_URL . "pesanan_saya.php");
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$s = mysqli_prepare($koneksi, "SELECT * FROM pesanan WHERE user_id = ? ORDER BY id DESC");
mysqli_stmt_bind_param($s, "i", $uid);
mysqli_stmt_execute($s);
$daftar = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);

include 'includes/header.php';
?>
<section class="wadah halaman">
  <h2 class="judul">Pesanan Saya</h2>

  <?php if ($flash): ?><div class="info <?= $flash['tipe'] ?>"><?= e($flash['pesan']) ?></div><?php endif; ?>

  <?php if (!$daftar): ?>
    <div class="kartu-web kosong-web">
      <p>Kamu belum punya pesanan.</p>
      <a class="btn-biru" href="index.php#katalog">Lihat Menu</a>
    </div>
  <?php endif; ?>

  <?php foreach ($daftar as $o): ?>
    <?php
      $s = mysqli_prepare($koneksi, "SELECT * FROM pesanan_detail WHERE pesanan_id = ?");
      mysqli_stmt_bind_param($s, "i", $o['id']);
      mysqli_stmt_execute($s);
      $items = mysqli_fetch_all(mysqli_stmt_get_result($s), MYSQLI_ASSOC);
    ?>
    <div class="kartu-web pesanan-kartu">
      <div class="p-atas">
        <div>
          <div class="p-kode"><?= e($o['kode']) ?></div>
          <div class="p-tgl"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?> &middot; <?= e($o['metode_bayar']) ?></div>
        </div>
        <span class="badge st-<?= e($o['status']) ?>"><?= e($o['status']) ?></span>
      </div>

      <?php foreach ($items as $it): ?>
        <div class="r-baris">
          <span><?= (int)$it['qty'] ?>x <?= e($it['nama_produk']) ?>
            <?php if ($it['keterangan']): ?><br><small class="k-kecil"><?= e($it['keterangan']) ?></small><?php endif; ?>
          </span>
          <b><?= rupiah($it['subtotal']) ?></b>
        </div>
      <?php endforeach; ?>

      <div class="p-bawah">
        <div>Total: <b style="color:var(--biru)"><?= rupiah($o['total']) ?></b></div>
        <?php if ($o['status'] === 'Baru'): ?>
          <form method="POST" onsubmit="return confirm('Batalkan pesanan ini?')">
            <input type="hidden" name="aksi" value="batal">
            <input type="hidden" name="id" value="<?= $o['id'] ?>">
            <button class="btn-batal" type="submit">Batalkan pesanan</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</section>

<?php include 'includes/footer.php'; ?>