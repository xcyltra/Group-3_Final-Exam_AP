<?php
session_start();
require_once '../config/database.php';

if (isset($_GET['id']) && $_SESSION['role'] == 'admin') {
    $id = $_GET['id'];
    
    // Hapus dari jobs (Cascade akan menangani foreign key jika di set, jika tidak hapus manual)
    $stmt = $conn->prepare("DELETE FROM jobs WHERE job_id = ?");
    
    if ($stmt->execute([$id])) {
        echo "<script>alert('Data Terhapus'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal Hapus'); window.location='index.php';</script>";
    }
} else {
    header("Location: index.php");
}
?>