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
            $new_filename = 'app_' . $id . '_' . $field . '_' . time() . '.pdf';
            $file_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                $file_updates[$field] = $new_filename;
            } else {
                $error = "Gagal upload file $label.";
                break;
            }
        }
    }

    if (!$error) {
        try {
            // Build update query
            $update_fields = ['user_id = ?', 'job_id = ?'];
            $params = [$user_id, $job_id];
            
            foreach ($file_updates as $field => $filename) {
                $update_fields[] = "$field = ?";
                $params[] = $filename;
            }
            
            $params[] = $id;
            $sql = "UPDATE applications SET " . implode(", ", $update_fields) . " WHERE application_id = ?";
            $conn->prepare($sql)->execute($params);
            
            header("Location: application.php?msg=updated");
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
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
            <?php if (!empty($error)): ?>
                <p style="color: red; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
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

                <hr style="margin: 25px 0; border: none; border-top: 1px solid #ddd;">
                <h3 style="margin-bottom: 15px;">Upload File Dokumen</h3>

                <?php foreach ($file_fields as $field => $label): ?>
                <div class="form-group">
                    <label class="form-label"><?php echo $label; ?> (PDF)</label>
                    <?php if (!empty($app[$field])): ?>
                        <p style="font-size: 12px; color: #666; margin-bottom: 8px;">
                            📄 File saat ini: <strong><?php echo htmlspecialchars($app[$field]); ?></strong>
                        </p>
                    <?php endif; ?>
                    <input type="file" name="<?php echo $field; ?>" class="form-input" accept=".pdf" placeholder="Pilih file PDF">
                    <p style="font-size: 12px; color: #999; margin-top: 5px;">Format: PDF | Biarkan kosong jika tidak ingin mengubah</p>
                </div>
                <?php endforeach; ?>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Update Data</button>
                    <a href="application.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>