<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'interviewer') {
    header("Location: ../../auth/login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: scoring.php");
    exit;
}

// Ambil data aplikasi dan pelamar
try {
    $stmt = $conn->prepare("
        SELECT 
            a.application_id,
            a.file_cv,
            a.file_identity_card,
            a.file_degree_certificate,
            a.file_portfolio,
            u.full_name,
            j.title,
            COALESCE(s.scoring_id, 0) as scoring_id,
            COALESCE(s.interview_score, '') as interview_score,
            COALESCE(s.technical_score, '') as technical_score
        FROM applications a
        JOIN user u ON a.user_id = u.user_id
        JOIN jobs j ON a.job_id = j.job_id
        LEFT JOIN scoring s ON a.application_id = s.application_id
        WHERE a.application_id = ?
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        header("Location: scoring.php");
        exit;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Helper mapping file
$file_map = [
    'file_cv' => 'CV',
    'file_identity_card' => 'KTP',
    'file_degree_certificate' => 'Ijazah',
    'file_portfolio' => 'Portofolio'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $interview_score = isset($_POST['interview_score']) ? intval($_POST['interview_score']) : 0;
    $technical_score = isset($_POST['technical_score']) ? intval($_POST['technical_score']) : 0;

    // Validasi
    $error = '';
    if ($interview_score < 0 || $interview_score > 100) {
        $error = "Interview Score harus antara 0-100.";
    }
    if ($technical_score < 0 || $technical_score > 100) {
        $error = "Technical Score harus antara 0-100.";
    }

    if (!$error) {
        try {
            if ($data['scoring_id'] > 0) {
                // Update existing scoring
                $sql = "UPDATE scoring SET interview_score = ?, technical_score = ? WHERE scoring_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$interview_score, $technical_score, $data['scoring_id']]);
            } else {
                // Insert new scoring
                $sql = "INSERT INTO scoring (application_id, interview_score, technical_score) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id, $interview_score, $technical_score]);
            }
            header("Location: scoring.php?msg=saved");
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai Penilaian - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .info-box {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
        }
        .info-box p {
            margin: 5px 0;
            font-size: 14px;
        }
        .info-box strong {
            color: #333;
        }
        .file-section {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .file-section h4 {
            margin-top: 0;
            color: #333;
        }
        .file-list {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .file-btn {
            background-color: #4CAF50;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .file-btn:hover {
            background-color: #45a049;
        }
        .pdf-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            align-items: center;
            justify-content: center;
        }
        .pdf-modal-content {
            background-color: #fff;
            padding: 0;
            width: 90%;
            height: 85vh;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
        }
        .pdf-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            background: #f5f5f5;
        }
        .pdf-modal-header h3 {
            margin: 0;
            font-size: 18px;
        }
        .pdf-actions {
            display: flex;
            gap: 10px;
        }
        .pdf-close {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .pdf-close:hover {
            background-color: #da190b;
        }
        .pdf-download {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .pdf-download:hover {
            background-color: #0b7dda;
        }
        .pdf-viewer {
            flex: 1;
            overflow: auto;
        }
        .pdf-viewer iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>

<?php include '../../includes/header.php'; ?>

<div class="dashboard-container">
    <div class="page-header header-center"><h2>Input Nilai Penilaian</h2></div>

    <div class="form-card">
        <div class="info-box">
            <p><strong>ID Aplikasi:</strong> #<?php echo $data['application_id']; ?></p>
            <p><strong>Nama Pelamar:</strong> <?php echo htmlspecialchars($data['full_name']); ?></p>
            <p><strong>Posisi yang Dilamar:</strong> <?php echo htmlspecialchars($data['title']); ?></p>
        </div>

        <div class="file-section">
            <h4>📄 File Dokumen Pelamar</h4>
            <div class="file-list">
                <?php 
                    $found_files = false;
                    foreach ($file_map as $field => $label) {
                        if (!empty($data[$field])) {
                            $file_path = '../../assets/uploads/applications/' . $data[$field];
                            echo '<button type="button" class="file-btn" onclick="openPdfModal(\'' . htmlspecialchars(addslashes($file_path)) . '\', \'' . htmlspecialchars($label) . '\')">' . $label . '</button>';
                            $found_files = true;
                        }
                    }
                    if (!$found_files) {
                        echo '<p style="color: #999; font-size: 12px;">Tidak ada file dokumen</p>';
                    }
                ?>
            </div>
        </div>

        <?php if (isset($error) && !empty($error)): ?>
            <p style="color:red; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Interview Score (0-100)</label>
                <input type="number" name="interview_score" class="form-input" 
                       min="0" max="100" value="<?php echo $data['interview_score']; ?>" 
                       placeholder="Contoh: 85" required>
            </div>

            <div class="form-group">
                <label class="form-label">Technical Score (0-100)</label>
                <input type="number" name="technical_score" class="form-input" 
                       min="0" max="100" value="<?php echo $data['technical_score']; ?>" 
                       placeholder="Contoh: 75" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">Simpan Nilai</button>
                <a href="scoring.php" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
</div>

<!-- PDF Viewer Modal -->
<div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">
        <div class="pdf-modal-header">
            <h3 id="pdfTitle">View PDF</h3>
            <div class="pdf-actions">
                <a id="pdfDownloadLink" href="#" class="pdf-download" download>Download</a>
                <button class="pdf-close" onclick="closePdfModal()">Tutup</button>
            </div>
        </div>
        <div class="pdf-viewer">
            <iframe id="pdfViewer" src="" type="application/pdf"></iframe>
        </div>
    </div>
</div>

<script>
    function openPdfModal(filePath, fileLabel) {
        const modal = document.getElementById('pdfModal');
        const iframe = document.getElementById('pdfViewer');
        const title = document.getElementById('pdfTitle');
        const downloadLink = document.getElementById('pdfDownloadLink');

        title.textContent = 'View ' + fileLabel;
        iframe.src = filePath;
        downloadLink.href = filePath;
        downloadLink.download = fileLabel + '.pdf';

        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('show'), 10);
    }

    function closePdfModal() {
        const modal = document.getElementById('pdfModal');
        modal.style.display = 'none';
        setTimeout(() => {
            document.getElementById('pdfViewer').src = '';
        }, 300);
    }

    window.onclick = function(e) {
        const modal = document.getElementById('pdfModal');
        if(e.target == modal) closePdfModal();
    }
</script>

</body>
</html>
