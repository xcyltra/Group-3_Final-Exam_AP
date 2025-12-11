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

// 2. Logika Filter
$search_text = isset($_GET['search']) ? $_GET['search'] : '';

$where_clauses = [];
$params = [];

// Filter Nama Departemen
if (!empty($search_text)) {
    $where_clauses[] = "name LIKE :search";
    $params[':search'] = "%$search_text%";
}

$sql_where = "";
if (count($where_clauses) > 0) {
    $sql_where = "WHERE " . implode(" AND ", $where_clauses);
}

// 3. Query Data Departemen
try {
    $query = "SELECT department_id, name, note
              FROM departments
              $sql_where
              ORDER BY name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Departemen - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        
        <div class="page-header">
            <h2>Kelola Departemen</h2>
            <a href="department_create.php" class="btn-tambah">+ Tambah</a>
        </div>

        <form action="" method="GET" class="filter-bar">
            <input type="text" name="search" class="search-input" placeholder="Cari Departemen" value="<?php echo htmlspecialchars($search_text); ?>">
            
            <button type="submit" class="btn-search">Search</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style= "text-align: center;">Nama Departemen</th>
                        <th style= "text-align: center;">Deskripsi</th>
                        <th style=" text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($departments) > 0): ?>
                        <?php foreach($departments as $row): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($row['note']); ?></td>
                            <td>
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="department_edit.php?id=<?php echo $row['department_id']; ?>" class="btn-edit" style="background-color: #666;">Edit</a>
                                    
                                    <a href="javascript:void(0);" 
                                       class="btn-hapus" 
                                       style="background-color: #999;"
                                       onclick="openDeleteModal('department_delete.php?id=<?php echo $row['department_id']; ?>')">
                                       Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align:center; padding:30px;">Tidak ada data departemen.</td>
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
            <p>Yakin ingin menghapus departemen ini?</p>
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
