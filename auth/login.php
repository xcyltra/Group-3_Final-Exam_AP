<?php
session_start();
require_once '../config/database.php';

$message = "";

if (isset($_POST['login'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            if ($user['role'] == 'admin') {
                header("Location: ../admin/index.php");
            } elseif ($user['role'] == 'interviewer') {
                header("Location: ../interviewer/index.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $message = "<script>alert('Username atau Password Salah!');</script>";
        }
    } catch (PDOException $e) {
        $message = "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Miso Corporation</title>
    
    <!-- Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php echo $message; ?>

    <!-- WRAPPER LOGIN (Pengganti body flex) -->
    <div class="login-wrapper">
        
        <div class="login-container">
            
            <!-- KARTU LOGIN -->
            <div class="login-card">
                
                <!-- Header Kartu -->
                <div class="login-header-card">
                    <!-- Ikon Panah Kembali -->
                    <a href="../index.php" class="back-icon">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    
                    <h2>Masuk ke Akun<br>Miso Corporation</h2>
                </div>

                <!-- FORM LOGIN -->
                <form action="" method="POST">
                    
                    <div class="form-group-login">
                        <label>Username</label>
                        <input type="text" name="username" required autocomplete="off">
                    </div>

                    <div class="form-group-login">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <button type="submit" name="login" class="btn-submit-login">Submit</button>
                </form>

            </div> <!-- End Login Card -->

            <!-- FOOTER LOGIN -->
            <div class="login-footer">
                Belum punya akun? <a href="register.php">Daftar disini</a>
            </div>

        </div> <!-- End Login Container -->

    </div> <!-- End Login Wrapper -->

</body>
</html>