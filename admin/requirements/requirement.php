<?php
session_start();
require_once '../../config/database.php';

// 1. Cek Akses Admin
// Cek Akses (Admin ATAU Interviewer boleh masuk)
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'interviewer')) {
    // Jika bukan admin DAN bukan interviewer, tendang ke login
    header("Location: ../auth/login.php");
    exit;
}

// 2. Logika Pencarian
$search = $_GET['search'] ?? '';
$params = [];

$sql = "SELECT * FROM requirements";

if ($search) {
    $sql .= " WHERE education LIKE ? OR experience LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$sql .= " ORDER BY requirement_id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper: Mapping Nama Kolom Database ke Nama Dokumen Asli
$doc_labels = [
    'status_cv' => 'CV',
    'status_identity_card' => 'KTP',
    'status_degree_certificate' => 'Ijazah',
    'status_family_register' => 'KK',
    'status_police_certificate' => 'SKCK',
    'status_passport_photo' => 'Pas Foto',
    'status_resume' => 'Resume',
    'status_training_certificate' => 'Sertifikat',
    'status_portfolio' => 'Portofolio',
    'status_health_certificate' => 'Surat Sehat'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Persyaratan - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        
        <div class="page-header">
            <h2>Kelola Persyaratan</h2>
            <a href="requirement_create.php" class="btn-tambah">+ Tambah</a>
        </div>

        <form action="" method="GET" class="filter-bar">
            <input type="text" name="search" class="search-input" placeholder="Cari Pendidikan / Pengalaman..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search">Search</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="5%" style= "text-align: center;">ID</th>
                        <th width="20%" style= "text-align: center;">Pendidikan</th>
                        <th width="20%" style= "text-align: center;">Pengalaman</th>
                        <th width="35%" style= "text-align: center;">Berkas Wajib</th>
                        <th width="20%" style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requirements) > 0): ?>
                        <?php foreach($requirements as $req): ?>
                        <tr>
                            <td>#<?php echo $req['requirement_id']; ?></td>
                            
                            <td><strong><?php echo htmlspecialchars($req['education']); ?></strong></td>
                            
                            <td><?php echo htmlspecialchars($req['experience']); ?></td>
                            
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <?php 
                                        $found = false;
                                        foreach($doc_labels as $col => $label) {
                                            // Cek jika statusnya 'Required'
                                            if ($req[$col] == 'Required') {
                                                echo '<span style="background: #e0e0e0; padding: 2px 8px; border-radius: 4px; font-size: 11px; border: 1px solid #ccc;">'.$label.'</span>';
                                                $found = true;
                                            }
                                        }
                                        if (!$found) {
                                            echo '<span style="color: #888; font-style: italic; font-size: 12px;">Tidak ada berkas wajib</span>';
                                        }
                                    ?>
                                </div>
                            </td>
                            
                            <td>
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="requirement_edit.php?id=<?php echo $req['requirement_id']; ?>" class="btn-edit">Edit</a>
                                    
                                    <a href="javascript:void(0);" 
                                       class="btn-hapus" 
                                       onclick="openDeleteModal('requirement_delete.php?id=<?php echo $req['requirement_id']; ?>')">
                                       Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px;">
                                Tidak ada data persyaratan ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon">⚠️</div>
            <h3>Konfirmasi Hapus</h3>
            <p>Yakin ingin menghapus paket persyaratan ini?</p>
            <div class="modal-actions">
                <button class="btn-close-modal" onclick="closeDeleteModal()">Batal</button>
                <a href="#" id="confirmDeleteBtn" class="btn-confirm-delete">Ya, Hapus</a>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(url) {
            document.getElementById('confirmDeleteBtn').href = url;
            document.getElementById('deleteModal').style.display = 'flex';
            setTimeout(() => document.getElementById('deleteModal').classList.add('show'), 10);
        }
        function closeDeleteModal() {
            let m = document.getElementById('deleteModal');
            m.classList.remove('show');
            setTimeout(() => m.style.display = 'none', 300);
        }
        window.onclick = function(e) { if(e.target == document.getElementById('deleteModal')) closeDeleteModal(); }
    </script>

</body>
</html>