<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
wajib_admin();

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$aksi = strtolower(trim($_POST['aksi'] ?? $_GET['aksi'] ?? ($_POST['status'] ?? $_GET['status'] ?? '')));
$alasan = trim($_POST['alasan'] ?? '');
$kembali = 'pesanan_detail.php?id=' . $id;
if (!$id) { header('Location: pesanan.php'); exit; }

$peta = ['verifikasi' => 'verifikasi', 'lunas' => 'verifikasi', 'proses' => 'proses', 'diproses' => 'proses',
         'antar' => 'antar', 'diantar' => 'antar', 'selesai' => 'selesai', 'batal' => 'batal'];
$aksi = $peta[$aksi] ?? '';

$koneksi->begin_transaction();
try {
    $s = $koneksi->prepare("SELECT * FROM pesanan WHERE id=? FOR UPDATE");
    $s->bind_param('i', $id); $s->execute();
    $p = $s->get_result()->fetch_assoc();
    if (!$p) throw new Exception('Pesanan tidak ditemukan.');
    $pesan = '';

    if ($aksi === 'verifikasi') {
        $s = $koneksi->prepare("UPDATE pesanan SET status_bayar='Lunas' WHERE id=?");
        $s->bind_param('i', $id); $s->execute(); $pesan = 'Pembayaran diverifikasi (Lunas).';
    } elseif (in_array($aksi, ['proses', 'antar', 'selesai'], true)) {
        $baru = ['proses' => 'Diproses', 'antar' => 'Diantar', 'selesai' => 'Selesai'][$aksi];
        if ($p['status'] === 'Batal') throw new Exception('Pesanan sudah dibatalkan.');
        $s = $koneksi->prepare("UPDATE pesanan SET status=? WHERE id=?");
        $s->bind_param('si', $baru, $id); $s->execute();
        if ($baru === 'Selesai') $koneksi->query("UPDATE pesanan SET status_bayar='Lunas' WHERE id=$id");
        $pesan = "Status diubah menjadi $baru.";
    } elseif ($aksi === 'batal') {
        if ($p['status'] === 'Batal') throw new Exception('Pesanan sudah dibatalkan.');
        if ($alasan === '') throw new Exception('Isi alasan pembatalan.');
        $s = $koneksi->prepare("UPDATE pesanan SET status='Batal', alasan_batal=? WHERE id=?");
        $s->bind_param('si', $alasan, $id); $s->execute();
        // kembalikan stok
        $r = $koneksi->query("SHOW COLUMNS FROM pesanan_detail"); $kol = [];
        while ($b = $r->fetch_assoc()) $kol[] = $b['Field'];
        $fk = in_array('pesanan_id', $kol) ? 'pesanan_id' : (in_array('id_pesanan', $kol) ? 'id_pesanan' : null);
        $kq = in_array('jumlah', $kol) ? 'jumlah' : (in_array('qty', $kol) ? 'qty' : null);
        $kp = in_array('produk_id', $kol) ? 'produk_id' : (in_array('id_produk', $kol) ? 'id_produk' : null);
        if ($fk && $kq && $kp) {
            $s = $koneksi->prepare("SELECT `$kp` pid, `$kq` q FROM pesanan_detail WHERE `$fk`=?");
            $s->bind_param('i', $id); $s->execute(); $rs = $s->get_result();
            $u = $koneksi->prepare("UPDATE produk SET stok = stok + ? WHERE id=?");
            while ($d = $rs->fetch_assoc()) { $q = (int)$d['q']; $pid = (int)$d['pid']; $u->bind_param('ii', $q, $pid); $u->execute(); }
        }
        $pesan = 'Pesanan dibatalkan dan stok dikembalikan.';
    } else {
        throw new Exception('Aksi tidak dikenal.');
    }
    $koneksi->commit();
    $_SESSION['flash'] = $pesan;
} catch (Throwable $e) {
    $koneksi->rollback();
    $_SESSION['flash'] = $e->getMessage();
}
header('Location: ' . $kembali); exit;