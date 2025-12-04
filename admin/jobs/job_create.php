<?php
session_start();
require_once '../config/database.php';

// Ambil data departemen untuk dropdown
$deptStmt = $conn->query("SELECT * FROM departments");
$departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['save'])) {
    try {
        $conn->beginTransaction();

        // 1. INSERT ke tabel REQUIREMENTS dulu
        $sqlReq = "INSERT INTO requirements (education, experience, status_cv, status_identity_card) 
                   VALUES (:edu, :exp, :cv, :ktp)";
        $stmtReq = $conn->prepare($sqlReq);
        $stmtReq->execute([
            ':edu' => $_POST['education'],
            ':exp' => $_POST['experience'],
            ':cv'  => 'Required', // Default Required
            ':ktp' => 'Required'  // Default Required
        ]);
        $reqId = $conn->lastInsertId(); // Ambil ID requirement yang baru dibuat

        // 2. INSERT ke tabel JOBS
        $sqlJob = "INSERT INTO jobs (department_id, title, description, requirement_id, job_type, salary, quota, closing_date) 
                   VALUES (:dept, :title, :desc, :req_id, :type, :salary, :quota, :close)";
        $stmtJob = $conn->prepare($sqlJob);
        $stmtJob->execute([
            ':dept' => $_POST['department_id'],
            ':title'=> $_POST['title'],
            ':desc' => $_POST['description'],
            ':req_id' => $reqId,
            ':type' => $_POST['job_type'],
            ':salary' => $_POST['salary'],
            ':quota' => $_POST['quota'],
            ':close' => $_POST['closing_date']
        ]);

        $conn->commit();
        echo "<script>alert('Lowongan Berhasil Ditambahkan!'); window.location='index.php';</script>";

    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('Gagal: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Lowongan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h2>Tambah Lowongan Baru</h2>
            <a href="index.php" class="btn-add">Kembali</a>
        </div>

        <div class="form-admin-card">
            <form method="POST">
                <div class="section-title">Detail Pekerjaan</div>
                <div class="form-row">
                    <div>
                        <label class="form-label">Judul Pekerjaan</label>
                        <input type="text" name="title" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Departemen</label>
                        <select name="department_id" class="form-select">
                            <?php foreach($departments as $d): ?>
                                <option value="<?php echo $d['department_id']; ?>"><?php echo $d['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label class="form-label">Tipe Pekerjaan</label>
                        <select name="job_type" class="form-select">
                            <option value="On-Site">On-Site</option>
                            <option value="Remote">Remote</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Gaji (Rupiah)</label>
                        <input type="number" name="salary" class="form-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label class="form-label">Kuota</label>
                        <input type="number" name="quota" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Tanggal Tutup</label>
                        <input type="date" name="closing_date" class="form-input" required>
                    </div>
                </div>

                <label class="form-label">Deskripsi Pekerjaan</label>
                <textarea name="description" class="form-textarea" required></textarea>

                <div class="section-title">Persyaratan (Requirements)</div>
                <div class="form-row">
                    <div>
                        <label class="form-label">Pendidikan (Misal: S1 Teknik)</label>
                        <input type="text" name="education" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Pengalaman (Misal: Min 1 Tahun)</label>
                        <input type="text" name="experience" class="form-input" required>
                    </div>
                </div>

                <button type="submit" name="save" class="btn-daftar" style="margin-top:20px; width:100%;">Simpan Lowongan</button>
            </form>
        </div>
    </div>
</body>
</html>