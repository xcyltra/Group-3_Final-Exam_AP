<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php"); exit;
}

// Daftar Kolom Dokumen (Untuk meloop form agar tidak panjang kodenya)
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

// PROSES SIMPAN
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Buat query dinamis
        $cols = "education, experience, " . implode(", ", array_keys($doc_fields));
        $vals = ":education, :experience, " . implode(", ", array_map(function($k){ return ":$k"; }, array_keys($doc_fields)));
        
        $sql = "INSERT INTO requirements ($cols) VALUES ($vals)";
        $stmt = $conn->prepare($sql);

        // Bind Parameter
        $params = [
            ':education' => $_POST['education'],
            ':experience' => $_POST['experience']
        ];
        foreach ($doc_fields as $key => $label) {
            $params[":$key"] = $_POST[$key];
        }

        $stmt->execute($params);
        header("Location: index.php?msg=created");
        exit;

    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Persyaratan - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="page-header header-center">
            <h2>Tambah Paket Persyaratan</h2>
        </div>

        <div class="form-card">
            <?php if(isset($error)) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>

            <form method="POST">
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Minimal Pendidikan</label>
                        <input type="text" name="education" class="form-input" placeholder="Contoh: S1 Teknik Informatika / D3" required>
                    </div>
                    <div>
                        <label class="form-label">Pengalaman Kerja</label>
                        <input type="text" name="experience" class="form-input" placeholder="Contoh: Min. 2 Tahun / Fresh Graduate" required>
                    </div>
                </div>

                <div class="divider-card" style="margin: 30px 0;"></div>
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; color: #333;">Kelengkapan Dokumen</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <?php foreach($doc_fields as $field => $label): ?>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label class="form-label"><?php echo $label; ?></label>
                        <select name="<?php echo $field; ?>" class="form-input">
                            <option value="None">Tidak Perlu (None)</option>
                            <option value="Required" <?php if($field == 'status_cv' || $field == 'status_identity_card') echo 'selected'; ?>>Wajib (Required)</option>
                            <option value="Optional">Opsional</option>
                        </select>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Simpan Persyaratan</button>
                    <a href="requirement.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>