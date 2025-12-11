<?php
session_start();
require_once '../../config/database.php';

// 1. Cek Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// 2. Logika Filter & Pencarian
$search = $_GET['search'] ?? '';
$role   = $_GET['role'] ?? '';

$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "full_name LIKE ?";
    $params[] = "%$search%";
}

if ($role) {
    $conditions[] = "role = ?";
    $params[] = $role;
}

// Query Dasar
$sql = "SELECT * FROM user";
if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY full_name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengguna - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        
        <div class="page-header">
            <h2>Kelola Pengguna</h2>
            <a href="user_create.php" class="btn-tambah">+ Tambah</a>
        </div>

        <form action="" method="GET" class="filter-bar">
            <input type="text" name="search" class="search-input" style="flex: 2;" placeholder="Cari Pengguna" value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="role" class="filter-select" style="flex: 1;">
                <option value="">Semua Role</option>
                <option value="admin" <?php echo ($role == 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="interviewer" <?php echo ($role == 'interviewer') ? 'selected' : ''; ?>>Interviewer</option>
                <option value="candidate" <?php echo ($role == 'candidate') ? 'selected' : ''; ?>>Candidate</option>
            </select>
            
            <button type="submit" class="btn-search">Search</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">User</th>
                        <th style="text-align: center;">Role User</th>
                        <th style="text-align: center; width: 20%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                <div style="font-size: 12px; color: #666; margin-top: 4px;">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </td>
                            
                            <td style="text-align: center; font-weight: 600;">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </td>
                            
                            <td>
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="user_edit.php?id=<?php echo $user['user_id']; ?>" class="btn-edit">Edit</a>
                                    
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <a href="javascript:void(0);" 
                                           class="btn-hapus" 
                                           onclick="openDeleteModal('user_delete.php?id=<?php echo $user['user_id']; ?>')">
                                           Hapus
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:#888;">(Anda)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 30px;">
                                Tidak ada data pengguna.
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
            <p>Apakah Anda yakin ingin menghapus pengguna ini? <br>Data terkait (Lamaran/Nilai) mungkin ikut terhapus.</p>
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