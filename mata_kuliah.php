<?php
require 'helpers.php'; require 'koneksi.php';
$q = get_str('q');
if ($q !== '') {
    $like = '%' . $q . '%';
    $s = $conn->prepare('SELECT id, kode, nama, dosen, semester FROM mata_kuliah WHERE kode LIKE ? OR nama LIKE ? ORDER BY nama');
    $s->bind_param('ss', $like, $like);
} else {
    $s = $conn->prepare('SELECT id, kode, nama, dosen, semester FROM mata_kuliah ORDER BY nama');
}
$s->execute();
$hasil = $s->get_result();
$judul = 'Mata kuliah';
include 'header.php';
?>
<div class="top">
    <span>Menampilkan <?= (int)$hasil->num_rows ?> data.</span>
    <a class="btn btn-primary" href="mata_kuliah_form.php">Tambah mata kuliah</a>
</div>
<form method="get" class="row">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari kode atau nama">
    <button class="btn btn-primary" type="submit">Cari</button>
    <a class="btn btn-outline" href="mata_kuliah.php">Reset</a>
</form>
<?php if ($hasil->num_rows === 0): ?>
    <div class="empty">Data tidak ditemukan.</div>
<?php else: ?>
<table>
    <tr><th>Mata kuliah</th><th>Dosen / semester</th><th>Aksi</th></tr>
    <?php while ($r = $hasil->fetch_assoc()): ?>
    <tr>
        <td><?= e($r['kode']) ?> - <?= e($r['nama']) ?></td>
        <td><?= e($r['dosen']) ?> / Semester <?= (int)$r['semester'] ?></td>
        <td>
            <a class="btn btn-sm" href="mata_kuliah_detail.php?id=<?= (int)$r['id'] ?>">Detail</a>
            <a class="btn btn-sm" href="mata_kuliah_form.php?id=<?= (int)$r['id'] ?>">Edit</a>
            <a class="btn btn-sm btn-danger" href="mata_kuliah_hapus.php?id=<?= (int)$r['id'] ?>">Hapus</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php endif; ?>
<?php include 'footer.php'; ?>
