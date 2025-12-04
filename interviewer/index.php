<?php
session_start();
require_once '../config/database.php';

// Cek akses interviewer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'interviewer') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Interviewer</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h2>Dashboard Interviewer</h2>
        </div>

        <div class="form-admin-card" style="text-align: center; padding: 50px;">
            <h3>Selamat Datang, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h3>
            <p>Silakan pilih menu untuk mulai melakukan penilaian terhadap kandidat.</p>
            <br>
            <a href="#" class="btn-daftar">Lihat Daftar Pelamar</a>
        </div>
    </div>
</body>
</html>