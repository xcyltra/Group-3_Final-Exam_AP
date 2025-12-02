<?php
session_start();
require_once '../config/database.php';

$message = "";

if (isset($_POST['login'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    try {
        // Cari user berdasarkan username
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Jika user ketemu DAN password cocok
        if ($user && password_verify($password, $user['password'])) {
            
            // Simpan data penting ke SESSION
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // Cek Role untuk mengarahkan ke folder yang benar
            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user['role'] == 'interviewer') {
                header("Location: ../interviewer/dashboard.php");
            } else {
                // Default candidate
                header("Location: ../candidate/dashboard.php");
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
    <title>Login - Miso Corporation</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php echo $message; ?>

    <div class="auth-container">
        <div class="auth-header">
            <div style="text-align: left; margin-bottom: 15px;">
                <a href="../index.php" style="text-decoration:none; color:black; font-size: 24px;">&larr;</a>
            </div>
            <h2>Masuk ke Akun<br>Miso Corporation</h2>
        </div>

        <form action="" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="off">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" name="login" class="btn-submit">Masuk</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="register.php">Daftar disini</a>
        </div>
    </div>
</body>
</html>