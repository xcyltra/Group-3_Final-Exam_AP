<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php"); exit;
}

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

// Ambil Data User
$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "UPDATE user SET full_name=?, username=?, email=?, phone_number=?, role=? WHERE user_id=?";
        $params = [$_POST['full_name'], $_POST['username'], $_POST['email'], $_POST['phone'], $_POST['role'], $id];

        // Update Password HANYA jika diisi
        if (!empty($_POST['password'])) {
            $sql = "UPDATE user SET full_name=?, username=?, email=?, phone_number=?, role=?, password=? WHERE user_id=?";
            $params = [
                $_POST['full_name'], $_POST['username'], $_POST['email'], 
                $_POST['phone'], $_POST['role'], 
                password_hash($_POST['password'], PASSWORD_DEFAULT), 
                $id
            ];
        }

        $conn->prepare($sql)->execute($params);
        header("Location: index.php?msg=updated");
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit User - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="page-header header-center">
            <h2>Edit Pengguna: <?php echo htmlspecialchars($user['full_name']); ?></h2>
        </div>

        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" class="form-input" required>
                            <option value="candidate" <?php echo ($user['role'] == 'candidate') ? 'selected' : ''; ?>>Candidate</option>
                            <option value="interviewer" <?php echo ($user['role'] == 'interviewer') ? 'selected' : ''; ?>>Interviewer</option>
                            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div>
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" class="form-input" placeholder="***">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                    <a href="user.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>