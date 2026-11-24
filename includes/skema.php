<?php
// ===== Cadangan: dipakai hanya jika config/koneksi.php belum punya fungsi/konstanta ini =====
foreach ([
    'BASE_URL' => '/gaks-doyan', 'NAMA_BRAND' => "Ga'ks Doyan",
    'TOKO_WA' => '6281234567890', 'TOKO_TELP' => '0812-3456-7890', 'TOKO_ALAMAT' => 'Jl. Sukatani No. 80, Kota Depok',
    'TOKO_IG' => '@gaksdoyan.id', 'JAM_BUKA' => '09:00', 'JAM_TUTUP' => '21:00',
    'ONGKIR_ANTAR' => 1000, 'ESTIMASI_MENIT' => 18, 'BANK_INFO' => 'BCA 1234567890 a.n. Ga\'ks Doyan',
] as $_k => $_v) if (!defined($_k)) define($_k, $_v);

if (!function_exists('e')) { function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('rupiah')) { function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); } }
if (!function_exists('aset')) {
    function aset($path) {
        $f = dirname(__DIR__) . '/' . $path;
        return rtrim(BASE_URL, '/') . '/' . $path . (is_file($f) ? '?v=' . filemtime($f) : '');
    }
}
if (!function_exists('hp_wa')) {
    function hp_wa($hp) {
        $d = preg_replace('/\D/', '', (string)$hp);
        if ($d !== '' && $d[0] === '0') $d = '62' . substr($d, 1);
        return $d;
    }
}
if (!function_exists('toko_buka')) {
    function toko_buka() {
        $b = str_replace('.', ':', JAM_BUKA); $t = str_replace('.', ':', JAM_TUTUP);
        $now = date('H:i');
        return $b <= $t ? ($now >= $b && $now < $t) : ($now >= $b || $now < $t);
    }
}
// Pembantu: menyesuaikan kode dengan nama tabel/kolom yang ada di database.
function daftar_kolom(mysqli $k, string $tabel): array {
    static $cache = [];
    if (isset($cache[$tabel])) return $cache[$tabel];
    $cache[$tabel] = [];
    if (!preg_match('/^[a-z0-9_]+$/i', $tabel)) return [];
    $r = @$k->query("SHOW COLUMNS FROM `$tabel`");
    if ($r) while ($b = $r->fetch_assoc()) $cache[$tabel][] = $b['Field'];
    return $cache[$tabel];
}
function pilih_tabel(mysqli $k, array $kandidat): ?string {
    foreach ($kandidat as $t) if (daftar_kolom($k, $t)) return $t;
    return null;
}
function pilih_kolom(mysqli $k, string $tabel, array $kandidat): ?string {
    $ada = daftar_kolom($k, $tabel);
    foreach ($kandidat as $c) if (in_array($c, $ada, true)) return $c;
    return null;
}
// Ambil nilai pertama yang ada dari sebuah baris hasil query.
function f(array $baris, array $kandidat, $bawaan = '') {
    foreach ($kandidat as $c) if (array_key_exists($c, $baris) && $baris[$c] !== null && $baris[$c] !== '') return $baris[$c];
    return $bawaan;
}
// Cari URL gambar: mencoba beberapa folder umum.
function url_gambar(?string $nama): string {
    if (!$nama) return '';
    if (preg_match('#^https?://#', $nama)) return $nama;
    $root = dirname(__DIR__);
    foreach (['', 'assets/img/', 'assets/img/produk/', 'assets/uploads/', 'uploads/', 'assets/images/', 'assets/img/promo/'] as $p) {
        if (is_file($root . '/' . $p . $nama)) return rtrim(BASE_URL, '/') . '/' . $p . $nama;
    }
    return '';
}
// Cari file logo / banner di assets/img.
function cari_aset(array $pola): string {
    $root = dirname(__DIR__);
    foreach ($pola as $p) {
        $g = glob($root . '/assets/img/' . $p);
        if ($g) return 'assets/img/' . basename($g[0]);
    }
    return '';
}
// Pilih foto makanan untuk sebuah promo: gambar promo sendiri, kalau tidak ada cocokkan dengan nama produk.
function foto_promo(array $promo, array $produk): string {
    $g = url_gambar((string)f($promo, ['gambar','foto','banner']));
    if ($g) return $g;
    $teks = mb_strtolower(f($promo, ['judul','nama','nama_promo']) . ' ' . f($promo, ['deskripsi','keterangan','isi']));
    $umum = ['rice','bowl','tea','jumbo','reguler','gratis','paket','hemat'];
    $cadangan = ''; $terbaik = ''; $skor = 0;
    foreach ($produk as $p) {
        $u = url_gambar((string)f($p, ['gambar','foto']));
        if (!$u) continue;
        if ($cadangan === '') $cadangan = $u;
        $n = mb_strtolower((string)f($p, ['nama','nama_produk']));
        if ($n !== '' && mb_strpos($teks, $n) !== false) return $u;
        $s = 0;
        foreach (preg_split('/\s+/', $n) as $w) if (mb_strlen($w) > 3 && !in_array($w, $umum, true) && mb_strpos($teks, $w) !== false) $s++;
        if ($s > $skor) { $skor = $s; $terbaik = $u; }
    }
    if ($terbaik) return $terbaik;
    if (mb_strpos($teks, 'rice bowl') !== false) {
        foreach ($produk as $p) if (stripos((string)f($p, ['nama','nama_produk']), 'rice bowl') === 0) {
            $u = url_gambar((string)f($p, ['gambar','foto'])); if ($u) return $u;
        }
    }
    return $cadangan;
}

// ===== Buat tabel/kolom tambahan otomatis jika belum ada =====
if (!function_exists('pastikan_tabel')) {
    function pastikan_tabel(mysqli $k): void {
        try {
            $k->query("CREATE TABLE IF NOT EXISTS testimoni (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(80) NOT NULL, isi VARCHAR(300) NOT NULL, bintang TINYINT NOT NULL DEFAULT 5, tampil TINYINT(1) NOT NULL DEFAULT 1, dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
            $k->query("CREATE TABLE IF NOT EXISTS pesan_kontak (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(80) NOT NULL, email VARCHAR(120) NOT NULL, pesan TEXT NOT NULL, dibaca TINYINT(1) NOT NULL DEFAULT 0, dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
            $k->query("CREATE TABLE IF NOT EXISTS pengeluaran (id INT AUTO_INCREMENT PRIMARY KEY, tanggal DATE NOT NULL, keterangan VARCHAR(150) NOT NULL, kategori VARCHAR(50) NOT NULL DEFAULT 'Bahan baku', jumlah INT NOT NULL DEFAULT 0, dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
            if ($k->query("SHOW TABLES LIKE 'pesanan'")->num_rows && !$k->query("SHOW COLUMNS FROM pesanan LIKE 'status_bayar'")->num_rows) {
                $k->query("ALTER TABLE pesanan ADD COLUMN status_bayar VARCHAR(30) NOT NULL DEFAULT 'Menunggu verifikasi'");
                $k->query("UPDATE pesanan SET status_bayar='Lunas' WHERE status='Selesai'");
            }
            $n = $k->query("SELECT COUNT(*) n FROM testimoni")->fetch_assoc()['n'];
            if ((int)$n === 0) $k->query("INSERT INTO testimoni (nama,isi,bintang) VALUES ('Rina','Ayam teriyakinya juicy banget, porsinya pas buat makan siang. Pesan lewat web gampang!',5),('Dimas','Saus mentainya juara. Antarnya cepat, masih hangat pas sampai.',5),('Salsa','Harga ramah kantong mahasiswa dan rasanya konsisten. Pasti repeat order.',4)");
        } catch (Throwable $x) { /* diabaikan */ }
    }
}
if (isset($koneksi) && $koneksi instanceof mysqli) pastikan_tabel($koneksi);