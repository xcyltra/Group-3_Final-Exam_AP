<?php
session_start();
require_once '../../config/database.php';

// 1. Cek Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// 2. Cek ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // Proses Delete
        $stmt = $conn->prepare("DELETE FROM jobs WHERE job_id = :id");
        $stmt->execute([':id' => $id]);
        
        // Redirect kembali ke index dengan pesan sukses
        header("Location: job.php?msg=deleted");
    } catch (PDOException $e) {
        // Redirect jika gagal (misal karena constraint Foreign Key)
        echo "Gagal menghapus data. Kemungkinan data ini sedang digunakan di tabel lamaran.";
        echo "<br><a href='job.php'>Kembali</a>";
    }
} else {
    header("Location: index.php");
}
?>