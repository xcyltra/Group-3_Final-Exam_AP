<?php
session_start();
require_once '../config/database.php';

$id = $_GET['id'];

// Ambil data job + requirement
$stmt = $conn->prepare("SELECT j.*, r.education, r.experience 
                        FROM jobs j 
                        JOIN requirements r ON j.requirement_id = r.requirement_id 
                        WHERE j.job_id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil departemen
$depts = $conn->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['update'])) {
    try {
        $conn->beginTransaction();
        
        // Update Requirements
        $sqlReq = "UPDATE requirements SET education=:edu, experience=:exp WHERE requirement_id=:rid";
        $conn->prepare($sqlReq)->execute([
            ':edu' => $_POST['education'],
            ':exp' => $_POST['experience'],
            ':rid' => $job['requirement_id']
        ]);

        // Update Jobs
        $sqlJob = "UPDATE jobs SET department_id=:dept, title=:title, description=:desc, job_type=:type, salary=:sal, quota=:qt, closing_date=:close WHERE job_id=:jid";
        $conn->prepare($sqlJob)->execute([
            ':dept' => $_POST['department_id'],
            ':title'=> $_POST['title'],
            ':desc' => $_POST['description'],
            ':type' => $_POST['job_type'],
            ':sal'  => $_POST['salary'],
            ':qt'   => $_POST['quota'],
            ':close'=> $_POST['closing_date'],
            ':jid'  => $id
        ]);

        $conn->commit();
        echo "<script>alert('Update Berhasil!'); window.location='index.php';</script>";
    } catch(Exception $e) {
        $conn->rollBack();
        echo "<script>alert('Error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Lowongan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h2>Edit Lowongan: <?php echo htmlspecialchars($job['title']); ?></h2>
            <a href="index.php" class="btn-add">Kembali</a>
        </div>
        <div class="form-admin-card">
            <form method="POST">
                <div class="form-row">
                    <div>
                        <label class="form-label">Judul</label>
                        <input type="text" name="title" class="form-input" value="<?php echo htmlspecialchars($job['title']); ?>">
                    </div>
                    <div>
                        <label class="form-label">Gaji</label>
                        <input type="number" name="salary" class="form-input" value="<?php echo $job['salary']; ?>">
                    </div>
                </div>
                <!-- Tambahkan input lain sesuai create.php namun isi value="..." -->
                
                <div class="section-title">Persyaratan</div>
                <div class="form-row">
                    <div>
                        <label class="form-label">Pendidikan</label>
                        <input type="text" name="education" class="form-input" value="<?php echo htmlspecialchars($job['education']); ?>">
                    </div>
                    <div>
                        <label class="form-label">Pengalaman</label>
                        <input type="text" name="experience" class="form-input" value="<?php echo htmlspecialchars($job['experience']); ?>">
                    </div>
                </div>
                
                <!-- Input Hidden untuk field yang wajib tapi tidak diubah drastis di contoh ini -->
                <input type="hidden" name="department_id" value="<?php echo $job['department_id']; ?>">
                <input type="hidden" name="job_type" value="<?php echo $job['job_type']; ?>">
                <input type="hidden" name="quota" value="<?php echo $job['quota']; ?>">
                <input type="hidden" name="closing_date" value="<?php echo $job['closing_date']; ?>">
                <input type="hidden" name="description" value="<?php echo htmlspecialchars($job['description']); ?>">

                <button type="submit" name="update" class="btn-daftar" style="margin-top:20px; width:100%;">Update Data</button>
            </form>
        </div>
    </div>
</body>
</html>