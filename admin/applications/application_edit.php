<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php"); exit;
}

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: application.php"); exit; }

// Ambil Data Aplikasi Saat Ini
$stmt = $conn->prepare("SELECT * FROM applications WHERE application_id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch();

// Ambil Data Pendukung
$users = $conn->query("SELECT user_id, full_name FROM user WHERE role='candidate'")->fetchAll();
$jobs = $conn->query("SELECT job_id, title FROM jobs")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "UPDATE applications SET user_id = ?, job_id = ? WHERE application_id = ?";
    $conn->prepare($sql)->execute([$_POST['user_id'], $_POST['job_id'], $id]);
    header("Location: application.php?msg=updated");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Pendaftar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="dashboard-container">
        <div class="page-header header-center"><h2>Edit Data Pendaftar</h2></div>
        
        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Kandidat</label>
                    <select name="user_id" class="form-input" required>
                        <?php foreach($users as $u): ?>
                            <option value="<?php echo $u['user_id']; ?>" <?php echo ($app['user_id'] == $u['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Lowongan</label>
                    <select name="job_id" class="form-input" required>
                        <?php foreach($jobs as $j): ?>
                            <option value="<?php echo $j['job_id']; ?>" <?php echo ($app['job_id'] == $j['job_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($j['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Update Data</button>
                    <a href="application.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>