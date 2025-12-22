<?php
session_start();
require_once '../../config/database.php';

// 1. Cek ID Lowongan
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id = $_GET['id'];

// 2. Ambil Data Detail Lowongan (JOIN ke Department & Requirements)
try {
    $query = "SELECT j.*, d.name as dept_name, r.education, r.experience 
              FROM jobs j 
              LEFT JOIN departments d ON j.department_id = d.department_id 
              LEFT JOIN requirements r ON j.requirement_id = r.requirement_id
              WHERE j.job_id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika data tidak ditemukan
    if (!$job) {
        echo "Lowongan tidak ditemukan.";
        exit;
    }

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail: <?php echo htmlspecialchars($job['title']); ?> - Miso Corp</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include '../../includes/header.php'; ?>

    <div class="breadcrumb">
        <a href="../../index.php">Lowongan Tersedia</a> &nbsp; &gt; &nbsp;
        <strong><?php echo htmlspecialchars($job['title']); ?></strong>
    </div>

    <div class="detail-card">
        
        <div class="job-header">
            
            <div class="job-info-left">
                <ul class="info-list">
                    <li>
                        <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                        <span>IDR <?php echo number_format($job['salary'], 0, ',', '.'); ?> / Bulan</span>
                    </li>
                    <li>
                        <div class="icon"><i class="fas fa-building"></i></div>
                        <span>Industri Jasa &gt; Departemen <?php echo htmlspecialchars($job['dept_name']); ?></span>
                    </li>
                    <li>
                        <div class="icon"><i class="fas fa-home"></i></div>
                        <span>Kerja &gt; <?php echo htmlspecialchars($job['job_type']); ?></span>
                    </li>
                    <li>
                        <div class="icon"><i class="fas fa-graduation-cap"></i></div>
                        <span>Minimal <?php echo htmlspecialchars($job['education']); ?></span>
                    </li>
                    <li>
                        <div class="icon"><i class="fas fa-briefcase"></i></div>
                        <span><?php echo htmlspecialchars($job['experience']); ?> Pengalaman</span>
                    </li>
                </ul>

                <div class="job-meta">
                    Tayang sejak <?php echo date('d M Y', strtotime($job['closing_date'] . ' -1 month')); ?>
                </div>

                <div class="action-buttons">
                    <button class="btn-action" style="background-color: #B0C4DE;">TANDAI</button>
                    <button class="btn-action" style="background-color: #B0C4DE;">BAGIKAN</button>
                </div>
            </div>

            <div class="job-info-right">
                
                <div class="req-section">
                    <h4>Persyaratan</h4>
                    <div class="tags-group">
                        <span class="tag">Laki-laki / Perempuan</span>
                        <span class="tag"><?php echo htmlspecialchars($job['experience']); ?></span>
                        <span class="tag">Minimal <?php echo htmlspecialchars($job['education']); ?></span>
                        
                        <span class="tag">20 - 35 Tahun</span> 
                    </div>
                </div>

                <div class="req-section">
                    <h4>Skills (Contoh)</h4>
                    <div class="tags-group">
                        <span class="tag">Microsoft Office</span>
                        <span class="tag">Communication</span>
                        <span class="tag">Teamwork</span>
                        <span class="tag"><?php echo htmlspecialchars($job['dept_name']); ?> Knowledge</span>
                    </div>
                </div>

            </div>
        </div>
        <hr class="divider">

        <div class="job-body">
            
            <div class="company-info">
                <h3>Loker Ini di Kelola Oleh</h3>
                <div class="company-name">Miso Corporation</div>

                <div class="job-title-mini">
                    Deskripsi pekerjaan <?php echo htmlspecialchars($job['title']); ?>
                </div>

                <div class="section-title">Sistem Kerja</div>
                <div class="small-text">
                    <?php 
                        if($job['job_type'] == 'Hybrid') echo "3 Hari WFO + 2 Hari WFA";
                        elseif($job['job_type'] == 'Remote') echo "Full Remote Working";
                        else echo "Full Work From Office (WFO)";
                    ?>
                </div>
                <div class="small-text">
                    Selama WFO maupun WFA wajib mengirim Daily Report ke atasan.
                </div>
            </div>

            <div class="job-desc-details">
                <h3>Deskripsi Pekerjaan</h3>
                
                <div class="desc-item">
                    <p>
                        <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                    </p>
                </div>

                <?php if (strlen($job['description']) < 50): ?>
                    <div class="desc-item">
                        <h4>1. Tanggung Jawab Utama</h4>
                        <ul>
                            <li>Melakukan tugas operasional harian sesuai standar perusahaan.</li>
                            <li>Berkoordinasi dengan tim internal untuk mencapai target.</li>
                            <li>Membuat laporan berkala kepada atasan.</li>
                        </ul>
                    </div>
                    <div class="desc-item">
                        <h4>2. Kualifikasi Tambahan</h4>
                        <ul>
                            <li>Memiliki kemampuan komunikasi yang baik.</li>
                            <li>Dapat bekerja di bawah tekanan.</li>
                            <li>Bersedia ditempatkan di kantor pusat.</li>
                        </ul>
                    </div>
                <?php endif; ?>

            </div>

        </div>
        </div> 
    </body>
</html>