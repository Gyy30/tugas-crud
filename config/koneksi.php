<?php
define('DB_HOST', 'localhost');      
define('DB_USER', 'root');          
define('DB_PASS', '');              
define('DB_NAME', 'db_absensi_siswa'); 

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set karakter
$conn->set_charset("utf8");
?>