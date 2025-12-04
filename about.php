<?php
session_start();
// Tidak perlu database jika isinya statis, tapi biarkan jika nanti butuh
// require_once 'config/database.php'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Miso Corporation</title>
    
    <!-- Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- INCLUDE NAVBAR -->
    <?php include 'includes/header.php'; ?>

    <!-- ABOUT CONTAINER -->
    <div class="about-container">
        
        <!-- Header Judul -->
        <div class="about-header">
            <h1>Tentang Kami<br>Miso Corporation</h1>
        </div>

        <!-- Kartu Besar -->
        <div class="about-card">
            <p>
                Miso Corporation menyediakan Daftar Lowongan Pekerjaan untuk anda, Kami menghadirkan macam - Macam Pekerjaan Berdasarkan Kemampuan, Waktu Dan Juga Pengalaman Anda.
            </p>
        </div>

    </div>

    <!-- INCLUDE FOOTER -->
    <?php include 'includes/footer.php'; ?>

</body>
</html>