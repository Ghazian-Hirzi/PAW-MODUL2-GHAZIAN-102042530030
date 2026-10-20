<?php
// Koneksi ke database LMS (bukan database showroom)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli('localhost', 'root', '', 'db_lms');
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL aktif, port benar, dan database db_lms sudah diimpor.');
}
