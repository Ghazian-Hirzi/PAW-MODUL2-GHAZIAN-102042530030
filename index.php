<?php
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
