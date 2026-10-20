<?php
require 'helpers.php'; require 'koneksi.php';
$id = valid_id($_GET['id'] ?? null);
if (!$id) { flash('error', 'ID tidak valid.'); redirect('materi.php'); }
$s = $conn->prepare('SELECT t.id, t.judul, t.isi, t.tautan, m.kode, m.nama
    FROM materi t JOIN mata_kuliah m ON m.id = t.mata_kuliah_id WHERE t.id = ?');
$s->bind_param('i', $id); $s->execute();
$r = $s->get_result()->fetch_assoc();
if (!$r) { flash('error', 'Data materi tidak ditemukan.'); redirect('materi.php'); }
$judul = 'Materi';
include 'header.php';
?>
<h2><?= e($r['judul']) ?></h2>
<dl>
    <dt>Mata kuliah</dt><dd><?= e($r['kode']) ?> - <?= e($r['nama']) ?></dd>
    <dt>Judul materi</dt><dd><?= e($r['judul']) ?></dd>
    <dt>Isi materi</dt><dd><?= nl2br(e($r['isi'])) ?></dd>
    <dt>Tautan referensi</dt><dd><a href="<?= e($r['tautan']) ?>" target="_blank" rel="noopener noreferrer"><?= e($r['tautan']) ?></a></dd>
</dl>
<p><a class="btn btn-primary" href="materi_form.php?id=<?= (int)$r['id'] ?>">Edit data</a>
   <a class="btn btn-outline" href="materi.php">Kembali</a></p>
<?php include 'footer.php'; ?>
