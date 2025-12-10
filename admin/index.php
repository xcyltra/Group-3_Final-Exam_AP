<?php
session_start();
require_once '../config/database.php';

// Cek Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    // --- LOGIKA DATABASE (Sama seperti sebelumnya) ---
    // 1. Lowongan
    $stmt = $conn->query("SELECT COUNT(*) FROM jobs");
    $total_jobs = $stmt->fetchColumn();
    
    $stmt = $conn->query("SELECT COUNT(*) FROM jobs WHERE job_type = 'On-Site'");
    $job_onsite = $stmt->fetchColumn();
    $stmt = $conn->query("SELECT COUNT(*) FROM jobs WHERE job_type = 'Hybrid'");
    $job_hybrid = $stmt->fetchColumn();
    $stmt = $conn->query("SELECT COUNT(*) FROM jobs WHERE job_type = 'Remote'");
    $job_remote = $stmt->fetchColumn();

    // 2. Pendaftar
    $stmt = $conn->query("SELECT COUNT(*) FROM applications");
    $total_applicants = $stmt->fetchColumn();

    $stmt = $conn->query("SELECT COUNT(a.application_id) FROM applications a JOIN jobs j ON a.job_id = j.job_id WHERE j.job_type = 'On-Site'");
    $app_onsite = $stmt->fetchColumn();
    $stmt = $conn->query("SELECT COUNT(a.application_id) FROM applications a JOIN jobs j ON a.job_id = j.job_id WHERE j.job_type = 'Hybrid'");
    $app_hybrid = $stmt->fetchColumn();
    $stmt = $conn->query("SELECT COUNT(a.application_id) FROM applications a JOIN jobs j ON a.job_id = j.job_id WHERE j.job_type = 'Remote'");
    $app_remote = $stmt->fetchColumn();

    // 3. Dept & Req
    $stmt = $conn->query("SELECT COUNT(*) FROM departments");
    $total_dept = $stmt->fetchColumn();
    $stmt = $conn->query("SELECT COUNT(*) FROM requirements");
    $total_req = $stmt->fetchColumn();

    // 4. User
    $stmt = $conn->query("SELECT COUNT(*) FROM user");
    $total_user = $stmt->fetchColumn();
    
    $stmt = $conn->query("SELECT COUNT(*) FROM user WHERE role = 'admin'");
    $user_admin = $stmt->fetchColumn();
    $stmt = $conn->query("SELECT COUNT(*) FROM user WHERE role = 'interviewer'");
    $user_interviewer = $stmt->fetchColumn();
    $stmt = $conn->query("SELECT COUNT(*) FROM user WHERE role = 'candidate'");
    $user_candidate = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="dashboard-container">
        <h2>Dashboard Admin</h2>

        <div class="stats-grid">
            
            <div class="stat-card">
                <h3>Total Lowongan Kerja</h3>
                <div class="number"><?php echo $total_jobs; ?></div>
                <div class="details">
                    <p>Total Berdasarkan Lokasi :</p>
                    <ul>
                        <li>On-Site = <?php echo $job_onsite; ?></li>
                        <li>Hybrid = <?php echo $job_hybrid; ?></li>
                        <li>Remote = <?php echo $job_remote; ?></li>
                    </ul>
                </div>
                <svg class="bg-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>

            <div class="stat-card">
                <h3>Total Pendaftar</h3>
                <div class="number"><?php echo $total_applicants; ?></div>
                <div class="details">
                    <p>Total Berdasarkan Lokasi :</p>
                    <ul>
                        <li>On-Site = <?php echo $app_onsite; ?></li>
                        <li>Hybrid = <?php echo $app_hybrid; ?></li>
                        <li>Remote = <?php echo $app_remote; ?></li>
                    </ul>
                </div>
                <svg class="bg-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="18" x2="12" y2="12"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                </svg>
            </div>

            <div class="stat-column">
                <div class="stat-card small">
                    <h3>Total Departemen</h3>
                    <div class="number"><?php echo $total_dept; ?></div>
                </div>
                <div class="stat-card small">
                    <h3>Total Persyaratan</h3>
                    <div class="number"><?php echo $total_req; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <h3>Total Pengguna</h3>
                <div class="number"><?php echo $total_user; ?></div>
                <div class="details">
                    <p>Total Berdasarkan Role :</p>
                    <ul>
                        <li>Admin = <?php echo $user_admin; ?></li>
                        <li>Interviewer = <?php echo $user_interviewer; ?></li>
                        <li>Candidate = <?php echo $user_candidate; ?></li>
                    </ul>
                </div>
                <svg class="bg-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>

        </div>
    </div>

</body>
</html>