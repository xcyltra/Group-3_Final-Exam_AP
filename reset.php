<?php
// Panggil koneksi database Anda
require_once 'config/database.php';

// 1. Buat Hash baru untuk password "123456"
// Ini akan menggunakan algoritma yang sesuai dengan versi PHP di laptop Anda
$new_password = "123456";
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // 2. Update SEMUA user di database agar passwordnya menjadi hash baru ini
    $sql = "UPDATE user SET password = :pass";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':pass' => $hashed_password]);

    echo "<h1>BERHASIL!</h1>";
    echo "<p>Semua password user telah direset menjadi: <b>123456</b></p>";
    echo "<p>Hash baru yang valid adalah: <br><code>$hashed_password</code></p>";
    echo "<br><a href='auth/login.php'>Klik disini untuk Login</a>";

} catch (PDOException $e) {
    echo "Gagal mereset password: " . $e->getMessage();
}
?>