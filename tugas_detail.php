<?php
require 'helpers.php'; require 'koneksi.php';
$id = valid_id($_GET['id'] ?? null);
if (!$id) { flash('error', 'ID tidak valid.'); redirect('tugas.php'); }
$s = $conn->prepare('SELECT t.id, t.judul, t.instruksi, t.tenggat, t.status, m.kode, m.nama
    FROM tugas t JOIN mata_kuliah m ON m.id = t.mata_kuliah_id WHERE t.id = ?');
$s->bind_param('i', $id); $s->execute();
$r = $s->get_result()->fetch_assoc();
if (!$r) { flash('error', 'Data tugas tidak ditemukan.'); redirect('tugas.php'); }
$judul = 'Tugas';
include 'header.php';
?>
<h2><?= e($r['judul']) ?></h2>
<dl>
    <dt>Mata kuliah</dt><dd><?= e($r['kode']) ?> - <?= e($r['nama']) ?></dd>
    <dt>Judul tugas</dt><dd><?= e($r['judul']) ?></dd>
    <dt>Instruksi</dt><dd><?= nl2br(e($r['instruksi'])) ?></dd>
    <dt>Tenggat</dt><dd><?= e($r['tenggat']) ?></dd>
    <dt>Status</dt><dd><?= e($r['status']) ?></dd>
</dl>
<p><a class="btn btn-primary" href="tugas_form.php?id=<?= (int)$r['id'] ?>">Edit data</a>
   <a class="btn btn-outline" href="tugas.php">Kembali</a></p>
<?php include 'footer.php'; ?>
