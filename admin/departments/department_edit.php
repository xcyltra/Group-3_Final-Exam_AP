<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: department.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $note = isset($_POST['note']) ? trim($_POST['note']) : '';

    if (empty($name)) {
        $error = "Nama departemen wajib diisi.";
    } else {
        try {
            // Cek duplikasi nama pada record lain
            $check = $conn->prepare("SELECT COUNT(*) FROM departments WHERE name = ? AND department_id != ?");
            $check->execute([$name, $id]);

            if ($check->fetchColumn() > 0) {
                $error = "Departemen dengan nama tersebut sudah ada.";
            } else {
                $stmt = $conn->prepare("UPDATE departments SET name = ?, note = ? WHERE department_id = ?");
                $stmt->execute([$name, $note, $id]);
                header("Location: department.php?msg=updated");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Ambil data departemen saat ini
$stmt = $conn->prepare("SELECT * FROM departments WHERE department_id = ?");
$stmt->execute([$id]);
$dept = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$dept) {
    header("Location: department.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Departemen - Miso Corp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include '../../includes/header.php'; ?>

<div class="dashboard-container">
    <div class="page-header header-center"><h2>Edit Departemen</h2></div>

    <div class="form-card">
        <?php if (isset($error)): ?>
            <p style="color:red"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nama Departemen</label>
                <input type="text" name="name" class="form-input" value="<?php echo isset($name) ? htmlspecialchars($name) : htmlspecialchars($dept['name']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="note" class="form-input" rows="4"><?php echo isset($note) ? htmlspecialchars($note) : htmlspecialchars($dept['note']); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">Simpan Perubahan</button>
                <a href="department.php" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
