<?php
session_start();
require_once '../../config/database.php';

// 1. Cek Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// 2. Logika Pencarian
$search = $_GET['search'] ?? '';
$params = [];

$sql = "SELECT * FROM requirements";

if ($search) {
    // Cari berdasarkan Pendidikan atau Pengalaman
    $sql .= " WHERE education LIKE ? OR experience LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$sql .= " ORDER BY requirement_id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <input type="text" name="search" class="search-input" placeholder="Cari Persyaratan (Pendidikan / Pengalaman)" value="<?php echo htmlspecialchars($search); ?>">
            
            <button type="submit" class="btn-search">Search</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="background-color: #888; color: #000;">Persyaratan</th>
                        
                        <th width="20%" style="background-color: #888; color: #000; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requirements) > 0): ?>
                        <?php foreach($requirements as $req): ?>
                        <tr>
                            <td>
                                <div style="line-height: 1.6;">
                                    <strong>ID Paket:</strong> #<?php echo $req['requirement_id']; ?> <br>
                                    <strong>Pendidikan:</strong> <?php echo htmlspecialchars($req['education']); ?> <br>
                                    <strong>Pengalaman:</strong> <?php echo htmlspecialchars($req['experience']); ?>
                                    
                                    <div style="margin-top: 5px; font-size: 12px; color: #555;">
                                        <em>Wajib: 
                                        <?php 
                                            $docs = [];
                                            if($req['status_cv'] == 'Required') $docs[] = 'CV';
                                            if($req['status_identity_card'] == 'Required') $docs[] = 'KTP';
                                            // Tampilkan list dokumen wajib dipisah koma
                                            echo implode(', ', $docs);
                                        ?>
                                        </em>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="requirement_edit.php?id=<?php echo $req['requirement_id']; ?>" class="btn-edit" style="background-color: #666;">Edit</a>
                                    
                                    <a href="javascript:void(0);" 
                                       class="btn-hapus" 
                                       style="background-color: #999;"
                                       onclick="openDeleteModal('requirement_delete.php?id=<?php echo $req['requirement_id']; ?>')">
                                       Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 30px;">
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
            <p>Apakah Anda yakin ingin menghapus paket persyaratan ini?</p>
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