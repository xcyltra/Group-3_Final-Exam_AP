<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'interviewer')) {
    // Jika bukan admin DAN bukan interviewer, tendang ke login
    header("Location: ../auth/login.php");
    exit;
}

// Ambil List Kandidat (User dengan role candidate)
$users = $conn->query("SELECT user_id, full_name FROM user WHERE role='candidate' ORDER BY full_name ASC")->fetchAll();

// Ambil List Lowongan
$jobs = $conn->query("SELECT job_id, title FROM jobs ORDER BY title ASC")->fetchAll();

// Define upload directory and allowed file types
$upload_dir = '../../assets/uploads/applications/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$file_fields = [
    'file_cv' => 'CV',
    'file_identity_card' => 'KTP',
    'file_degree_certificate' => 'Ijazah',
    'file_portfolio' => 'Portofolio'
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $job_id = $_POST['job_id'] ?? null;

    try {
        // Cek apakah user sudah melamar job ini sebelumnya
        $check = $conn->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND job_id = ?");
        $check->execute([$user_id, $job_id]);
        
        if ($check->fetchColumn() > 0) {
            $error = "Kandidat ini sudah terdaftar di lowongan tersebut.";
        } else {
            // Insert aplikasi terlebih dahulu
            $sql = "INSERT INTO applications (user_id, job_id, created_at) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $job_id]);
            $app_id = $conn->lastInsertId();

            // Handle file uploads
            $file_updates = [];
            foreach ($file_fields as $field => $label) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES[$field]['tmp_name'];
                    $file_name = basename($_FILES[$field]['name']);
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Only allow PDF
                    if ($file_ext !== 'pdf') {
                        $error = "Format file harus PDF. Silakan upload ulang.";
                        break;
                    }
                    
                    // Generate unique filename
                    $new_filename = 'app_' . $app_id . '_' . $field . '_' . time() . '.pdf';
                    $file_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file_tmp, $file_path)) {
                        $file_updates[$field] = $new_filename;
                    } else {
                        $error = "Gagal upload file $label.";
                        break;
                    }
                }
            }

            if (!$error && !empty($file_updates)) {
                // Update dengan file yang sudah diupload
                $update_fields = [];
                $params = [];
                foreach ($file_updates as $field => $filename) {
                    $update_fields[] = "$field = ?";
                    $params[] = $filename;
                }
                $params[] = $app_id;
                
                $sql_update = "UPDATE applications SET " . implode(", ", $update_fields) . " WHERE application_id = ?";
                $conn->prepare($sql_update)->execute($params);
            }

            if (!$error) {
                header("Location: application.php?msg=created");
                exit;
            }
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
            <?php if(isset($error)) echo "<p style='color:red; margin-bottom: 15px;'>" . htmlspecialchars($error) . "</p>"; ?>
            
            <form method="POST" enctype="multipart/form-data">
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

                <hr style="margin: 25px 0; border: none; border-top: 1px solid #ddd;">
                <h3 style="margin-bottom: 15px;">Upload File Dokumen</h3>

                <?php foreach ($file_fields as $field => $label): ?>
                <div class="form-group">
                    <label class="form-label"><?php echo $label; ?> (PDF)</label>
                    <input type="file" name="<?php echo $field; ?>" class="form-input" accept=".pdf">
                    <p style="font-size: 12px; color: #999; margin-top: 5px;">Format: PDF | Opsional</p>
                </div>
                <?php endforeach; ?>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Simpan Data</button>
                    <a href="application.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>