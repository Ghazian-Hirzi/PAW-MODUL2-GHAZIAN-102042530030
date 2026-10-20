<?php
<<<<<<< HEAD
require 'helpers.php'; require 'koneksi.php';
$jml = [];
foreach (['mata_kuliah' => 'SELECT COUNT(*) AS n FROM mata_kuliah',
          'materi'      => 'SELECT COUNT(*) AS n FROM materi',
          'tugas'       => 'SELECT COUNT(*) AS n FROM tugas'] as $k => $sql) {
    $s = $conn->prepare($sql); $s->execute();
    $jml[$k] = (int)$s->get_result()->fetch_assoc()['n'];
}
$mk = daftar_mk($conn);
$judul = 'Learning Management System';
include 'header.php';
?>
<p>Pengelolaan mata kuliah, materi pembelajaran, dan tugas.</p>
<div class="stats">
    <div>Mata kuliah<strong><?= $jml['mata_kuliah'] ?></strong></div>
    <div>Materi<strong><?= $jml['materi'] ?></strong></div>
    <div>Tugas<strong><?= $jml['tugas'] ?></strong></div>
</div>
<h2>Mata kuliah tersedia</h2>
<?php if (!$mk): ?>
    <div class="empty">Belum ada mata kuliah.</div>
<?php else: ?>
<ul>
    <?php foreach ($mk as $r): ?>
        <li><a href="mata_kuliah_detail.php?id=<?= (int)$r['id'] ?>"><?= e($r['kode']) ?> - <?= e($r['nama']) ?></a></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
<?php include 'footer.php'; ?>
=======
require 'koneksi.php';
$cari = '';
if (isset($_GET['cari'])) {
$cari = mysqli_real_escape_string($conn, $_GET['cari']);
}
$hasil = mysqli_query($conn,
"SELECT * FROM showroom_mobil
WHERE nama_mobil LIKE '%$cari%' OR brand_mobil LIKE '%$cari%'
ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>ShowRoom EAD</title>
<link rel="stylesheet" href="style.css">
</head>
<body><main>
<p class="label">PRAKTIKUM PAW / MODUL 2</p>
<h1>ShowRoom EAD</h1>
<p>Kelola data mobil yang tersedia di showroom.</p>
<?php if (isset($_GET['pesan'])): ?>
<p class="pesan">Perubahan data berhasil disimpan.</p>
<?php endif; ?>
<p><a class="tombol" href="tambah.php">Tambah mobil</a></p>
<form method="get" class="pencarian">
<label for="cari">Cari nama atau brand</label>
<input id="cari" name="cari" value="<?= $_GET['cari'] ?? '' ?>">
<button>Cari</button> <a href="index.php">Reset</a>
</form>
<p>Jumlah hasil: <?= mysqli_num_rows($hasil) ?></p>
<?php if (mysqli_num_rows($hasil) == 0): ?>
<p>Data tidak ditemukan. Coba kata kunci lain.</p>
<?php endif; ?>
<div class="daftar">
<?php while ($mobil = mysqli_fetch_assoc($hasil)): ?>
<article>
<p class="label"><?= $mobil['kode_mobil'] ?></p>
<h2><?= $mobil['nama_mobil'] ?></h2>
<p><?= $mobil['brand_mobil'] ?> &bull;
<?= $mobil['tipe_mobil'] ?></p>
<p>Warna: <?= $mobil['warna_mobil'] ?></p>
<p>Rp <?= number_format($mobil['harga_mobil'], 0, ',', '.') ?></p>
<a href="detail.php?id=<?= $mobil['id'] ?>">Detail</a>
<a href="edit.php?id=<?= $mobil['id'] ?>">Edit</a>
<a href="hapus.php?id=<?= $mobil['id'] ?>">Hapus</a>
</article>
<?php endwhile; ?>
</div>
</main></body>
</html>
>>>>>>> 42c382d198a108e2ce15b50004b39e88b7bca2b3
