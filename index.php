<?php
session_start();
require_once 'config/database.php';

// --- 1. INISIALISASI VARIABEL PENCARIAN ---
$keyword   = isset($_GET['q']) ? $_GET['q'] : '';
$type      = isset($_GET['type']) ? $_GET['type'] : '';
$location  = isset($_GET['location']) ? $_GET['location'] : '';
$selected_depts = isset($_GET['dept']) ? $_GET['dept'] : []; 

// --- 2. BANGUN QUERY JOBS SECARA DINAMIS ---
$sql = "SELECT j.*, d.name AS dept_name, r.education, r.experience
        FROM jobs j
        JOIN departments d ON j.department_id = d.department_id
        JOIN requirements r ON j.requirement_id = r.requirement_id
        WHERE j.closing_date >= CURDATE()";

$params = [];

// A. Filter Keyword
if (!empty($keyword)) {
    $sql .= " AND (j.title LIKE :keyword OR j.description LIKE :keyword)";
    $params[':keyword'] = "%" . $keyword . "%";
}

// B. Filter Tipe
if (!empty($type)) {
    $sql .= " AND j.job_type = :type";
    $params[':type'] = $type;
}

// D. Filter Departemen (Checkbox Array)
if (!empty($selected_depts)) {
    $dept_placeholders = [];
    foreach ($selected_depts as $key => $dept_id) {
        $placeholder = ":dept_" . $key;
        $dept_placeholders[] = $placeholder;
        $params[$placeholder] = $dept_id;
    }
    $sql .= " AND j.department_id IN (" . implode(',', $dept_placeholders) . ")";
}

$sql .= " ORDER BY j.job_id DESC";

// Eksekusi Query
try {
    $stmtJobs = $conn->prepare($sql);
    $stmtJobs->execute($params);
    $jobs = $stmtJobs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error Query: " . $e->getMessage());
}

// --- 3. QUERY DEPARTEMEN (UNTUK SIDEBAR) ---
$stmtDepts = $conn->query("SELECT * FROM departments ORDER BY name ASC");
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
            <input type="text" name="q" placeholder="Nama Pekerjaan atau kata kunci" value="<?php echo htmlspecialchars($keyword); ?>">
            
            <select name="type">
                <option value="">Semua</option>
                <option value="On-Site" <?php echo ($type == 'On-Site') ? 'selected' : ''; ?>>On-Site</option>
                <option value="Remote"  <?php echo ($type == 'Remote') ? 'selected' : ''; ?>>Remote</option>
                <option value="Hybrid"  <?php echo ($type == 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
            </select>
            
            <?php foreach($selected_depts as $sd): ?>
                <input type="hidden" name="dept[]" value="<?php echo $sd; ?>">
            <?php endforeach; ?>

            <button type="submit" class="btn-search">Search</button>
        </form>
    </div>

    <div class="container">
        
        <aside class="sidebar">
            <form action="" method="GET" id="filterForm">
                <h3>Departemen</h3>
                <div class="divider"></div>
                
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($keyword); ?>">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">

                <ul>
                    <?php foreach($departments as $dept): ?>
                    <li>
                        <label style="cursor: pointer; display: flex; align-items: center;">
                            <input type="checkbox" name="dept[]" 
                                   value="<?php echo $dept['department_id']; ?>"
                                   <?php echo (in_array($dept['department_id'], $selected_depts)) ? 'checked' : ''; ?>
                                   onchange="this.form.submit()">
                            
                            <span style="margin-left: 8px;"><?php echo htmlspecialchars($dept['name']); ?></span>
                        </label>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <?php if(!empty($selected_depts) || !empty($keyword) || !empty($type) || !empty($location)): ?><?php endif; ?>
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
                            <span style="border-color: #7C3AED; color: #7C3AED;"><?php echo htmlspecialchars($job['dept_name']); ?></span>
                            <span><?php echo htmlspecialchars($job['experience']); ?></span>
                        </div>

                        <div class="description">
                            <?php echo nl2br(htmlspecialchars(substr($job['description'], 0, 150))) . '...'; ?>
                        </div>

                        <div class="divider-card"></div>

                        <div class="card-footer">
                            <span>Quota: <?php echo $job['quota']; ?> Orang</span>
                            <span>Tutup: <?php echo date('d M Y', strtotime($job['closing_date'])); ?></span>
                        </div>
                        
                        <a href="admin/jobs/detail.php?id=<?php echo $job['job_id']; ?>" style="margin-top: 15px; text-align: right; color: #000; font-weight: 600; text-decoration: underline; font-size: 14px;">Lihat Detail &rarr;</a>
                    </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: white; border-radius: 12px; border: 1px solid #E2E8F0;">
                        <h3 style="color: #64748B;">Lowongan tidak ditemukan.</h3>
                        <a href="index.php" class="btn-search" style="display:inline-block; margin-top:15px; text-decoration:none;">Tampilkan Semua Lowongan</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>

    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>