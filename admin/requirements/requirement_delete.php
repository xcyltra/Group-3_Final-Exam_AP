<?php
session_start();
require_once '../../config/database.php';

// Cek akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php"); exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM requirements WHERE requirement_id = :id");
        $stmt->execute([':id' => $id]);
        
        header("Location: index.php?msg=deleted");
    } catch (PDOException $e) {
        // Tampilkan pesan jika gagal (biasanya karena ID ini dipakai di tabel Jobs)
        echo "<div style='font-family: Poppins; text-align: center; margin-top: 50px;'>";
        echo "<h3>Gagal Menghapus!</h3>";
        echo "<p>Paket persyaratan ini sedang digunakan oleh Lowongan Kerja yang aktif.</p>";
        echo "<p>Silakan hapus atau ubah lowongan kerja yang menggunakan paket ini terlebih dahulu.</p>";
        echo "<a href='index.php' style='background: #333; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali</a>";
        echo "</div>";
    }
} else {
    header("Location: index.php");
}
?>