<?php
// Sekali pakai: pilih foto untuk tiap produk. Buka di browser, lalu HAPUS file ini.
require_once 'config/koneksi.php';
$dir = __DIR__ . '/assets/img/produk';
$files = array_map('basename', glob($dir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: []);
sort($files);
$prod = [];
$r = $koneksi->query("SELECT id, nama FROM produk ORDER BY id");
while ($b = $r->fetch_assoc()) $prod[] = $b;
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['pilih'] ?? [] as $pid => $file) {
        if ($file === '' || !in_array($file, $files, true)) continue;
        $st = $koneksi->prepare("UPDATE produk SET gambar=? WHERE id=?");
        $id = (int)$pid; $st->bind_param('si', $file, $id); $st->execute();
    }
    $pesan = 'Tersimpan! Buka beranda dan tekan Ctrl+F5. Lalu hapus file pulihkan_gambar.php.';
}
$sekarang = [];
$r = $koneksi->query("SELECT id, gambar FROM produk");
while ($b = $r->fetch_assoc()) $sekarang[$b['id']] = $b['gambar'];
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Pasang Foto Produk</title>
<style>body{font-family:Segoe UI,Arial,sans-serif;background:#fff6ea;margin:0;padding:24px;color:#4a2c1d}
h1{margin:0 0 6px}.ok{background:#e0f5ea;padding:12px;border-radius:10px;margin:12px 0}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px;margin-top:16px}
.k{background:#fff;border-radius:14px;padding:12px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
.k img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:10px}.k b{display:block;margin:8px 0 4px}
select{width:100%;padding:8px;border-radius:8px;border:1px solid #ccc;font:inherit}
button{margin-top:18px;background:#d6402b;color:#fff;border:0;border-radius:24px;padding:12px 28px;font:inherit;font-weight:700;cursor:pointer}</style></head><body>
<h1>Pasang Foto Produk</h1>
<p>Lihat tiap foto, lalu pilih produk yang sesuai di bawahnya. Ditemukan <?= count($files) ?> foto.</p>
<?php if ($pesan): ?><div class="ok"><?= htmlspecialchars($pesan) ?></div><?php endif; ?>
<form method="post"><div class="grid">
<?php foreach ($files as $i => $f):
  $pre = 0; foreach ($sekarang as $pid => $g) if ($g === $f) $pre = $pid; ?>
  <div class="k"><img src="assets/img/produk/<?= htmlspecialchars($f) ?>" alt=""><b>Foto <?= $i + 1 ?></b>
  <select name="foto[<?= $i ?>]" data-file="<?= htmlspecialchars($f) ?>">
    <option value="">- pilih produk -</option>
    <?php foreach ($prod as $p): ?><option value="<?= (int)$p['id'] ?>" <?= ($pre ? $pre == $p['id'] : (($prod[$i]['id'] ?? 0) == $p['id'])) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama']) ?></option><?php endforeach; ?>
  </select></div>
<?php endforeach; ?>
</div>
<div id="hid"></div>
<button type="submit" onclick="return kumpul()">Simpan</button></form>
<script>
function kumpul(){
  var h=document.getElementById('hid'); h.innerHTML='';
  document.querySelectorAll('select[data-file]').forEach(function(s){
    if(!s.value) return;
    var i=document.createElement('input'); i.type='hidden'; i.name='pilih['+s.value+']'; i.value=s.dataset.file; h.appendChild(i);
  });
  return true;
}
</script></body></html>