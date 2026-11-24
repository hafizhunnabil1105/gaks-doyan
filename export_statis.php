<?php
// Buka sekali di browser: http://localhost/gaks-doyan/export_statis.php
// Hasil: folder "docs" berisi versi HTML statis untuk GitHub Pages.
set_time_limit(180);
header('Content-Type: text/html; charset=utf-8');

$root  = __DIR__;
$path  = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');   // mis. /gaks-doyan
$skema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base  = $skema . '://' . $_SERVER['HTTP_HOST'] . $path;
$out   = $root . '/docs';

// halaman yang diekspor: alamat asli => nama file hasil
$peta = [
    'index.php'                => 'index.html',
    'menu.php'                 => 'menu.html',
    'promo.php'                => 'promo.html',
    'halaman.php?p=tentang'    => 'tentang.html',
    'halaman.php?p=carapesan'  => 'carapesan.html',
    'halaman.php?p=faq'        => 'faq.html',
    'halaman.php?p=kontak'     => 'kontak.html',
    'halaman.php?p=testimoni'  => 'testimoni.html',
    'halaman.php?p=syarat'     => 'syarat.html',
    'halaman.php?p=privasi'    => 'privasi.html',
];

function salin_folder($dari, $ke) {
    if (!is_dir($dari)) return 0;
    @mkdir($ke, 0777, true); $n = 0;
    foreach (scandir($dari) as $f) {
        if ($f === '.' || $f === '..') continue;
        $a = $dari . '/' . $f; $b = $ke . '/' . $f;
        if (is_dir($a)) $n += salin_folder($a, $b); else { copy($a, $b); $n++; }
    }
    return $n;
}

function ubah_link($html, $peta, $path) {
    $pola = '#\b(href|action)=(["\'])([^"\']*)\2#i';
    return preg_replace_callback($pola, function ($m) use ($peta, $path) {
        [$utuh, $attr, $q, $nilai] = $m;
        $v = html_entity_decode($nilai);
        if ($v === '' || $v[0] === '#' || preg_match('#^(https?:|mailto:|tel:|javascript:|//)#i', $v)) return $utuh;
        if ($path !== '' && strpos($v, $path . '/') === 0) $v = substr($v, strlen($path) + 1);
        $v = ltrim($v, '/');
        if (strpos($v, 'assets/') === 0) return $attr . '=' . $q . $path . '/' . $v . $q;
        if (isset($peta[$v])) return $attr . '=' . $q . $peta[$v] . $q;
        if (preg_match('#^halaman\.php\?p=([a-z]+)#', $v, $x)) return $attr . '=' . $q . $x[1] . '.html' . $q;
        if ($v === '' || $v === './') return $attr . '=' . $q . 'index.html' . $q;
        return $attr . '=' . $q . 'demo.html' . $q;
    }, $html);
}

@mkdir($out, 0777, true);
file_put_contents($out . '/.nojekyll', '');
$log = [];
$log[] = 'Menyalin folder assets: ' . salin_folder($root . '/assets', $out . '/assets') . ' file';

$ctx = stream_context_create(['http' => ['timeout' => 25, 'ignore_errors' => true]]);
foreach ($peta as $asli => $hasil) {
    $isi = @file_get_contents($base . '/' . $asli, false, $ctx);
    if ($isi === false || $isi === '') { $log[] = "GAGAL  $asli"; continue; }
    $isi = ubah_link($isi, $peta, $path);
    file_put_contents($out . '/' . $hasil, $isi);
    $log[] = "OK     $asli  ->  docs/$hasil";
}

// halaman penjelas untuk fitur yang butuh server
$demo = '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
      . '<title>Fitur Server - Ga\'ks Doyan</title><link rel="stylesheet" href="' . $path . '/assets/css/final.css"></head>'
      . '<body style="font-family:Arial,sans-serif;background:#fff8f0;color:#4a2c17;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0">'
      . '<div style="max-width:520px;text-align:center;padding:30px">'
      . '<h1 style="margin:0 0 10px">Fitur ini butuh server</h1>'
      . '<p style="line-height:1.6">Login, keranjang, checkout, lacak pesanan, dan halaman admin memakai PHP dan MySQL, '
      . 'jadi tidak bisa berjalan di GitHub Pages. Halaman ini adalah tampilan statis dari website Ga\'ks Doyan.</p>'
      . '<p><a href="index.html" style="display:inline-block;background:#c8321e;color:#fff;padding:10px 22px;border-radius:30px;text-decoration:none;font-weight:bold">Kembali ke Beranda</a></p>'
      . '<p style="font-size:14px">Source code lengkap: <a href="https://github.com/hafizhunnabil1105/gaks-doyan">github.com/hafizhunnabil1105/gaks-doyan</a></p>'
      . '</div></body></html>';
file_put_contents($out . '/demo.html', $demo);
$log[] = 'OK     docs/demo.html';

echo '<pre style="font:15px/1.7 monospace">' . htmlspecialchars(implode("\n", $log)) . "\n\nSelesai. Folder 'docs' sudah dibuat di dalam folder project.</pre>";