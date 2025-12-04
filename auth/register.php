<?php
session_start();
// Akses ke database (naik satu folder)
require_once '../config/database.php';

$message = "";

// LOGIKA PHP: MENANGANI FORM SUBMIT
if (isset($_POST['submit'])) {
    // 1. Ambil & Bersihkan Input
    $full_name = htmlspecialchars($_POST['full_name']);
    $username  = htmlspecialchars($_POST['username']);
    $email     = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone     = htmlspecialchars($_POST['phone_number']);
    $pass      = $_POST['password'];
    $conf_pass = $_POST['confirm_password'];

    // 2. Validasi Password
    if ($pass !== $conf_pass) {
        $message = "<script>alert('Password dan Konfirmasi Password tidak sama!');</script>";
    } else {
        try {
            // 3. Cek apakah Username/Email sudah ada
            $stmtCheck = $conn->prepare("SELECT user_id FROM user WHERE username = :username OR email = :email");
            $stmtCheck->execute([':username' => $username, ':email' => $email]);

            if ($stmtCheck->rowCount() > 0) {
                $message = "<script>alert('Username atau Email sudah terdaftar! Silakan gunakan yang lain.');</script>";
            } else {
                // 4. Enkripsi Password & Insert Data
                $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
                $role = 'candidate'; // Default role pelamar

                $sql = "INSERT INTO user (full_name, username, email, phone_number, password, role) 
                        VALUES (:full_name, :username, :email, :phone, :password, :role)";
                
                $stmt = $conn->prepare($sql);
                $data = [
                    ':full_name' => $full_name,
                    ':username'  => $username,
                    ':email'     => $email,
                    ':phone'     => $phone,
                    ':password'  => $hashed_password,
                    ':role'      => $role
                ];

                if ($stmt->execute($data)) {
                    // Berhasil -> Redirect ke Login
                    echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location='login.php';</script>";
                    exit;
                }
            }
        } catch (PDOException $e) {
            $message = "<script>alert('Terjadi kesalahan sistem: " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Miso Corporation</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php echo $message; ?>

    <div class="auth-wrapper">
        
        <div class="auth-card">
            
            <div class="auth-header">
                <a href="../index.php" class="back-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                
                <h2>Daftar Akun<br>Miso Corporation</h2>
            </div>

            <form action="" method="POST">
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-control" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="phone_number" class="form-control" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" name="submit" class="btn-submit-auth">Submit</button>
            </form>

            <div class="auth-footer">
                Sudah punya akun? <a href="login.php">Masuk disini</a>
            </div>

        </div>
    </div>

</body>
</html>