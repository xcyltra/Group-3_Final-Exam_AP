<?php
session_start();
require_once '../../config/database.php';

// 1. Cek Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'interviewer') {
    header("Location: ../../auth/login.php");
    exit;
}

// 2. Logika Filter & Search
$search_text = isset($_GET['search']) ? $_GET['search'] : '';
$params = [];

$where_clauses = ["is_active = 1"];

if (!empty($search_text)) {
    $where_clauses[] = "u.full_name LIKE :search";
    $params[':search'] = "%$search_text%";
}

$sql_where = "WHERE " . implode(" AND ", $where_clauses);

// 3. Query Data (Applications JOIN Users, Jobs, Scoring)
try {
    $query = "SELECT 
                a.application_id, 
                u.full_name, 
                j.title,
                a.file_cv,
                a.file_identity_card,
                a.file_degree_certificate,
                a.file_portfolio,
                COALESCE(s.interview_score, '-') as interview_score,
                COALESCE(s.technical_score, '-') as technical_score,
                COALESCE(s.scoring_id, 0) as scoring_id
              FROM applications a
              JOIN user u ON a.user_id = u.user_id
              JOIN jobs j ON a.job_id = j.job_id
              LEFT JOIN scoring s ON a.application_id = s.application_id
              $sql_where
              ORDER BY a.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penilaian - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .file-viewer-btn {
            background-color: #4CAF50;
            color: white;
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 4px;
        }
        .file-viewer-btn:hover {
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
        }
        .pdf-modal-content {
            background-color: #fff;
            margin: 20px auto;
            padding: 0;
            width: 90%;
            height: 90vh;
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
        .pdf-close {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
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
        
        <div class="page-header">
            <h2>Kelola Penilaian Aplikasi</h2>
        </div>

        <form action="" method="GET" class="filter-bar">
            <input type="text" name="search" class="search-input" placeholder="Cari Nama Pelamar" value="<?php echo htmlspecialchars($search_text); ?>">
            <button type="submit" class="btn-search">Search</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Aplikasi</th>
                        <th style=" text-align: center;">Nama Pelamar</th>
                        <th style=" text-align: center;">Posisi</th>
                        <th style=" text-align: center;">File</th>
                        <th style=" text-align: center;">Interview Score</th>
                        <th style=" text-align: center;">Technical Score</th>
                        <th style=" text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($applications) > 0): ?>
                        <?php foreach($applications as $row): ?>
                        <tr>
                            <td><strong>#<?php echo $row['application_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>
                                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                    <?php 
                                        foreach($file_map as $file_col => $file_label) {
                                            if (!empty($row[$file_col])) {
                                                $file_path = '../../assets/uploads/applications/' . $row[$file_col];
                                                echo '<button type="button" class="file-viewer-btn" onclick="openPdfModal(\'' . htmlspecialchars(addslashes($file_path)) . '\', \'' . htmlspecialchars($file_label) . '\')">'.$file_label.'</button>';
                                            }
                                        }
                                    ?>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <strong><?php echo $row['interview_score']; ?></strong>
                            </td>
                            <td style="text-align: center;">
                                <strong><?php echo $row['technical_score']; ?></strong>
                            </td>
                            <td>
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="scoring_edit.php?id=<?php echo $row['application_id']; ?>" class="btn-edit" style="background-color: #666;">Input Nilai</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:30px;">Tidak ada data aplikasi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PDF Viewer Modal -->
    <div id="pdfModal" class="pdf-modal">
        <div class="pdf-modal-content">
            <div class="pdf-modal-header">
                <h3 id="pdfTitle">View PDF</h3>
                <div>
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
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
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
