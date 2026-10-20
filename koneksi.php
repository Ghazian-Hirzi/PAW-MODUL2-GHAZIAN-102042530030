<?php
<<<<<<< HEAD
// Koneksi ke database LMS (bukan database showroom)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli('localhost', 'root', '', 'db_lms');
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL aktif, port benar, dan database db_lms sudah diimpor.');
}
=======
$conn = mysqli_connect('127.0.0.1', 'root', '', 'paw_modul2_showroom');
mysqli_set_charset($conn, 'utf8mb4');
>>>>>>> 42c382d198a108e2ce15b50004b39e88b7bca2b3
