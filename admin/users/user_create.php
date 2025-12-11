<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $passHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO user (full_name, username, email, phone_number, role, password) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $_POST['full_name'], 
            $_POST['username'], 
            $_POST['email'], 
            $_POST['phone'], 
            $_POST['role'], 
            $passHash
        ]);

        header("Location: index.php?msg=created");
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah User - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="page-header header-center">
            <h2>Tambah Pengguna Baru</h2>
        </div>

        <div class="form-card">
            <?php if(isset($error)) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-input" required>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" class="form-input" required>
                            <option value="candidate">Candidate</option>
                            <option value="interviewer">Interviewer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Simpan User</button>
                    <a href="user.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>