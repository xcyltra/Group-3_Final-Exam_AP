<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$base_url = "/recruitment"; 

// Deteksi nama file saat ini (index.php atau about.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    <!-- LOGO -->
    <div class="logo">
        <a href="<?php echo $base_url; ?>/index.php" style="text-decoration:none; color:inherit;">
            MISO CORP.
        </a>
    </div>

    <!-- LINK TENGAH -->
    <div class="nav-links">
        <!-- Logika Active: Jika halaman index.php, tambahkan class 'active' -->
        <a href="<?php echo $base_url; ?>/index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Lowongan Kerja</a>
        
        <!-- Logika Active: Jika halaman about.php, tambahkan class 'active' -->
        <a href="<?php echo $base_url; ?>/about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">Tentang Perusahaan</a>
    </div>

    <!-- LINK KANAN -->
    <div class="auth-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="#" style="font-weight: bold;">Halo, <?php echo htmlspecialchars($_SESSION['full_name']); ?></a>
            
            <?php if($_SESSION['role'] == 'candidate'): ?>
                <a href="<?php echo $base_url; ?>/profile/index.php">Profil</a>
            <?php elseif($_SESSION['role'] == 'admin'): ?>
                <a href="<?php echo $base_url; ?>/admin/index.php">Dashboard</a>
            <?php elseif($_SESSION['role'] == 'interviewer'): ?>
                <a href="<?php echo $base_url; ?>/interviewer/index.php">Dashboard</a>
            <?php endif; ?>
            
            <a href="<?php echo $base_url; ?>/auth/logout.php" style="color: #d9534f;">Logout</a>
        <?php else: ?>
            <a href="<?php echo $base_url; ?>/auth/login.php">Masuk</a>
            <a href="<?php echo $base_url; ?>/auth/register.php" class="btn-daftar">Daftar &rarr;</a>
        <?php endif; ?>
    </div>
</nav>