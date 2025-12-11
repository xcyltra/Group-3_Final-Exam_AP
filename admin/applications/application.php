<?php
session_start();
require_once '../../config/database.php';

// 1. Check Admin Access
// Cek Akses (Admin ATAU Interviewer boleh masuk)
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'interviewer')) {
    // Jika bukan admin DAN bukan interviewer, tendang ke login
    header("Location: ../auth/login.php");
    exit;
}

// 2. Fetch Departments (For Location Filter)
$deptQuery = $conn->query("SELECT * FROM departments ORDER BY name ASC");
$departments = $deptQuery->fetchAll(PDO::FETCH_ASSOC);

// 3. Filter Logic
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$location = $_GET['loc'] ?? '';

$conditions = [];
$params = [];

// Build Search Conditions
if ($search) {
    $conditions[] = "u.full_name LIKE ?";
    $params[] = "%$search%";
}
if ($type) {
    $conditions[] = "j.job_type = ?";
    $params[] = $type;
}
if ($location) {
    $conditions[] = "j.department_id = ?";
    $params[] = $location;
}

// Construct SQL Query
$sql = "SELECT a.application_id, u.full_name, j.title, j.job_type, d.name as dept_name
        FROM applications a
        JOIN user u ON a.user_id = u.user_id
        JOIN jobs j ON a.job_id = j.job_id
        LEFT JOIN departments d ON j.department_id = d.department_id";

if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY a.created_at DESC";

// Execute Query
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications - Miso Corp</title>
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
            <input type="text" name="search" class="search-input" placeholder="Search Applicant Name" value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="type" class="filter-select">
                <option value="">Lokasi</option>
                <option value="On-Site" <?php echo ($type == 'On-Site') ? 'selected' : ''; ?>>On-Site</option>
                <option value="Remote" <?php echo ($type == 'Remote') ? 'selected' : ''; ?>>Remote</option>
                <option value="Hybrid" <?php echo ($type == 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
            </select>

            <select name="loc" class="filter-select">
                <option value="">Departemen</option>
                <?php foreach($departments as $dept): ?>
                    <option value="<?php echo $dept['department_id']; ?>" <?php echo ($location == $dept['department_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn-search">Search</button>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style= "text-align: center;">Nama Pendaftar</th>
                        <th style= "text-align: center;">Posisi Dilamar</th>
                        <th style= "text-align: center;">Lokasi</th>
                        <th style= "text-align: center;">Departemen</th>
                        <th style= "text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($applications) > 0): ?>
                        <?php foreach($applications as $app): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($app['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($app['title']); ?></td>
                            <td><strong><?php echo htmlspecialchars($app['job_type']); ?></strong></td>
                            <td><strong><?php echo htmlspecialchars($app['dept_name']); ?></strong></td>
                            <td>
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="application_edit.php?id=<?php echo $app['application_id']; ?>" class="btn-edit" style="background-color: #666;">Edit</a>
                                    
                                    <a href="javascript:void(0);" 
                                       class="btn-hapus" 
                                       style="background-color: #999;"
                                       onclick="openDeleteModal('application_delete.php?id=<?php echo $app['application_id']; ?>')">
                                       Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:30px;">No application data found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon">⚠️</div>
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this application?</p>
            <div class="modal-actions">
                <button class="btn-close-modal" onclick="closeDeleteModal()">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn-confirm-delete">Yes, Delete</a>
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