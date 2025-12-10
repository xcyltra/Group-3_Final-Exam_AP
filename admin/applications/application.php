<?php
session_start();
require_once '../../config/database.php';

// 1. Cek Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// 2. Ambil Data Departemen (Untuk Filter Lokasi)
$dept_stmt = $conn->query("SELECT * FROM departments ORDER BY name ASC");
$departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Logika Filter
$search_text = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_loc  = isset($_GET['loc']) ? $_GET['loc'] : '';

$where_clauses = [];
$params = [];

// Filter Nama Pelamar
if (!empty($search_text)) {
    $where_clauses[] = "u.full_name LIKE :search";
    $params[':search'] = "%$search_text%";
}
// Filter Tipe Pekerjaan
if (!empty($filter_type)) {
    $where_clauses[] = "j.job_type = :type";
    $params[':type'] = $filter_type;
}
// Filter Departemen
if (!empty($filter_loc)) {
    $where_clauses[] = "j.department_id = :loc";
    $params[':loc'] = $filter_loc;
}

$sql_where = "";
if (count($where_clauses) > 0) {
    $sql_where = "WHERE " . implode(" AND ", $where_clauses);
}

// 4. Query Data (JOIN tabel applications, user, jobs, departments)
try {
    $query = "SELECT a.application_id, u.full_name, j.title, j.job_type, d.name as dept_name
              FROM applications a
              JOIN user u ON a.user_id = u.user_id
              JOIN jobs j ON a.job_id = j.job_id
              LEFT JOIN departments d ON j.department_id = d.department_id
              $sql_where
              ORDER BY a.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Pendaftar - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        
        <div class="page-header">
            <h2>Kelola Data Pendaftar</h2>
            <a href="application_create.php" class="btn-tambah">+ Tambah</a>
        </div>

        <form action="" method="GET" class="filter-bar">
            <input type="text" name="search" class="search-input" placeholder="Cari Pendaftar" value="<?php echo htmlspecialchars($search_text); ?>">
            
            <select name="type" class="filter-select">
                <option value="">Tipe Pekerjaan</option>
                <option value="On-Site" <?php echo ($filter_type == 'On-Site') ? 'selected' : ''; ?>>On-Site</option>
                <option value="Remote" <?php echo ($filter_type == 'Remote') ? 'selected' : ''; ?>>Remote</option>
                <option value="Hybrid" <?php echo ($filter_type == 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
            </select>

            <select name="loc" class="filter-select">
                <option value="">Departemen</option>
                <?php foreach($departments as $d): ?>
                    <option value="<?php echo $d['department_id']; ?>" <?php echo ($filter_loc == $d['department_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($d['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn-search">Search</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="background-color: #888; color: #000;">Nama Pendaftar</th>
                        <th style="background-color: #888; color: #000;">Posisi Dilamar</th>
                        <th style="background-color: #888; color: #000;">Tipe</th>
                        <th style="background-color: #888; color: #000;">Lokasi</th>
                        <th style="background-color: #888; color: #000; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($apps) > 0): ?>
                        <?php foreach($apps as $row): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['job_type']); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['dept_name']); ?></strong>
                            </td>
                            <td>
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="application_edit.php?id=<?php echo $row['application_id']; ?>" class="btn-edit" style="background-color: #666;">Edit</a>
                                    
                                    <a href="javascript:void(0);" 
                                       class="btn-hapus" 
                                       style="background-color: #999;"
                                       onclick="openDeleteModal('application_delete.php?id=<?php echo $row['application_id']; ?>')">
                                       Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:30px;">Tidak ada data pendaftar.</td>
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
            <p>Yakin ingin menghapus data pendaftar ini?</p>
            <div class="modal-actions">
                <button class="btn-close-modal" onclick="closeDeleteModal()">Batal</button>
                <a href="#" id="confirmDeleteBtn" class="btn-confirm-delete">Ya, Hapus</a>
            </div>
        </div>
    </div>

    <script>
        // Gunakan script modal yang sama seperti sebelumnya
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