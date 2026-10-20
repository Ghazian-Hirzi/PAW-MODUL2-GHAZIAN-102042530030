<?php /* variabel $judul diisi sebelum include */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'LMS') ?> - LMS</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav>
    <a href="index.php">Beranda</a>
    <a href="mata_kuliah.php">Mata Kuliah</a>
    <a href="materi.php">Materi</a>
    <a href="tugas.php">Tugas</a>
</nav>
<main class="card">
<h1><?= e($judul ?? 'LMS') ?></h1>
<?php tampil_flash(); ?>
