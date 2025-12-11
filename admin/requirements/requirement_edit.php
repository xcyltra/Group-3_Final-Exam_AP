<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'interviewer')) {
    // Jika bukan admin DAN bukan interviewer, tendang ke login
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

// Ambil Data Lama
$stmt = $conn->prepare("SELECT * FROM requirements WHERE requirement_id = ?");
$stmt->execute([$id]);
$req = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$req) die("Data tidak ditemukan.");

// Daftar Dokumen
$doc_fields = [
    'status_cv' => 'CV / Curriculum Vitae',
    'status_identity_card' => 'KTP / Identitas',
    'status_degree_certificate' => 'Ijazah Terakhir',
    'status_family_register' => 'Kartu Keluarga (KK)',
    'status_police_certificate' => 'SKCK',
    'status_passport_photo' => 'Pas Foto',
    'status_resume' => 'Resume',
    'status_training_certificate' => 'Sertifikat Pelatihan',
    'status_portfolio' => 'Portofolio',
    'status_health_certificate' => 'Surat Kesehatan'
];

// PROSES UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $set_parts = ["education = :education", "experience = :experience"];
        foreach (array_keys($doc_fields) as $key) {
            $set_parts[] = "$key = :$key";
        }
        $sql = "UPDATE requirements SET " . implode(", ", $set_parts) . " WHERE requirement_id = :id";
        
        $params = [':education' => $_POST['education'], ':experience' => $_POST['experience'], ':id' => $id];
        foreach ($doc_fields as $key => $label) {
            $params[":$key"] = $_POST[$key];
        }

        $conn->prepare($sql)->execute($params);
        header("Location: index.php?msg=updated");
        exit;

    } catch (PDOException $e) {
        $error = "Gagal update: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Persyaratan - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="page-header header-center">
            <h2>Edit Paket Persyaratan #<?php echo $id; ?></h2>
        </div>

        <div class="form-card">
            <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

            <form method="POST">
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Minimal Pendidikan</label>
                        <input type="text" name="education" class="form-input" value="<?php echo htmlspecialchars($req['education']); ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Pengalaman Kerja</label>
                        <input type="text" name="experience" class="form-input" value="<?php echo htmlspecialchars($req['experience']); ?>" required>
                    </div>
                </div>

                <div class="divider-card" style="margin: 30px 0;"></div>
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; color: #333;">Kelengkapan Dokumen</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <?php foreach($doc_fields as $field => $label): ?>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label class="form-label"><?php echo $label; ?></label>
                        <select name="<?php echo $field; ?>" class="form-input">
                            <option value="None" <?php echo ($req[$field] == 'None') ? 'selected' : ''; ?>>Tidak Perlu</option>
                            <option value="Required" <?php echo ($req[$field] == 'Required') ? 'selected' : ''; ?>>Wajib</option>
                            <option value="Optional" <?php echo ($req[$field] == 'Optional') ? 'selected' : ''; ?>>Opsional</option>
                        </select>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                    <a href="requirement.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>