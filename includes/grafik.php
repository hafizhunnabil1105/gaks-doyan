<?php
require_once __DIR__ . '/skema.php';

// Kolom penting tabel pesanan (menyesuaikan nama kolom yang ada).
function kolom_pesanan(mysqli $k): array {
    return [
        'tgl'   => pilih_kolom($k, 'pesanan', ['created_at','tanggal','tgl_pesanan','waktu_pesan','dibuat','tanggal_pesan']),
        'total' => pilih_kolom($k, 'pesanan', ['total','total_harga','grand_total','total_bayar']),
        'user'  => pilih_kolom($k, 'pesanan', ['user_id','pelanggan_id','id_user','customer_id']),
    ];
}

// Omzet per hari (pesanan batal tidak dihitung). Mengembalikan array tanggal => rupiah.
function data_harian(mysqli $k, string $dari, string $sampai): array {
    $c = kolom_pesanan($k);
    $hasil = [];
    for ($t = strtotime($dari); $t <= strtotime($sampai); $t += 86400) $hasil[date('Y-m-d', $t)] = 0;
    if (!$c['tgl'] || !$c['total']) return $hasil;
    $sql = "SELECT DATE(`{$c['tgl']}`) d, SUM(`{$c['total']}`) s FROM pesanan
            WHERE DATE(`{$c['tgl']}`) BETWEEN ? AND ? AND status <> 'Batal' GROUP BY d";
    $st = $k->prepare($sql);
    $st->bind_param('ss', $dari, $sampai);
    $st->execute();
    $r = $st->get_result();
    while ($b = $r->fetch_assoc()) $hasil[$b['d']] = (int)$b['s'];
    return $hasil;
}

function grafik_singkat(int $n): string {
    if ($n >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 1, ',', ''), '0'), ',') . ' jt';
    if ($n >= 1000) return round($n / 1000) . ' rb';
    return (string)$n;
}
function grafik_skala(int $maks): int {
    if ($maks <= 0) return 100000;
    $p = 10 ** (strlen((string)$maks) - 1);
    return (int)(ceil($maks / $p) * $p);
}

// Grafik batang SVG. $data = [['label'=>'Sen','nilai'=>12000,'ini'=>false], ...]
function grafik_batang(array $data): string {
    $W = 640; $H = 250; $kiri = 46; $bawah = 30; $atas = 22; $kanan = 10;
    $maks = grafik_skala(max(array_merge([0], array_column($data, 'nilai'))));
    $tinggi = $H - $atas - $bawah;
    $n = max(1, count($data));
    $lebar = ($W - $kiri - $kanan) / $n;
    $bw = min(56, $lebar * 0.55);
    $o = '<svg class="gr" viewBox="0 0 ' . $W . ' ' . $H . '" role="img" aria-label="Grafik penjualan">';
    for ($i = 0; $i <= 4; $i++) {
        $y = $atas + $tinggi - ($tinggi * $i / 4);
        $o .= '<line class="gr-garis" x1="' . $kiri . '" y1="' . $y . '" x2="' . ($W - $kanan) . '" y2="' . $y . '"/>';
        $o .= '<text class="gr-teks" x="' . ($kiri - 8) . '" y="' . ($y + 4) . '" text-anchor="end">' . grafik_singkat((int)($maks * $i / 4)) . '</text>';
    }
    foreach ($data as $i => $d) {
        $h = $maks > 0 ? $tinggi * $d['nilai'] / $maks : 0;
        $x = $kiri + $lebar * $i + ($lebar - $bw) / 2;
        $y = $atas + $tinggi - $h;
        $kls = 'gr-bar' . (!empty($d['ini']) ? ' hari-ini' : '');
        $o .= '<g class="gr-grup"><rect class="' . $kls . '" x="' . round($x, 1) . '" y="' . round($y, 1) . '" width="' . round($bw, 1) . '" height="' . round(max($h, 0), 1) . '" rx="6"><title>' . htmlspecialchars($d['label']) . ': Rp ' . number_format($d['nilai'], 0, ',', '.') . '</title></rect>';
        if ($d['nilai'] > 0) $o .= '<text class="gr-nilai" x="' . round($x + $bw / 2, 1) . '" y="' . round($y - 6, 1) . '" text-anchor="middle">' . grafik_singkat($d['nilai']) . '</text>';
        $o .= '<text class="gr-teks" x="' . round($x + $bw / 2, 1) . '" y="' . ($H - 10) . '" text-anchor="middle">' . htmlspecialchars($d['label']) . '</text></g>';
    }
    return $o . '</svg>';
}