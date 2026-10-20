<?php
require 'helpers.php'; require 'koneksi.php';
$STATUS = ['Draf', 'Aktif', 'Selesai'];

$edit = isset($_GET['id']);
$id = 0; $lama = null;
if ($edit) {
    $id = valid_id($_GET['id']);
    if (!$id) { flash('error', 'ID tidak valid.'); redirect('tugas.php'); }
    $s = $conn->prepare('SELECT id, mata_kuliah_id, judul, instruksi, tenggat, status FROM tugas WHERE id = ?');
    $s->bind_param('i', $id); $s->execute();
    $lama = $s->get_result()->fetch_assoc();
    if (!$lama) { flash('error', 'Data tugas tidak ditemukan.'); redirect('tugas.php'); }
}
$data = $lama ?: ['mata_kuliah_id' => '', 'judul' => '', 'instruksi' => '', 'tenggat' => '', 'status' => 'Draf'];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { flash('error', 'Token tidak valid. Permintaan ditolak.'); redirect('tugas.php'); }
    $data = ['mata_kuliah_id' => post('mata_kuliah_id'), 'judul' => post('judul'), 'instruksi' => post('instruksi'),
             'tenggat' => post('tenggat'), 'status' => post('status')];

    $mkId = valid_id($data['mata_kuliah_id']);
    if (!$mkId || !mk_ada($conn, $mkId)) $err['mata_kuliah_id'] = 'Pilih mata kuliah yang tersedia.';
    if (panjang($data['judul']) < 3 || panjang($data['judul']) > 150) $err['judul'] = 'Judul harus 3-150 karakter.';
    if (panjang($data['instruksi']) < 3 || panjang($data['instruksi']) > 2000) $err['instruksi'] = 'Instruksi harus 3-2000 karakter.';
    if (!tanggal_valid($data['tenggat'])) $err['tenggat'] = 'Tenggat harus tanggal yang valid.';
    if (!in_array($data['status'], $STATUS, true)) $err['status'] = 'Status hanya boleh Draf, Aktif, atau Selesai.';

    if (!$err) {
        if ($edit && (int)$lama['mata_kuliah_id'] === $mkId && $lama['judul'] === $data['judul']
            && $lama['instruksi'] === $data['instruksi'] && $lama['tenggat'] === $data['tenggat']
            && $lama['status'] === $data['status']) {
            flash('info', 'Tidak ada perubahan data.');
            redirect('tugas.php');
        }
        try {
            if ($edit) {
                $q = $conn->prepare('UPDATE tugas SET mata_kuliah_id = ?, judul = ?, instruksi = ?, tenggat = ?, status = ? WHERE id = ?');
                $q->bind_param('issssi', $mkId, $data['judul'], $data['instruksi'], $data['tenggat'], $data['status'], $id);
            } else {
                $q = $conn->prepare('INSERT INTO tugas (mata_kuliah_id, judul, instruksi, tenggat, status) VALUES (?, ?, ?, ?, ?)');
                $q->bind_param('issss', $mkId, $data['judul'], $data['instruksi'], $data['tenggat'], $data['status']);
            }
            $q->execute();
            flash('success', $edit ? 'Tugas berhasil diubah.' : 'Tugas berhasil ditambahkan.');
            redirect('tugas.php');
        } catch (mysqli_sql_exception $ex) {
            $err['umum'] = 'Data gagal disimpan karena kesalahan database.';
        }
    }
}

$opsi = daftar_mk($conn);
$judul = $edit ? 'Edit tugas' : 'Tambah tugas';
include 'header.php';
if (isset($err['umum'])) echo '<div class="alert error">' . e($err['umum']) . '</div>';
$aksi = 'tugas_form.php' . ($edit ? '?id=' . $id : '');
?>
<form method="post" action="<?= e($aksi) ?>" novalidate>
    <?= csrf_field() ?>
    <label for="mk">Mata kuliah</label>
    <select id="mk" name="mata_kuliah_id">
        <option value="">Pilih Mata kuliah</option>
        <?php foreach ($opsi as $o): ?>
            <option value="<?= (int)$o['id'] ?>" <?= (string)$data['mata_kuliah_id'] === (string)$o['id'] ? 'selected' : '' ?>><?= e($o['kode']) ?> - <?= e($o['nama']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($err['mata_kuliah_id'])): ?><div class="field-error"><?= e($err['mata_kuliah_id']) ?></div><?php endif; ?>

    <label for="judul">Judul tugas</label>
    <input type="text" id="judul" name="judul" value="<?= e($data['judul']) ?>">
    <?php if (isset($err['judul'])): ?><div class="field-error"><?= e($err['judul']) ?></div><?php endif; ?>

    <label for="instruksi">Instruksi tugas</label>
    <textarea id="instruksi" name="instruksi"><?= e($data['instruksi']) ?></textarea>
    <?php if (isset($err['instruksi'])): ?><div class="field-error"><?= e($err['instruksi']) ?></div><?php endif; ?>

    <label for="tenggat">Tenggat</label>
    <input type="date" id="tenggat" name="tenggat" value="<?= e($data['tenggat']) ?>">
    <?php if (isset($err['tenggat'])): ?><div class="field-error"><?= e($err['tenggat']) ?></div><?php endif; ?>

    <label for="status">Status</label>
    <select id="status" name="status">
        <?php foreach ($STATUS as $st): ?>
            <option value="<?= e($st) ?>" <?= $data['status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($err['status'])): ?><div class="field-error"><?= e($err['status']) ?></div><?php endif; ?>

    <p><button class="btn btn-primary" type="submit">Simpan data</button>
       <a class="btn btn-outline" href="tugas.php">Batal</a></p>
</form>
<?php include 'footer.php'; ?>
