<?php
$host = "localhost";
$db_name = "miso_corp";
$username = "root";
$password = ""; // Sesuaikan jika ada password database

try {
    // Membuat koneksi PDO
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    
    // Set mode error ke Exception agar mudah di-debug
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // Jika gagal, hentikan program dan tampilkan pesan
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>