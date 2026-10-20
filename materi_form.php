<?php
require 'helpers.php'; require 'koneksi.php';

$edit = isset($_GET['id']);
$id = 0; $lama = null;
if ($edit) {
    $id = valid_id($_GET['id']);
    if (!$id) { flash('error', 'ID tidak valid.'); redirect('materi.php'); }
    $s = $conn->prepare('SELECT id, mata_kuliah_id, judul, isi, tautan FROM materi WHERE id = ?');
    $s->bind_param('i', $id); $s->execute();
    $lama = $s->get_result()->fetch_assoc();
    if (!$lama) { flash('error', 'Data materi tidak ditemukan.'); redirect('materi.php'); }
}
$data = $lama ?: ['mata_kuliah_id' => '', 'judul' => '', 'isi' => '', 'tautan' => ''];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { flash('error', 'Token tidak valid. Permintaan ditolak.'); redirect('materi.php'); }
    $data = ['mata_kuliah_id' => post('mata_kuliah_id'), 'judul' => post('judul'), 'isi' => post('isi'), 'tautan' => post('tautan')];

    $mkId = valid_id($data['mata_kuliah_id']);
    if (!$mkId || !mk_ada($conn, $mkId)) $err['mata_kuliah_id'] = 'Pilih mata kuliah yang tersedia.';
    if (panjang($data['judul']) < 3 || panjang($data['judul']) > 150) $err['judul'] = 'Judul harus 3-150 karakter.';
    if (panjang($data['isi']) < 3 || panjang($data['isi']) > 2000) $err['isi'] = 'Isi materi harus 3-2000 karakter.';
    if (!url_valid($data['tautan'])) $err['tautan'] = 'Gunakan tautan http/https yang valid (maksimal 255 karakter).';

    if (!$err) {
        if ($edit && (int)$lama['mata_kuliah_id'] === $mkId && $lama['judul'] === $data['judul']
            && $lama['isi'] === $data['isi'] && $lama['tautan'] === $data['tautan']) {
            flash('info', 'Tidak ada perubahan data.');
            redirect('materi.php');
        }
        try {
            if ($edit) {
                $q = $conn->prepare('UPDATE materi SET mata_kuliah_id = ?, judul = ?, isi = ?, tautan = ? WHERE id = ?');
                $q->bind_param('isssi', $mkId, $data['judul'], $data['isi'], $data['tautan'], $id);
            } else {
                $q = $conn->prepare('INSERT INTO materi (mata_kuliah_id, judul, isi, tautan) VALUES (?, ?, ?, ?)');
                $q->bind_param('isss', $mkId, $data['judul'], $data['isi'], $data['tautan']);
            }
            $q->execute();
            flash('success', $edit ? 'Materi berhasil diubah.' : 'Materi berhasil ditambahkan.');
            redirect('materi.php');
        } catch (mysqli_sql_exception $ex) {
            $err['umum'] = 'Data gagal disimpan karena kesalahan database.';
        }
    }
}

$opsi = daftar_mk($conn);
$judul = $edit ? 'Edit materi' : 'Tambah materi';
include 'header.php';
if (isset($err['umum'])) echo '<div class="alert error">' . e($err['umum']) . '</div>';
$aksi = 'materi_form.php' . ($edit ? '?id=' . $id : '');
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

    <label for="judul">Judul materi</label>
    <input type="text" id="judul" name="judul" value="<?= e($data['judul']) ?>">
    <?php if (isset($err['judul'])): ?><div class="field-error"><?= e($err['judul']) ?></div><?php endif; ?>

    <label for="isi">Isi materi</label>
    <textarea id="isi" name="isi"><?= e($data['isi']) ?></textarea>
    <?php if (isset($err['isi'])): ?><div class="field-error"><?= e($err['isi']) ?></div><?php endif; ?>

    <label for="tautan">Tautan referensi</label>
    <input type="text" id="tautan" name="tautan" value="<?= e($data['tautan']) ?>">
    <?php if (isset($err['tautan'])): ?><div class="field-error"><?= e($err['tautan']) ?></div><?php endif; ?>

    <p><button class="btn btn-primary" type="submit">Simpan data</button>
       <a class="btn btn-outline" href="materi.php">Batal</a></p>
</form>
<?php include 'footer.php'; ?>
