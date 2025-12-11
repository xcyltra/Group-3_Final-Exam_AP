<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'interviewer')) {
    // Jika bukan admin DAN bukan interviewer, tendang ke login
    header("Location: ../auth/login.php");
    exit;
}

// Cek ID di URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id = $_GET['id'];

// Ambil Data Job Lama
$stmt = $conn->prepare("SELECT * FROM jobs WHERE job_id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("Data tidak ditemukan.");
}

// Ambil Data Departemen & Requirements
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$requirements = $conn->query("SELECT * FROM requirements")->fetchAll(PDO::FETCH_ASSOC);

// PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "UPDATE jobs SET 
                title = :title, department_id = :dept_id, job_type = :type, 
                quota = :quota, salary = :salary, closing_date = :closing, 
                description = :desc, requirement_id = :req_id
                WHERE job_id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title'    => $_POST['title'],
            ':dept_id'  => $_POST['department_id'],
            ':type'     => $_POST['job_type'],
            ':quota'    => $_POST['quota'],
            ':salary'   => $_POST['salary'],
            ':closing'  => $_POST['closing_date'],
            ':desc'     => $_POST['description'],
            ':req_id'   => $_POST['requirement_id'],
            ':id'       => $id
        ]);

        header("Location: job.php?msg=updated");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal update: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Lowongan - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="page-header header-center">
            <h2>Edit Lowongan</h2>
        </div>

        <div class="form-card">
            <form action="" method="POST">
                
                <div class="form-group">
                    <label class="form-label">Judul Posisi</label>
                    <input type="text" name="title" class="form-input" value="<?php echo htmlspecialchars($job['title']); ?>" required>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Departemen</label>
                        <select name="department_id" class="form-input" required>
                            <?php foreach($departments as $d): ?>
                                <option value="<?php echo $d['department_id']; ?>" <?php echo ($job['department_id'] == $d['department_id']) ? 'selected' : ''; ?>>
                                    <?php echo $d['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tipe Pekerjaan</label>
                        <select name="job_type" class="form-input" required>
                            <option value="On-Site" <?php echo ($job['job_type'] == 'On-Site') ? 'selected' : ''; ?>>On-Site</option>
                            <option value="Remote" <?php echo ($job['job_type'] == 'Remote') ? 'selected' : ''; ?>>Remote</option>
                            <option value="Hybrid" <?php echo ($job['job_type'] == 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Kuota</label>
                        <input type="number" name="quota" class="form-input" value="<?php echo $job['quota']; ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Gaji</label>
                        <input type="number" name="salary" class="form-input" value="<?php echo $job['salary']; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Paket Persyaratan</label>
                    <select name="requirement_id" class="form-input" required>
                        <?php foreach($requirements as $r): ?>
                            <option value="<?php echo $r['requirement_id']; ?>" <?php echo ($job['requirement_id'] == $r['requirement_id']) ? 'selected' : ''; ?>>
                                ID: <?php echo $r['requirement_id']; ?> - Min Pend: <?php echo $r['education']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Penutupan</label>
                    <input type="date" name="closing_date" class="form-input" value="<?php echo $job['closing_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-input"><?php echo htmlspecialchars($job['description']); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                    <a href="job.php" class="btn-cancel">Batal</a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>