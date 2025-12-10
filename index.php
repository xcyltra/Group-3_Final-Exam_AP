<?php
session_start();
require_once 'config/database.php';

// --- QUERY DATA ---
// 1. Ambil Lowongan (JOIN jobs, departments, requirements)
$queryJobs = "SELECT 
                j.*, 
                d.name AS dept_name, 
                r.education, 
                r.experience,
                j.job_type,
                j.salary,
                j.quota,
                j.closing_date
              FROM jobs j
              JOIN departments d ON j.department_id = d.department_id
              JOIN requirements r ON j.requirement_id = r.requirement_id
              WHERE j.closing_date >= CURDATE()
              ORDER BY j.job_id DESC";
$stmtJobs = $conn->prepare($queryJobs);
$stmtJobs->execute();
$jobs = $stmtJobs->fetchAll(PDO::FETCH_ASSOC);

// 2. Ambil Departemen (Untuk Sidebar)
$queryDepts = "SELECT * FROM departments";
$stmtDepts = $conn->prepare($queryDepts);
$stmtDepts->execute();
$departments = $stmtDepts->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Miso Corporation - Lowongan Kerja</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="search-section">
        <form action="" method="GET" class="search-bar">
            <input type="text" name="q" placeholder="Nama Pekerjaan atau kata kunci">
            
            <select name="type">
                <option value="">Tipe Pekerjaan</option>
                <option value="On-Site">On-Site</option>
                <option value="Remote">Remote</option>
                <option value="Hybrid">Hybrid</option>
            </select>
            
            <select name="location">
                <option value="">Lokasi Kerja</option>
                <option value="Jakarta">Jakarta</option>
                <option value="Bandung">Bandung</option>
            </select>
            
            <button type="submit" class="btn-search">Search</button>
        </form>
    </div>

    <div class="container">
        
        <aside class="sidebar">
            <h3>Departemen</h3>
            <div class="divider"></div>
            
            <form action="" method="GET">
                <ul>
                    <?php foreach($departments as $dept): ?>
                    <li>
                        <label style="cursor: pointer; display: flex; align-items: center;">
                            <input type="checkbox" name="dept[]" value="<?php echo $dept['department_id']; ?>">
                            <span style="margin-left: 8px;"><?php echo htmlspecialchars($dept['name']); ?></span>
                        </label>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </form>
        </aside>

        <main class="content">
            <h2>Lowongan Kerja Miso Corporation</h2>
            
            <div class="job-grid">
                <?php if (count($jobs) > 0): ?>
                    <?php foreach($jobs as $job): ?>
                    
                    <div class="job-card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                            <div class="salary">
                                Rp <?php echo number_format($job['salary']/1000000, 1, ',', '.'); ?> jt 
                                <span style="font-weight: normal; color: #666;">- <?php echo number_format(($job['salary']/1000000)+2, 1, ',', '.'); ?> jt</span>
                            </div>
                        </div>

                        <div class="tags">
                            <span><?php echo $job['job_type']; ?></span> 
                            <span><?php echo htmlspecialchars($job['experience']); ?></span>
                            <span><?php echo htmlspecialchars($job['education']); ?></span>
                        </div>

                        <div class="description">
                            <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                        </div>

                        <div class="divider-card"></div>

                        <div class="card-footer">
                            <span>Quota: <?php echo $job['quota']; ?> Orang</span>
                            <span>Tutup: <?php echo date('d F Y', strtotime($job['closing_date'])); ?></span>
                        </div>
                        
                        <a href="admin/jobs/detail.php?id=<?php echo $job['job_id']; ?>" style="margin-top: 15px; text-align: right; color: #000; font-weight: 600; text-decoration: underline; font-size: 14px;">Lihat Detail &rarr;</a>
                    </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; text-align: center;">Tidak ada lowongan tersedia saat ini.</p>
                <?php endif; ?>
            </div>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>