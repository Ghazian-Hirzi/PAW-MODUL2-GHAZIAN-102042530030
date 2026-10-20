<?php
require 'helpers.php'; require 'koneksi.php';
$id = valid_id($_GET['id'] ?? null);
if (!$id) { flash('error', 'ID tidak valid.'); redirect('mata_kuliah.php'); }
$s = $conn->prepare('SELECT m.id, m.kode, m.nama, m.dosen, m.semester,
    (SELECT COUNT(*) FROM materi WHERE mata_kuliah_id = m.id) AS jml_materi,
    (SELECT COUNT(*) FROM tugas WHERE mata_kuliah_id = m.id) AS jml_tugas
    FROM mata_kuliah m WHERE m.id = ?');
$s->bind_param('i', $id); $s->execute();
$r = $s->get_result()->fetch_assoc();
if (!$r) { flash('error', 'Data mata kuliah tidak ditemukan.'); redirect('mata_kuliah.php'); }
$judul = 'Mata kuliah';
include 'header.php';
?>
<h2><?= e($r['nama']) ?></h2>
<dl>
    <dt>Kode</dt><dd><?= e($r['kode']) ?></dd>
    <dt>Nama</dt><dd><?= e($r['nama']) ?></dd>
    <dt>Dosen pengampu</dt><dd><?= e($r['dosen']) ?></dd>
    <dt>Semester</dt><dd><?= (int)$r['semester'] ?></dd>
    <dt>Jumlah materi</dt><dd><?= (int)$r['jml_materi'] ?></dd>
    <dt>Jumlah tugas</dt><dd><?= (int)$r['jml_tugas'] ?></dd>
</dl>
<p><a class="btn btn-primary" href="mata_kuliah_form.php?id=<?= (int)$r['id'] ?>">Edit data</a>
   <a class="btn btn-outline" href="mata_kuliah.php">Kembali</a></p>
<?php include 'footer.php'; ?>
