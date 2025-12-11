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

try {
    $stmt = $conn->prepare("DELETE FROM departments WHERE department_id = ?");
    $stmt->execute([$id]);
    header("Location: department.php?msg=deleted");
    exit;
} catch (PDOException $e) {
    header("Location: department.php?err=" . urlencode($e->getMessage()));
    exit;
}
?>