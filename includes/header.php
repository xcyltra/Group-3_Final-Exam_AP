<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Base URL (Sesuaikan folder htdocs Anda jika perlu)
// Jika folder project Anda bernama 'recruitment', biarkan seperti ini.
$base_url = '/Group-3_Final-Exam_AP'; 

// Deteksi halaman saat ini untuk class 'active'
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    
    <div class="nav-left">
        <a href="<?php echo $base_url; ?>/index.php" class="logo">
            MISO CORP.
        </a>

        <div class="nav-links">
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="<?php echo $base_url; ?>/admin/index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Dashboard</a>
                <a href="<?php echo $base_url; ?>/admin/jobs/job.php" class="<?php echo ($current_page == 'job.php') ? 'active' : ''; ?>">Lowongan Kerja</a>
                <a href="<?php echo $base_url; ?>/admin/applications/application.php" class="<?php echo ($current_page == 'application.php') ? 'active' : ''; ?>">Pendaftaran</a>
                <a href="<?php echo $base_url; ?>/admin/departments/department.php" class="<?php echo ($current_page == 'department.php') ? 'active' : ''; ?>">Departemen</a>
                <a href="<?php echo $base_url; ?>/admin/requirements/requirement.php" class="<?php echo ($current_page == 'requirement.php') ? 'active' : ''; ?>">Persyaratan</a>
                <a href="<?php echo $base_url; ?>/admin/users/user.php" class="<?php echo ($current_page == 'user.php') ? 'active' : ''; ?>">User</a>

            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'candidate'): ?>
                <a href="<?php echo $base_url; ?>/index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Lowongan Tersedia</a>
                <a href="<?php echo $base_url; ?>/about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">Tentang Perusahaan</a>

            <?php else: ?>
                <a href="<?php echo $base_url; ?>/index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Lowongan Kerja</a>
                <a href="<?php echo $base_url; ?>/about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">Tentang Perusahaan</a>
            <?php endif; ?>

        </div>
    </div>

    <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            
            <span style="font-weight: 600; font-size: 16px;">
                <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['role']); ?>
            </span>

            <div class="profile-icon">
                <svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>

            <a href="<?php echo $base_url; ?>/auth/logout.php" style="margin-left: 10px; color: #333;" title="Logout">
                <svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </a>

        <?php else: ?>
            <a href="<?php echo $base_url; ?>/auth/login.php">Masuk</a>
            
            <a href="<?php echo $base_url; ?>/auth/register.php" class="btn-daftar">Daftar &rarr;</a>
        <?php endif; ?>
    </div>

</nav>