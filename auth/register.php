<?php
require_once '../config/database.php';

$message = "";

if (isset($_POST['submit'])) {
    
    // ... kode validasi & insert ...
                if ($stmt->execute($data)) {
                    echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location='login.php';</script>";
                    exit;
                }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Akun - Miso Corporation</title>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div style="text-align: left; margin-bottom: 15px;">
                <a href="../index.php" style="text-decoration:none; color:black; font-size: 24px;">&larr;</a>
            </div>
            <h2>Daftar Akun<br>Miso Corporation</h2>
        </div>

        <form action="" method="POST">
             <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="full_name" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="off">
            </div>
             <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" required autocomplete="off">
            </div>
             <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="text" name="phone_number" required autocomplete="off">
            </div>
             <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
             <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" name="submit" class="btn-submit">Submit</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="login.php">Masuk disini</a>
        </div>
    </div>
</body>
</html>