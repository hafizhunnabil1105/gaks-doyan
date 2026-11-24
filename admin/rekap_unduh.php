<?php
require_once '../config/koneksi.php';
require_once '../includes/auth.php';
require_once '../includes/grafik.php';
wajib_admin();

$tgl = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tgl'] ?? '') ? $_GET['tgl'] : date('Y-m-d');
$c = kolom_pesanan($koneksi);
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="rekap-' . $tgl . '.csv"');
echo "\xEF\xBB\xBF";
$o = fopen('php://output', 'w');
fputcsv($o, ['Kode', 'Waktu', 'Nama', 'Telepon', 'Pengambilan', 'Metode Bayar', 'Status', 'Status Bayar', 'Total'], ';');
if ($c['tgl']) {
    $s = $koneksi->prepare("SELECT * FROM pesanan WHERE DATE(`{$c['tgl']}`)=? ORDER BY id");
    $s->bind_param('s', $tgl); $s->execute(); $r = $s->get_result();
    while ($p = $r->fetch_assoc()) fputcsv($o, [
        f($p, ['kode','kode_pesanan'], $p['id']), $p[$c['tgl']], f($p, ['nama_penerima','nama_pelanggan','nama']), f($p, ['telepon','no_hp','hp','no_telp','telp']),
        f($p, ['metode_ambil']), f($p, ['metode_bayar','metode']), $p['status'] ?? '', $p['status_bayar'] ?? '', f($p, ['total','total_harga','grand_total','total_bayar'], 0)
    ], ';');
}
fclose($o);