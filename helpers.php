<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url) { header('Location: ' . $url); exit; }

// ID harus bilangan bulat >= 1; selain itu dianggap tidak valid
function valid_id($v): ?int {
    if (!is_string($v) && !is_int($v)) return null;
    $r = filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $r === false ? null : $r;
}
function post(string $k): string { return isset($_POST[$k]) && is_string($_POST[$k]) ? trim($_POST[$k]) : ''; }
function get_str(string $k): string { return isset($_GET[$k]) && is_string($_GET[$k]) ? trim($_GET[$k]) : ''; }
function panjang(string $s): int { return mb_strlen($s, 'UTF-8'); }

function tanggal_valid(string $s): bool {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d && $d->format('Y-m-d') === $s;
}
function url_valid(string $s): bool {
    return panjang($s) <= 255 && preg_match('#^https?://#i', $s) && filter_var($s, FILTER_VALIDATE_URL) !== false;
}

// Pesan sekali tampil (disimpan di session)
function flash(string $tipe, string $pesan) { $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan]; }
function tampil_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="alert ' . e($f['tipe']) . '">' . e($f['pesan']) . '</div>';
    }
}

// Token CSRF
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="token" value="' . e(csrf_token()) . '">'; }
function csrf_valid(): bool {
    return isset($_POST['token'], $_SESSION['csrf']) && is_string($_POST['token'])
        && hash_equals($_SESSION['csrf'], $_POST['token']);
}

function daftar_mk(mysqli $conn): array {
    $r = $conn->prepare('SELECT id, kode, nama FROM mata_kuliah ORDER BY nama');
    $r->execute();
    return $r->get_result()->fetch_all(MYSQLI_ASSOC);
}
function mk_ada(mysqli $conn, int $id): bool {
    $s = $conn->prepare('SELECT id FROM mata_kuliah WHERE id = ?');
    $s->bind_param('i', $id); $s->execute();
    return $s->get_result()->num_rows > 0;
}

/* Hapus: GET = halaman konfirmasi, POST = proses hapus (token + ID).
   Nama tabel hanya dari daftar tetap di bawah, bukan dari isian pengguna. */
function proses_hapus(mysqli $conn, string $kunci): void {
    $konfig = [
        'mata_kuliah' => ['tabel' => 'mata_kuliah', 'label' => 'Mata kuliah', 'kolom' => 'nama',  'daftar' => 'mata_kuliah.php', 'self' => 'mata_kuliah_hapus.php'],
        'materi'      => ['tabel' => 'materi',      'label' => 'Materi',      'kolom' => 'judul', 'daftar' => 'materi.php',      'self' => 'materi_hapus.php'],
        'tugas'       => ['tabel' => 'tugas',       'label' => 'Tugas',       'kolom' => 'judul', 'daftar' => 'tugas.php',       'self' => 'tugas_hapus.php'],
    ];
    if (!isset($konfig[$kunci])) exit('Tabel tidak dikenal.');
    $k = $konfig[$kunci];
    $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

    if ($isPost && !csrf_valid()) { flash('error', 'Token tidak valid. Permintaan ditolak.'); redirect($k['daftar']); }

    $id = valid_id($isPost ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null));
    if (!$id) { flash('error', 'ID tidak valid.'); redirect($k['daftar']); }

    $s = $conn->prepare("SELECT id, {$k['kolom']} AS nama FROM {$k['tabel']} WHERE id = ?");
    $s->bind_param('i', $id); $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) { flash('error', 'Data tidak ditemukan.'); redirect($k['daftar']); }

    $error = '';
    if ($isPost) {
        try {
            $d = $conn->prepare("DELETE FROM {$k['tabel']} WHERE id = ?");
            $d->bind_param('i', $id); $d->execute();
            flash('success', 'Data berhasil dihapus.');
            redirect($k['daftar']);
        } catch (mysqli_sql_exception $ex) {
            if ((int)$ex->getCode() === 1451) {
                $error = 'Data belum tersimpan. Mata kuliah masih memiliki materi atau tugas. Hapus data terkait terlebih dahulu.';
            } else {
                $error = 'Data gagal dihapus karena kesalahan database.';
            }
        }
    }

    $judul = $k['label'];
    include 'header.php';
    if ($error) echo '<div class="alert error">' . e($error) . '</div>';
    ?>
    <p>Hapus <strong><?= e($row['nama']) ?></strong>?</p>
    <p>Data baru dihapus setelah tombol konfirmasi dikirim.</p>
    <form method="post" action="<?= e($k['self']) ?>" class="inline">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
        <button type="submit" class="btn btn-danger">Ya, hapus data</button>
        <a class="btn btn-outline" href="<?= e($k['daftar']) ?>">Batal</a>
    </form>
    <?php
    include 'footer.php';
}
