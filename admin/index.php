<?php
session_start();
require_once '../config/database.php';

// Cek akses admin
// Pastikan user sudah login dan role-nya adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil data jobs
$query = "SELECT j.*, d.name as dept_name FROM jobs j 
          LEFT JOIN departments d ON j.department_id = d.department_id 
          ORDER BY j.job_id DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Miso Corp</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="admin-container">
        
        <h2 class="admin-header-title">Kelola Lowongan Pekerjaan</h2>
        
        <a href="job_create.php" class="btn-add-job">+ Tambah Lowongan</a>

        <div class="table-responsive">
            
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Judul Pekerjaan</th>
                        <th>Departemen</th>
                        <th>Tipe</th>
                        <th>Quota</th>
                        <th>Tutup</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($jobs) > 0): ?>
                        <?php foreach($jobs as $job): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($job['title']); ?></td>
                            <td><?php echo htmlspecialchars($job['dept_name']); ?></td>
                            <td>
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($job['job_type']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($job['quota']); ?></td>
                            <td><?php echo date('d M Y', strtotime($job['closing_date'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="job_edit.php?id=<?php echo $job['job_id']; ?>" class="btn-action btn-edit">Edit</a>
                                    <a href="job_delete.php?id=<?php echo $job['job_id']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus lowongan ini?');">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">Belum ada data lowongan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div> </div> </body>
</html>