<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php"); exit;
}

// Ambil List Kandidat (User dengan role candidate)
$users = $conn->query("SELECT user_id, full_name FROM user WHERE role='candidate' ORDER BY full_name ASC")->fetchAll();

// Ambil List Lowongan
$jobs = $conn->query("SELECT job_id, title FROM jobs ORDER BY title ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Cek apakah user sudah melamar job ini sebelumnya
        $check = $conn->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND job_id = ?");
        $check->execute([$_POST['user_id'], $_POST['job_id']]);
        
        if ($check->fetchColumn() > 0) {
            $error = "Kandidat ini sudah terdaftar di lowongan tersebut.";
        } else {
            $sql = "INSERT INTO applications (user_id, job_id, created_at) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_POST['user_id'], $_POST['job_id']]);
            header("Location: application.php?msg=created");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Pendaftar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="dashboard-container">
        <div class="page-header header-center"><h2>Tambah Data Pendaftar</h2></div>
        
        <div class="form-card">
            <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Kandidat</label>
                    <select name="user_id" class="form-input" required>
                        <option value="">Pilih Kandidat</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Lowongan Pekerjaan</label>
                    <select name="job_id" class="form-input" required>
                        <option value="">Pilih Lowongan</option>
                        <?php foreach($jobs as $j): ?>
                            <option value="<?php echo $j['job_id']; ?>"><?php echo htmlspecialchars($j['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Simpan Data</button>
                    <a href="application.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>