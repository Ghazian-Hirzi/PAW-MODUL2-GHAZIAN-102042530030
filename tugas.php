<?php
require 'helpers.php'; require 'koneksi.php';
$q  = get_str('q');
$mk = valid_id($_GET['mk'] ?? null);
$sql = 'SELECT t.id, t.judul, t.tenggat, t.status, m.kode, m.nama FROM tugas t JOIN mata_kuliah m ON m.id = t.mata_kuliah_id WHERE 1=1';
$tipe = ''; $par = [];
if ($q !== '') { $sql .= ' AND t.judul LIKE ?'; $tipe .= 's'; $par[] = '%' . $q . '%'; }
if ($mk)       { $sql .= ' AND t.mata_kuliah_id = ?'; $tipe .= 'i'; $par[] = $mk; }
$sql .= ' ORDER BY t.tenggat';
$s = $conn->prepare($sql);
if ($par) $s->bind_param($tipe, ...$par);
$s->execute();
$hasil = $s->get_result();
$opsi = daftar_mk($conn);
$judul = 'Tugas';
include 'header.php';
?>
<div class="top">
    <span>Menampilkan <?= (int)$hasil->num_rows ?> data.</span>
    <a class="btn btn-primary" href="tugas_form.php">Tambah tugas</a>
</div>
<form method="get" class="row">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari judul tugas">
    <select name="mk">
        <option value="">Semua mata kuliah</option>
        <?php foreach ($opsi as $o): ?>
            <option value="<?= (int)$o['id'] ?>" <?= $mk === (int)$o['id'] ? 'selected' : '' ?>><?= e($o['kode']) ?> - <?= e($o['nama']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-primary" type="submit">Cari</button>
    <a class="btn btn-outline" href="tugas.php">Reset</a>
</form>
<?php if ($hasil->num_rows === 0): ?>
    <div class="empty">Data tidak ditemukan.</div>
<?php else: ?>
<table>
    <tr><th>Judul</th><th>Mata kuliah</th><th>Tenggat</th><th>Status</th><th>Aksi</th></tr>
    <?php while ($r = $hasil->fetch_assoc()): ?>
    <tr>
        <td><?= e($r['judul']) ?></td>
        <td><?= e($r['kode']) ?> - <?= e($r['nama']) ?></td>
        <td><?= e($r['tenggat']) ?></td>
        <td><?= e($r['status']) ?></td>
        <td>
            <a class="btn btn-sm" href="tugas_detail.php?id=<?= (int)$r['id'] ?>">Detail</a>
            <a class="btn btn-sm" href="tugas_form.php?id=<?= (int)$r['id'] ?>">Edit</a>
            <a class="btn btn-sm btn-danger" href="tugas_hapus.php?id=<?= (int)$r['id'] ?>">Hapus</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php endif; ?>
<?php include 'footer.php'; ?>
