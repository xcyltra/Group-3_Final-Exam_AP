<?php
session_start();
require_once '../../config/database.php';

// Cek Admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'interviewer')) {
    // Jika bukan admin DAN bukan interviewer, tendang ke login
    header("Location: ../auth/login.php");
    exit;
}

// Ambil Data Departemen untuk Dropdown
$dept_stmt = $conn->query("SELECT * FROM departments ORDER BY name ASC");
$departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil Data Requirements untuk Dropdown (Opsional, sesuaikan logika Anda)
// Disini saya ambil simple ID dan Pendidikan agar bisa dipilih
$req_stmt = $conn->query("SELECT * FROM requirements");
$requirements = $req_stmt->fetchAll(PDO::FETCH_ASSOC);

// PROSES SIMPAN DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $sql = "INSERT INTO jobs (title, department_id, job_type, quota, salary, closing_date, description, requirement_id) 
                VALUES (:title, :dept_id, :type, :quota, :salary, :closing, :desc, :req_id)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title'    => $_POST['title'],
            ':dept_id'  => $_POST['department_id'],
            ':type'     => $_POST['job_type'],
            ':quota'    => $_POST['quota'],
            ':salary'   => $_POST['salary'],
            ':closing'  => $_POST['closing_date'],
            ':desc'     => $_POST['description'],
            ':req_id'   => $_POST['requirement_id'] // Pastikan requirement dipilih
        ]);

        // Redirect sukses
        header("Location: job.php?msg=created");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Lowongan - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="page-header header-center">
            <h2>Buat Lowongan Baru</h2>
        </div>

        <div class="form-card">
            <?php if(isset($error)): ?>
                <p style="color: red; margin-bottom: 15px;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form action="" method="POST">
                
                <div class="form-group">
                    <label class="form-label">Judul Posisi</label>
                    <input type="text" name="title" class="form-input" placeholder="Contoh: Senior Backend Developer" required>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Departemen</label>
                        <select name="department_id" class="form-input" required>
                            <option value="">Pilih Departemen</option>
                            <?php foreach($departments as $d): ?>
                                <option value="<?php echo $d['department_id']; ?>"><?php echo $d['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tipe Pekerjaan</label>
                        <select name="job_type" class="form-input" required>
                            <option value="On-Site">On-Site</option>
                            <option value="Remote">Remote</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Kuota Penerimaan</label>
                        <input type="number" name="quota" class="form-input" placeholder="0" required>
                    </div>
                    <div>
                        <label class="form-label">Gaji (IDR)</label>
                        <input type="number" name="salary" class="form-input" placeholder="Contoh: 5000000" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Paket Persyaratan (Requirement ID)</label>
                    <select name="requirement_id" class="form-input" required>
                        <?php foreach($requirements as $r): ?>
                            <option value="<?php echo $r['requirement_id']; ?>">
                                ID: <?php echo $r['requirement_id']; ?> - Min Pend: <?php echo $r['education']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #666;">*Pilih paket syarat yang sudah dibuat di menu Persyaratan</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Penutupan</label>
                    <input type="date" name="closing_date" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Pekerjaan</label>
                    <textarea name="description" class="form-input" placeholder="Tuliskan detail pekerjaan..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Simpan Lowongan</button>
                    <a href="job.php" class="btn-cancel">Batal</a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>