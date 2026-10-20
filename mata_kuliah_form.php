<?php
require 'helpers.php'; require 'koneksi.php';

$edit = isset($_GET['id']);
$id = 0; $lama = null;
if ($edit) {
    $id = valid_id($_GET['id']);
    if (!$id) { flash('error', 'ID tidak valid.'); redirect('mata_kuliah.php'); }
    $s = $conn->prepare('SELECT id, kode, nama, dosen, semester FROM mata_kuliah WHERE id = ?');
    $s->bind_param('i', $id); $s->execute();
    $lama = $s->get_result()->fetch_assoc();
    if (!$lama) { flash('error', 'Data mata kuliah tidak ditemukan.'); redirect('mata_kuliah.php'); }
}
$data = $lama ?: ['kode' => '', 'nama' => '', 'dosen' => '', 'semester' => ''];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { flash('error', 'Token tidak valid. Permintaan ditolak.'); redirect('mata_kuliah.php'); }
    $data = ['kode' => post('kode'), 'nama' => post('nama'), 'dosen' => post('dosen'), 'semester' => post('semester')];

    // Pemeriksaan isian
    if (!preg_match('/^[A-Z0-9-]{3,20}$/', $data['kode'])) $err['kode'] = 'Kode 3-20 karakter: huruf kapital, angka, atau tanda -.';
    if (panjang($data['nama']) < 3 || panjang($data['nama']) > 100) $err['nama'] = 'Nama harus 3-100 karakter.';
    if (panjang($data['dosen']) < 3 || panjang($data['dosen']) > 100) $err['dosen'] = 'Dosen harus 3-100 karakter.';
    $smt = filter_var($data['semester'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 14]]);
    if ($smt === false) $err['semester'] = 'Semester harus bilangan bulat 1-14.';

    // Kode harus unik (selain milik data ini sendiri)
    if (!isset($err['kode'])) {
        $u = $conn->prepare('SELECT id FROM mata_kuliah WHERE kode = ? AND id <> ?');
        $u->bind_param('si', $data['kode'], $id); $u->execute();
        if ($u->get_result()->num_rows > 0) $err['kode'] = 'Kode mata kuliah sudah digunakan.';
    }

    if (!$err) {
        if ($edit && $lama['kode'] === $data['kode'] && $lama['nama'] === $data['nama']
            && $lama['dosen'] === $data['dosen'] && (int)$lama['semester'] === $smt) {
            flash('info', 'Tidak ada perubahan data.');
            redirect('mata_kuliah.php');
        }
        try {
            if ($edit) {
                $q = $conn->prepare('UPDATE mata_kuliah SET kode = ?, nama = ?, dosen = ?, semester = ? WHERE id = ?');
                $q->bind_param('sssii', $data['kode'], $data['nama'], $data['dosen'], $smt, $id);
            } else {
                $q = $conn->prepare('INSERT INTO mata_kuliah (kode, nama, dosen, semester) VALUES (?, ?, ?, ?)');
                $q->bind_param('sssi', $data['kode'], $data['nama'], $data['dosen'], $smt);
            }
            $q->execute();
            flash('success', $edit ? 'Mata kuliah berhasil diubah.' : 'Mata kuliah berhasil ditambahkan.');
            redirect('mata_kuliah.php');
        } catch (mysqli_sql_exception $ex) {
            if ((int)$ex->getCode() === 1062) $err['kode'] = 'Kode mata kuliah sudah digunakan.';
            else $err['umum'] = 'Data gagal disimpan karena kesalahan database.';
        }
    }
}

$judul = $edit ? 'Edit mata kuliah' : 'Tambah mata kuliah';
include 'header.php';
if (isset($err['umum'])) echo '<div class="alert error">' . e($err['umum']) . '</div>';
$aksi = 'mata_kuliah_form.php' . ($edit ? '?id=' . $id : '');
?>
<form method="post" action="<?= e($aksi) ?>" novalidate>
    <?= csrf_field() ?>
    <label for="kode">Kode mata kuliah</label>
    <input type="text" id="kode" name="kode" value="<?= e($data['kode']) ?>">
    <?php if (isset($err['kode'])): ?><div class="field-error"><?= e($err['kode']) ?></div><?php endif; ?>

    <label for="nama">Nama mata kuliah</label>
    <input type="text" id="nama" name="nama" value="<?= e($data['nama']) ?>">
    <?php if (isset($err['nama'])): ?><div class="field-error"><?= e($err['nama']) ?></div><?php endif; ?>

    <label for="dosen">Dosen pengampu</label>
    <input type="text" id="dosen" name="dosen" value="<?= e($data['dosen']) ?>">
    <?php if (isset($err['dosen'])): ?><div class="field-error"><?= e($err['dosen']) ?></div><?php endif; ?>

    <label for="semester">Semester</label>
    <input type="text" id="semester" name="semester" value="<?= e($data['semester']) ?>">
    <?php if (isset($err['semester'])): ?><div class="field-error"><?= e($err['semester']) ?></div><?php endif; ?>

    <p><button class="btn btn-primary" type="submit">Simpan data</button>
       <a class="btn btn-outline" href="mata_kuliah.php">Batal</a></p>
</form>
<?php include 'footer.php'; ?>
