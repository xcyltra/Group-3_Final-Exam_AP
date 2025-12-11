<?php
session_start();
require_once '../../config/database.php';

// 1. Cek Akses Admin
// Cek Akses (Admin ATAU Interviewer boleh masuk)
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'interviewer')) {
    // Jika bukan admin DAN bukan interviewer, tendang ke login
    header("Location: ../../auth/login.php");
    exit;
}

// 2. Ambil Data Departemen (Untuk Dropdown Filter)
try {
    $dept_stmt = $conn->query("SELECT * FROM departments ORDER BY name ASC");
    $departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $departments = [];
}

// 3. Logika Filter Pencarian
$search_text = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_dept = isset($_GET['dept']) ? $_GET['dept'] : '';

$where_clauses = [];
$params = [];

// Filter Nama Lowongan
if (!empty($search_text)) {
    $where_clauses[] = "j.title LIKE :search";
    $params[':search'] = "%$search_text%";
}

// Filter Tipe Pekerjaan (On-Site, Remote, dll)
if (!empty($filter_type)) {
    $where_clauses[] = "j.job_type = :type";
    $params[':type'] = $filter_type;
}

// Filter Departemen
if (!empty($filter_dept)) {
    $where_clauses[] = "j.department_id = :dept";
    $params[':dept'] = $filter_dept;
}

// Gabungkan semua filter
$sql_where = "";
if (count($where_clauses) > 0) {
    $sql_where = "WHERE " . implode(" AND ", $where_clauses);
}

// 4. Query Utama (Ambil Data Jobs)
try {
    $query = "SELECT j.*, d.name as dept_name 
              FROM jobs j 
              LEFT JOIN departments d ON j.department_id = d.department_id 
              $sql_where
              ORDER BY j.job_id DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lowongan - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include '../../includes/header.php'; ?>

    <div class="dashboard-container">
        
        <div class="page-header">
            <h2>Kelola Lowongan Pekerjaan</h2>
            <a href="job_create.php" class="btn-tambah">+ Tambah</a>
        </div>

        <form action="" method="GET" class="filter-bar">
            
            <input type="text" name="search" class="search-input" placeholder="Cari Lowongan Kerja" value="<?php echo htmlspecialchars($search_text); ?>">
            
            <select name="type" class="filter-select">
                <option value="">Lokasi</option>
                <option value="On-Site" <?php echo ($filter_type == 'On-Site') ? 'selected' : ''; ?>>On-Site</option>
                <option value="Remote" <?php echo ($filter_type == 'Remote') ? 'selected' : ''; ?>>Remote</option>
                <option value="Hybrid" <?php echo ($filter_type == 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
            </select>

            <select name="dept" class="filter-select">
                <option value="">Departemen</option>
                <?php foreach($departments as $d): ?>
                    <option value="<?php echo $d['department_id']; ?>" <?php echo ($filter_dept == $d['department_id']) ? 'selected' : ''; ?>>
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
                        <th width="5%">No</th>
                        <th style= "text-align: center;">Nama Pekerjaan</th>
                        <th style= "text-align: center;">Departemen</th>
                        <th style= "text-align: center;">Lokasi</th>
                        <th style= "text-align: center;">Kuota</th>
                        <th style= "text-align: center;">Tutup Lamaran</th>
                        <th style= "text-align: center;">Aksi</th>
                    </tr>
                </thead>    
                <tbody>
                    <?php if (count($jobs) > 0): ?>
                        <?php $no = 1; foreach($jobs as $job): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo htmlspecialchars($job['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($job['dept_name']); ?></td>
                            <td>
                                <span class="tag">
                                    <?php echo htmlspecialchars($job['job_type']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($job['quota']); ?></td>
                            <td>
                                <?php 
                                    $closing = strtotime($job['closing_date']);
                                    echo date('d M Y', $closing);
                                    if ($closing < time()) echo ' <span style="color: var(--danger-color); font-size: 11px; font-weight: 600;">(Expired)</span>';
                                ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="job_edit.php?id=<?php echo $job['job_id']; ?>" class="btn-edit">Edit</a>
                                    <a href="javascript:void(0);" 
                                       class="btn-hapus" 
                                       onclick="openDeleteModal('job_delete.php?id=<?php echo $job['job_id']; ?>')">
                                       Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-secondary);">
                                Tidak ada lowongan yang ditemukan.
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
            <p>Apakah Anda yakin ingin menghapus data lowongan ini? Tindakan ini tidak dapat dibatalkan.</p>
            
            <div class="modal-actions">
                <button class="btn-close-modal" onclick="closeDeleteModal()">Batal</button>
                
                <a href="#" id="confirmDeleteBtn" class="btn-confirm-delete">Ya, Hapus</a>
            </div>
        </div>
    </div>

    <script>
        // Fungsi Buka Modal
        function openDeleteModal(deleteUrl) {
            const modal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            
            confirmBtn.href = deleteUrl;
            
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        // Fungsi Tutup Modal
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // Tutup modal jika user klik di area gelap
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>